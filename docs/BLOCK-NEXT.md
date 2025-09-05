# ブロック版移行計画

NS Mega Menu のGutenbergブロック版実装に向けた設計メモ

## 目的

現在のClassic Menu（`wp_nav_menu()`）ベースから、Gutenbergブロック対応への移行により、以下を実現：

- **サイトエディター（FSE）**での直接編集
- **リアルタイムプレビュー**でのメガメニュー表示
- **ブロックパターン**としての再利用
- **REST API**によるHeadless対応

## アーキテクチャ移行戦略

### 再利用する層（Current）

#### ✅ データレイヤー（100%再利用）
- `_nsmm_mode`, `_nsmm_columns`, `_nsmm_thumb_id` メタ構造
- `ns_mega_menu_get_*()` ユーティリティ関数
- サニタイズ・バリデーション関数

#### ✅ スタイルシート（80%再利用）
- CSS変数化により両方式で共用
- `.nsmm-*` クラス体系維持
- レスポンシブ・アクセシビリティ対応継承

#### ✅ セキュリティレイヤー（100%再利用）
- エスケープ・サニタイズ処理
- 権限チェック機構
- nonce・CSRF対策

### 置換する層（New）

#### 🔄 表示レイヤー
**Before:** `NSMM_Walker` + `wp_nav_menu()`
```php
class NSMM_Walker extends Walker_Nav_Menu {
    public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
        // HTML文字列構築
    }
}
```

**After:** React Component + Block API
```javascript
const MegaMenuItem = ({ item, mode, columns }) => {
    return (
        <li className={`menu-item ${mode === 'mega-grid' ? 'nsmm-grid-item' : ''}`}>
            <InnerBlocks template={getMenuTemplate(mode)} />
        </li>
    );
};
```

#### 🔄 設定レイヤー
**Before:** `wp_nav_menu_item_custom_fields` フック
```php
add_action( 'wp_nav_menu_item_custom_fields', function( $item_id, $item, $depth ) {
    // 管理画面HTML出力
} );
```

**After:** Block Inspector Controls
```javascript
const MegaMenuControls = ({ attributes, setAttributes }) => (
    <InspectorControls>
        <PanelBody title="メガメニュー設定">
            <SelectControl
                label="表示タイプ"
                value={attributes.mode}
                options={megaMenuModes}
                onChange={(mode) => setAttributes({ mode })}
            />
        </PanelBody>
    </InspectorControls>
);
```

## 実装計画

### Phase 1: 基盤ブロック（4-6週間）

#### 1.1 コアブロック実装
```
ns-mega-menu-block/
├── src/
│   ├── blocks/
│   │   ├── mega-menu/         # メインメニューブロック
│   │   ├── mega-menu-item/    # メニューアイテムブロック  
│   │   └── mega-menu-panel/   # メガパネルブロック
│   ├── components/
│   └── utils/
```

#### 1.2 block.json 設計
```json
{
  "name": "ns-mega-menu/mega-menu",
  "category": "widgets",
  "attributes": {
    "menuLocation": {"type": "string"},
    "layout": {"type": "string", "default": "horizontal"},
    "style": {"type": "object"}
  },
  "providesContext": {
    "ns-mega-menu/menuLocation": "menuLocation",
    "ns-mega-menu/layout": "layout"
  }
}
```

### Phase 2: データ統合（2-3週間）

#### 2.1 既存メタデータ統合
```javascript
// block attributes と post meta の双方向同期
const useMegaMenuMeta = (itemId) => {
    const [meta, setMeta] = useEntityProp('postType', 'nav_menu_item', 'meta', itemId);
    
    return {
        mode: meta._nsmm_mode || '',
        columns: meta._nsmm_columns || 4,
        thumbnailId: meta._nsmm_thumb_id || 0,
        updateMeta: (newMeta) => setMeta({ ...meta, ...newMeta })
    };
};
```

#### 2.2 REST API拡張
```php
// カスタムフィールドをREST APIに公開
add_action( 'rest_api_init', function() {
    register_rest_field( 'nav_menu_item', 'mega_menu_data', array(
        'get_callback' => 'get_mega_menu_rest_data',
        'update_callback' => 'update_mega_menu_rest_data',
        'schema' => array(
            'type' => 'object',
            'properties' => array(
                'mode' => array( 'type' => 'string' ),
                'columns' => array( 'type' => 'integer' ),
                'thumbnail_id' => array( 'type' => 'integer' ),
            ),
        ),
    ) );
} );
```

### Phase 3: 後方互換性（1-2週間）

#### 3.1 Classic Menu Bridge
```php
// Classic Menu が選択された場合の自動変換
class NSMM_Classic_Bridge {
    public function convert_to_blocks( $menu_id ) {
        $menu_items = wp_get_nav_menu_items( $menu_id );
        return $this->generate_block_markup( $menu_items );
    }
    
    private function generate_block_markup( $items ) {
        // Classic形式からBlock形式へのコンバーター
    }
}
```

#### 3.2 共存モード
```php
// 両方式を併用可能に
add_action( 'init', function() {
    if ( get_option( 'nsmm_enable_blocks', false ) ) {
        // ブロック版を有効化
        new NSMM_Block_Init();
    }
    
    // Classic版は常に利用可能
    new NSMM_Classic_Init();
} );
```

## ブロック設計詳細

### メインメニューブロック
```javascript
// 親コンテナブロック
registerBlockType('ns-mega-menu/mega-menu', {
    title: 'NS Mega Menu',
    category: 'widgets',
    supports: {
        align: ['wide', 'full'],
        color: { background: true, text: true },
        spacing: { padding: true, margin: true }
    },
    edit: MegaMenuEdit,
    save: MegaMenuSave
});
```

### メニューアイテムブロック
```javascript
// 子アイテムブロック（InnerBlocks使用）
registerBlockType('ns-mega-menu/menu-item', {
    parent: ['ns-mega-menu/mega-menu'],
    attributes: {
        title: { type: 'string' },
        url: { type: 'string' },
        mode: { type: 'string' },
        columns: { type: 'number', default: 4 },
        thumbnailId: { type: 'number', default: 0 }
    },
    edit: MenuItemEdit,
    save: MenuItemSave
});
```

### メガパネルブロック
```javascript
// メガメニューコンテンツブロック
registerBlockType('ns-mega-menu/mega-panel', {
    parent: ['ns-mega-menu/menu-item'],
    attributes: {
        layout: { type: 'string', default: 'grid' },
        columns: { type: 'number', default: 4 }
    },
    edit: MegaPanelEdit,
    save: MegaPanelSave
});
```

## 移行シナリオ

### シナリオ A: 段階移行（推奨）

1. **v0.11.0**: ブロック版をオプション機能として追加
2. **v0.12.0**: ブロック版を推奨、Classic版も維持
3. **v1.0.0**: ブロック版をデフォルト、Classic版をレガシー扱い
4. **v2.0.0**: Classic版を廃止（メジャーバージョンアップ）

### シナリオ B: 並行運用

- Classic版を `ns-mega-menu` パッケージで継続
- ブロック版を `ns-mega-menu-blocks` として新規作成
- ユーザーが選択

## 技術的課題と解決策

### 課題1: SSRとCSR の混在
**問題**: サーバーレンダリングとクライアントレンダリングの整合性

**解決策**: 
```php
// サーバーサイドでも同一HTML生成
function render_mega_menu_ssr( $attributes ) {
    $walker = new NSMM_Block_Walker( $attributes );
    return wp_nav_menu( array( 'walker' => $walker, 'echo' => false ) );
}
```

### 課題2: 既存CSSとの互換性
**問題**: 新しいブロック構造での既存CSS適用

**解決策**:
```scss
// CSS変数でスタイル統一
:root {
  --nsmm-primary-color: #1e50b5;
  --nsmm-grid-columns: 4;
}

.nsmm-mega,
.wp-block-ns-mega-menu-mega-panel {
  // 共通スタイル
}
```

### 課題3: パフォーマンス
**問題**: ブロックエディターでのメニュー編集パフォーマンス

**解決策**:
```javascript
// 遅延読み込み + メモ化
const MegaMenuEdit = memo(({ attributes, setAttributes }) => {
    const deferredAttributes = useDeferredValue(attributes);
    // 重いレンダリング処理
});
```

## 開発環境セットアップ

### 必要ツール追加
```json
{
  "devDependencies": {
    "@wordpress/scripts": "^26.0.0",
    "@wordpress/block-editor": "^12.0.0",
    "@wordpress/components": "^25.0.0",
    "react": "^18.0.0",
    "react-dom": "^18.0.0"
  }
}
```

### ビルド設定
```javascript
// webpack.config.js
module.exports = {
  ...defaultConfig,
  entry: {
    'mega-menu-block': './src/blocks/mega-menu/index.js',
    'menu-item-block': './src/blocks/menu-item/index.js',
    'mega-panel-block': './src/blocks/mega-panel/index.js'
  }
};
```

## 成功指標

### 技術指標
- [ ] 既存Classic版と同等の機能実現
- [ ] パフォーマンス劣化なし（Core Web Vitals維持）
- [ ] 後方互換性100%維持

### UX指標
- [ ] ブロックエディターでのリアルタイムプレビュー
- [ ] 設定変更の即時反映
- [ ] Classic版と同等の操作感

### 移行指標
- [ ] 既存ユーザーの90%が問題なく移行
- [ ] サポート問い合わせ増加率20%以下
- [ ] WordPress.orgレビュースコア維持

## 次のアクション

1. **技術検証**: 最小限のプロトタイプ作成（1週間）
2. **フィードバック収集**: 既存ユーザーからのヒアリング（2週間）
3. **詳細設計**: 上記計画の詳細化（1週間）
4. **開発開始**: Phase 1から段階的実装

---

このドキュメントは実装進捗に応じて更新していく