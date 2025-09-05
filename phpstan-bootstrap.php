<?php
/**
 * PHPStan Bootstrap
 * 静的解析時のWordPress環境シミュレーション
 *
 * @package NSMegaMenu
 */

// WordPress定数の定義
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', '/tmp/wordpress/' );
}

if ( ! defined( 'WP_PLUGIN_DIR' ) ) {
	define( 'WP_PLUGIN_DIR', '/tmp/wordpress/wp-content/plugins' );
}

if ( ! defined( 'WP_CONTENT_DIR' ) ) {
	define( 'WP_CONTENT_DIR', '/tmp/wordpress/wp-content' );
}

// 基本的なWordPress関数のスタブ
if ( ! function_exists( 'plugin_dir_url' ) ) {
	function plugin_dir_url( $file ) {
		return 'https://example.com/wp-content/plugins/';
	}
}

if ( ! function_exists( 'plugin_dir_path' ) ) {
	function plugin_dir_path( $file ) {
		return '/tmp/wordpress/wp-content/plugins/';
	}
}

if ( ! function_exists( 'plugin_basename' ) ) {
	function plugin_basename( $file ) {
		return 'ns-mega-menu/ns-mega-menu.php';
	}
}

if ( ! function_exists( 'wp_parse_args' ) ) {
	function wp_parse_args( $args, $defaults = '' ) {
		return array_merge( (array) $defaults, (array) $args );
	}
}

// WordPress クラスのスタブ
if ( ! class_exists( 'Walker_Nav_Menu' ) ) {
	class Walker_Nav_Menu {
		public function start_lvl( &$output, $depth = 0, $args = null ) {}
		public function end_lvl( &$output, $depth = 0, $args = null ) {}
		public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {}
		public function end_el( &$output, $item, $depth = 0, $args = null ) {}
		public function display_element( $element, &$children_elements, $max_depth, $depth = 0, $args = array(), &$output = '' ) {}
	}
}
