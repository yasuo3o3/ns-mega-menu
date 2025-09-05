<?php
/**
 * NS Mega Menu Functions
 * テンプレートタグとユーティリティ関数
 * 
 * @package NSMegaMenu
 * @since 0.10.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Template tag for rendering mega menu
 * 
 * Usage: <?php ns_mega_menu_render( array( 'theme_location' => 'primary' ) ); ?>
 * 
 * @param array $args Menu arguments.
 */
function ns_mega_menu_render( $args = array() ) {
	$defaults = array(
		'echo'            => true,
		'container'       => 'nav',
		'container_class' => 'nsmm nsmm-default',
		'fallback_cb'     => '__return_empty_string',
		'walker'          => new NSMM_Walker(),
	);

	$args = wp_parse_args( $args, $defaults );

	// 最低限の設定チェック
	if ( empty( $args['theme_location'] ) && empty( $args['menu'] ) ) {
		if ( $args['echo'] ) {
			echo '<!-- ns_mega_menu_render: theme_location または menu パラメータが必要です -->';
		}
		return '';
	}

	if ( $args['echo'] ) {
		wp_nav_menu( $args );
	} else {
		return wp_nav_menu( $args );
	}
}

/**
 * 後方互換性のための旧関数名エイリアス
 * 
 * @deprecated 0.10.0 Use ns_mega_menu_render() instead.
 * @param array $args Menu arguments.
 */
function nsmm_render_menu( $args = array() ) {
	_deprecated_function( __FUNCTION__, '0.10.0', 'ns_mega_menu_render' );
	return ns_mega_menu_render( $args );
}

/**
 * Get mega menu mode for menu item
 * 
 * @param int $menu_item_id Menu item ID.
 * @return string Mode ('', 'mega-grid', 'mega-wide').
 */
function ns_mega_menu_get_mode( $menu_item_id ) {
	return get_post_meta( $menu_item_id, '_nsmm_mode', true );
}

/**
 * Get mega menu columns for menu item
 * 
 * @param int $menu_item_id Menu item ID.
 * @return int Columns (2-6, default 4).
 */
function ns_mega_menu_get_columns( $menu_item_id ) {
	$cols = (int) get_post_meta( $menu_item_id, '_nsmm_columns', true );
	return $cols ? $cols : 4;
}

/**
 * Get thumbnail ID for menu item
 * 
 * @param int $menu_item_id Menu item ID.
 * @return int Attachment ID.
 */
function ns_mega_menu_get_thumbnail_id( $menu_item_id ) {
	return (int) get_post_meta( $menu_item_id, '_nsmm_thumb_id', true );
}

/**
 * Check if menu item has mega menu enabled
 * 
 * @param int $menu_item_id Menu item ID.
 * @return bool True if mega menu enabled.
 */
function ns_mega_menu_has_mega( $menu_item_id ) {
	$mode = ns_mega_menu_get_mode( $menu_item_id );
	return in_array( $mode, array( 'mega-grid', 'mega-wide' ), true );
}

/**
 * Sanitize mega menu mode
 * 
 * @param string $mode Mode value.
 * @return string Sanitized mode.
 */
function ns_mega_menu_sanitize_mode( $mode ) {
	$allowed_modes = array( '', 'mega-grid', 'mega-wide' );
	return in_array( $mode, $allowed_modes, true ) ? $mode : '';
}

/**
 * Sanitize mega menu columns
 * 
 * @param int $cols Columns value.
 * @return int Sanitized columns (2-6).
 */
function ns_mega_menu_sanitize_columns( $cols ) {
	$cols = (int) $cols;
	return max( 2, min( 6, $cols ) );
}