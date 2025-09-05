<?php
/**
 * Plugin Name:       NS Mega Menu
 * Description:       メガメニューを簡単に実装。アクセシブル&軽量。
 * Version:           0.10.0
 * Author:            Netservice
 * Author URI:        https://netservice.jp/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Text Domain:       ns-mega-menu
 * Domain Path:       /languages
 * Requires at least: 6.0
 * Tested up to:      6.5
 * Requires PHP:      7.4
 * Network:           false
 *
 * @package NSMegaMenu
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// プラグイン定数を定義
define( 'NSMM_VERSION', '0.10.0' );
define( 'NSMM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'NSMM_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'NSMM_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// 後方互換性のために旧定数も維持
define( 'NSMM_VER', NSMM_VERSION );
define( 'NSMM_URL', NSMM_PLUGIN_URL );
define( 'NSMM_PATH', NSMM_PLUGIN_PATH );

/**
 * テキストドメイン読み込み
 */
function ns_mega_menu_load_textdomain() {
	load_plugin_textdomain(
		'ns-mega-menu',
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages'
	);
}

/**
 * プラグイン初期化
 */
function ns_mega_menu_init() {
	// コアクラスを読み込み
	require_once NSMM_PATH . 'inc/class-core.php';

	// プラグインインスタンスを初期化
	NSMM_Core::get_instance();
}

// テキストドメイン読み込み
add_action( 'plugins_loaded', 'ns_mega_menu_load_textdomain' );

// プラグイン読み込み時に初期化
add_action( 'plugins_loaded', 'ns_mega_menu_init' );
