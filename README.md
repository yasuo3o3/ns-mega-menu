# NS Mega Menu

WordPress用の軽量でアクセシブルなメガメニュープラグインです。

## 概要

NS Mega Menuは、WordPressサイトに簡単にメガメニューを実装できるプラグインです。画像グリッド型とワイド型の2つのレイアウトをサポートし、レスポンシブ対応・アクセシビリティ対応済みです。

## 機能

- **2つのメガメニュータイプ**
  - 画像グリッド型：サムネイル付きの視覚的なメニュー
  - ワイド型：テキスベースの横長メニュー
- **レスポンシブ対応**：PCホバー操作、スマホタップ操作に対応
- **アクセシビリティ対応**：WAI-ARIA準拠、キーボードナビゲーション対応
- **軽量実装**：依存ライブラリなし、最小限のCSS/JS
- **テーマ互換性**：ブロックテーマ・クラシックテーマ両対応

## 必要環境

| 項目 | 要件 |
|------|------|
| WordPress | 6.0以上 |
| PHP | 7.4以上 |
| 推奨PHP | 8.1以上 |

## インストール

### 手動インストール

1. プラグインファイルをダウンロード
2. `wp-content/plugins/ns-mega-menu/` にアップロード
3. 管理画面でプラグインを有効化

## 使用方法（1分クイックスタート）

### 1. メニューにメガパネルを設定

1. **外観 > メニュー** に移動
2. 親メニュー項目を選択・展開
3. **NS Mega Menu** セクションで表示タイプを選択：
   - `（指定なし）`：通常のサブメニュー
   - `メガメニュー（画像グリッド）`：サムネイル付きグリッド
   - `メガメニュー（ワイド）`：横長テキストメニュー
4. 必要に応じて列数を調整（2-6列）

### 2. 子メニュー項目の設定

画像グリッドを選択した場合：
1. 子メニュー項目を展開
2. **NS Mega Menu** セクションでサムネイル画像を設定
3. メニュー項目の説明欄にテキストを入力（説明文として表示）

### 3. テンプレートに出力

#### ショートコード
```
[ns_mega_menu location="primary" class="nsmm nsmm-default"]
```

#### テンプレートタグ
```php
<?php ns_mega_menu_render( array( 'theme_location' => 'primary' ) ); ?>
```

## 設定項目一覧

### 親メニュー項目

| 設定項目 | 説明 | デフォルト |
|----------|------|------------|
| 表示タイプ | メガメニューの種類選択 | （指定なし） |
| 列数 | グリッド・ワイドの列数 | 4 |

### 子メニュー項目

| 設定項目 | 説明 | 使用場面 |
|----------|------|----------|
| サムネイル | メニューに表示する画像 | 画像グリッド |
| 説明 | メニュー項目の説明テキスト | 両メガメニュー |

## テーマ開発者向け

### テンプレート上書き

プラグインのテンプレートはテーマでカスタマイズ可能です：

```
your-theme/
  ns-mega-menu/
    menu-template.php    # カスタムメニューテンプレート
    mega-grid-item.php   # グリッドアイテムテンプレート  
    mega-wide-item.php   # ワイドアイテムテンプレート
```

### 利用可能なフィルター・アクション

#### フィルター

```php
// メニュー出力のカスタマイズ
add_filter( 'ns_mega_menu_walker_class', 'your_custom_walker' );

// CSSクラスの追加・変更
add_filter( 'ns_mega_menu_container_class', 'your_class_filter' );

// メガメニューモードの拡張
add_filter( 'ns_mega_menu_allowed_modes', 'add_custom_modes' );
```

#### アクション

```php
// メニュー出力前
do_action( 'ns_mega_menu_before_render', $args );

// メニュー出力後  
do_action( 'ns_mega_menu_after_render', $args );

// 管理画面カスタムフィールド追加
do_action( 'ns_mega_menu_admin_fields', $item_id, $item, $depth );
```

### ユーティリティ関数

```php
// メニュー項目がメガメニューかチェック
ns_mega_menu_has_mega( $menu_item_id );

// メガメニューモードを取得
ns_mega_menu_get_mode( $menu_item_id );

// メガメニューの列数を取得
ns_mega_menu_get_columns( $menu_item_id );

// サムネイルIDを取得
ns_mega_menu_get_thumbnail_id( $menu_item_id );
```

## 互換性

- **ブロックテーマ**：完全対応（FSE環境でも動作）
- **クラシックテーマ**：完全対応
- **メニューウォーカー**：標準的な `wp_nav_menu()` と互換
- **他プラグイン**：CSSプレフィックスによる干渉回避

## 既知の制限

- 4階層以上のメニューは通常サブメニューとして表示
- メガメニューは2階層目までの表示に最適化
- IE11以下はサポート対象外

## FAQ

### メガメニューが表示されない
- メニューの場所が正しく設定されているか確認
- 親メニューにメガメニュータイプが設定されているか確認  
- 子メニューが存在するか確認

### スマホで操作できない
- JavaScriptエラーがないか開発者ツールで確認
- 他プラグインとの競合がないか調査

### カスタムCSSが効かない
- セレクタの詳細度を確認（`.nsmm` プレフィックス必須）
- `!important` の使用を検討

## ライセンス

GPL v2 or later（完全なライセンス文書はLICENSEファイルをご参照ください）

## 開発

### 必要な開発環境

```bash
# 依存関係インストール
composer install

# コーディング規約チェック
composer run lint

# 自動修正
composer run lint:fix

# 静的解析
composer run analyze  

# 互換性チェック
composer run test:compatibility

# 全チェック実行
composer run test:all
```

### ビルド（配布ZIP作成）

```bash
# 配布用ZIPを作成
git archive --prefix=ns-mega-menu/ --output=ns-mega-menu-0.10.0.zip HEAD
```

## サポート

- バグ報告：[GitHub Issues](https://github.com/netservice/ns-mega-menu/issues)
- 開発者サイト：[Netservice](https://netservice.jp/)

---

**NS Mega Menu** - Netservice制作の高品質WordPressプラグイン