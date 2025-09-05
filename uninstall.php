<?php
/**
 * NS Mega Menu Uninstall
 * プラグイン削除時の処理
 *
 * @package NSMegaMenu
 * @since 0.10.0
 */

// プラグイン削除時以外は処理しない
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * アンインストール処理
 */
function ns_mega_menu_uninstall() {
	// 設定オプションの削除確認
	$delete_data = get_option( 'ns_mega_menu_delete_on_uninstall', false );

	if ( $delete_data ) {
		// プラグイン設定を削除
		delete_option( 'ns_mega_menu' );
		delete_option( 'ns_mega_menu_delete_on_uninstall' );

		// メニュー項目のカスタムメタを削除
		global $wpdb;

		$meta_keys = array(
			'_nsmm_mode',
			'_nsmm_columns',
			'_nsmm_thumb_id',
		);

		foreach ( $meta_keys as $meta_key ) {
			$wpdb->delete(
				$wpdb->postmeta,
				array( 'meta_key' => $meta_key ),
				array( '%s' )
			);
		}

		// トランジェントデータをクリア
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_nsmm_%' OR option_name LIKE '_transient_timeout_nsmm_%'" );
	}
}

// アンインストール実行
ns_mega_menu_uninstall();
