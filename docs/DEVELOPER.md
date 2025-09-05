# Developer Documentation

NS Mega Menu プラグインの開発者向けドキュメントです。

## アーキテクチャ概要

### ファイル構造

```
ns-mega-menu/
├── ns-mega-menu.php      # メインプラグインファイル
├── uninstall.php         # アンインストール処理
├── inc/                  # 機能モジュール
│   ├── class-core.php    # コアクラス（初期化・調整）
│   ├── class-walker.php  # カスタムウォーカー
│   ├── class-admin.php   # 管理画面機能
│   ├── class-frontend.php # フロントエンド機能
│   └── functions.php     # ユーティリティ関数
├── assets/               # 静的アセット
│   ├── css/
│   └── js/
├── templates/            # テンプレートファイル（将来用）
├── languages/            # 言語ファイル
└── docs/                 # 開発者ドキュメント
```

### クラス設計

#### NSMM_Core
- プラグインの初期化とコーディネート
- Singletonパターン
- 依存関係の管理

#### NSMM_Walker
- WordPress標準の `Walker_Nav_Menu` を継承
- メガメニューHTML出力を担当
- 親子関係の追跡

#### NSMM_Admin
- 管理画面のカスタムフィールド
- メニューアイテムのメタデータ保存
- メディアライブラリ連携

#### NSMM_Frontend
- フロントエンドアセットの読み込み
- キャッシュバスティング

## 公開API

### 定数

```php
NSMM_VERSION        // プラグインバージョン
NSMM_PLUGIN_URL     // プラグインURL
NSMM_PLUGIN_PATH    // プラグインパス
NSMM_PLUGIN_BASENAME // プラグインベースネーム

// 後方互換性
NSMM_VER            // = NSMM_VERSION  
NSMM_URL            // = NSMM_PLUGIN_URL
NSMM_PATH           // = NSMM_PLUGIN_PATH
```

### 関数

#### ns_mega_menu_render( $args )
**推奨テンプレートタグ**

```php
ns_mega_menu_render( array(
    'theme_location' => 'primary',
    'container'      => 'nav',
    'container_class'=> 'my-menu-class',
    'echo'           => true,
) );
```

#### nsmm_render_menu( $args )
**非推奨（0.10.0〜）**

後方互換性のために残存。`ns_mega_menu_render()` を使用してください。

#### ユーティリティ関数

```php
// メガメニュー関連
ns_mega_menu_has_mega( $menu_item_id )        // メガメニューが有効か
ns_mega_menu_get_mode( $menu_item_id )        // メガメニューモード取得
ns_mega_menu_get_columns( $menu_item_id )     // 列数取得  
ns_mega_menu_get_thumbnail_id( $menu_item_id ) // サムネイルID取得

// サニタイズ関数
ns_mega_menu_sanitize_mode( $mode )           // モード値の検証
ns_mega_menu_sanitize_columns( $cols )        // 列数の検証
```

### ショートコード

#### [ns_mega_menu]

```php
[ns_mega_menu location="primary" menu="" container="nav" class="nsmm nsmm-default"]
```

**属性:**
- `location`: メニューの場所
- `menu`: メニューID・名前・スラッグ
- `container`: コンテナタグ（nav, div, false等）
- `class`: コンテナクラス

## フックAPI

### フィルター

#### ns_mega_menu_walker_class
**カスタムウォーカーの指定**

```php
add_filter( 'ns_mega_menu_walker_class', function( $walker_class ) {
    return 'My_Custom_Walker';
} );
```

#### ns_mega_menu_container_class
**コンテナクラスの変更**

```php
add_filter( 'ns_mega_menu_container_class', function( $classes, $args ) {
    $classes .= ' my-additional-class';
    return $classes;
}, 10, 2 );
```

#### ns_mega_menu_allowed_modes
**許可されるメガメニューモードの拡張**

```php
add_filter( 'ns_mega_menu_allowed_modes', function( $modes ) {
    $modes[] = 'my-custom-mode';
    return $modes;
} );
```

#### ns_mega_menu_item_classes
**メニューアイテムクラスの変更**

```php
add_filter( 'ns_mega_menu_item_classes', function( $classes, $item, $depth ) {
    if ( $depth === 0 ) {
        $classes[] = 'top-level';
    }
    return $classes;
}, 10, 3 );
```

### アクション

#### ns_mega_menu_before_render
**メニュー出力前**

```php
add_action( 'ns_mega_menu_before_render', function( $args ) {
    // メニュー出力前の処理
} );
```

#### ns_mega_menu_after_render  
**メニュー出力後**

```php
add_action( 'ns_mega_menu_after_render', function( $args ) {
    // メニュー出力後の処理
} );
```

#### ns_mega_menu_admin_fields
**管理画面カスタムフィールド追加**

```php
add_action( 'ns_mega_menu_admin_fields', function( $item_id, $item, $depth ) {
    if ( $depth === 0 ) {
        echo '<div>追加のフィールド</div>';
    }
}, 10, 3 );
```

## データ構造

### メニューアイテムメタ

```php
// 親メニューアイテム
_nsmm_mode     // '', 'mega-grid', 'mega-wide'
_nsmm_columns  // 2-6 (デフォルト: 4)

// 子メニューアイテム  
_nsmm_thumb_id // アタッチメントID（数値）
```

### プラグインオプション

```php
$options = get_option( 'ns_mega_menu', array() );
// 現在は使用していないが、将来の設定用に予約
```

## カスタマイズ例

### カスタムウォーカーの作成

```php
class My_Mega_Walker extends NSMM_Walker {
    public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
        // カスタムHTML出力
        parent::start_el( $output, $item, $depth, $args, $id );
    }
}

// ウォーカーを登録
add_filter( 'ns_mega_menu_walker_class', function() {
    return 'My_Mega_Walker';
} );
```

### 追加メガメニューモード

```php
// モードを追加
add_filter( 'ns_mega_menu_allowed_modes', function( $modes ) {
    $modes[] = 'mega-carousel';
    return $modes;
} );

// 管理画面にオプション追加
add_action( 'wp_nav_menu_item_custom_fields', function( $item_id, $item, $depth ) {
    if ( $depth === 0 ) {
        $mode = get_post_meta( $item_id, '_nsmm_mode', true );
        echo '<option value="mega-carousel" ' . selected( $mode, 'mega-carousel', false ) . '>カルーセル</option>';
    }
}, 15, 4 );
```

### 独自CSS追加

```php
add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_style( 
        'my-mega-menu-custom',
        get_template_directory_uri() . '/css/mega-menu-custom.css',
        array( 'nsmm-style' ),
        '1.0.0'
    );
}, 20 );
```

## セキュリティ考慮事項

### 出力エスケープ
すべての出力は適切にエスケープしてください：

```php
echo esc_html( $title );           // HTML内テキスト
echo esc_attr( $class );           // HTML属性
echo esc_url( $url );              // URL  
echo wp_kses_post( $description ); // HTML許可済みコンテンツ
```

### 入力サニタイズ
すべての入力は適切にサニタイズしてください：

```php
$mode = sanitize_text_field( $_POST['mode'] );
$cols = (int) $_POST['columns'];
$thumb_id = absint( $_POST['thumb_id'] );
```

### nonce検証
フォーム送信時は必ずnonce検証を行ってください：

```php
if ( ! wp_verify_nonce( $_POST['nonce'], 'action_name' ) ) {
    wp_die( 'Security check failed' );
}
```

## パフォーマンス最適化

### アセット条件読み込み
```php
// メガメニューが使用されている場合のみJS読み込み
add_action( 'wp_enqueue_scripts', function() {
    if ( ns_mega_menu_is_used_on_page() ) {
        wp_enqueue_script( 'nsmm-script' );
    }
} );
```

### キャッシュ考慮
```php
// メニューキャッシュとの互換性
add_filter( 'wp_nav_menu_cache', function( $cache_key, $args ) {
    if ( isset( $args->walker ) && is_a( $args->walker, 'NSMM_Walker' ) ) {
        $cache_key .= '_nsmm';
    }
    return $cache_key;
}, 10, 2 );
```

## 拡張性

### テンプレートシステム
将来的なテンプレート上書き対応：

```php
// テーマでのテンプレート上書き
function get_ns_mega_menu_template( $template ) {
    $theme_template = get_stylesheet_directory() . '/ns-mega-menu/' . $template;
    if ( file_exists( $theme_template ) ) {
        return $theme_template;
    }
    return NSMM_PATH . 'templates/' . $template;
}
```

### REST API対応
将来的なHeadless WordPress対応：

```php
// メニューデータのREST API公開
add_action( 'rest_api_init', function() {
    register_rest_field( 'nav_menu_item', 'mega_menu', array(
        'get_callback' => function( $item ) {
            return array(
                'mode' => ns_mega_menu_get_mode( $item['id'] ),
                'columns' => ns_mega_menu_get_columns( $item['id'] ),
                'thumbnail' => ns_mega_menu_get_thumbnail_id( $item['id'] ),
            );
        },
    ) );
} );
```

## 互換性維持

### 非推奨機能
- `nsmm_render_menu()`: 0.10.0で非推奨、`ns_mega_menu_render()`を使用
- 旧定数（`NSMM_VER`等）: 1.0.0で削除予定

### ブレイキングチェンジの避け方
- 公開APIの変更は事前告知
- 非推奨機能は最低2バージョン維持
- セマンティックバージョニング準拠