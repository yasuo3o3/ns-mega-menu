<?php
/**
 * NS Mega Menu Frontend
 * フロントエンド機能（アセット読み込み等）
 * 
 * @package NSMegaMenu
 * @since 0.10.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Frontend functionality class
 */
class NSMM_Frontend {

	/**
	 * Single instance
	 */
	private static $instance = null;

	/**
	 * Get instance
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		$this->init_hooks();
	}

	/**
	 * Initialize frontend hooks
	 */
	private function init_hooks() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Enqueue frontend assets
	 */
	public function enqueue_assets() {
		$css_file = NSMM_PATH . 'assets/css/ns-mega-menu.css';
		$js_file  = NSMM_PATH . 'assets/js/ns-mega-menu.js';

		// CSS のエンキュー（ファイル更新時間をバージョンに使用）
		if ( file_exists( $css_file ) ) {
			wp_enqueue_style(
				'nsmm-style',
				NSMM_URL . 'assets/css/ns-mega-menu.css',
				array(),
				filemtime( $css_file )
			);
		}

		// JS のエンキュー（フッターに配置、ファイル更新時間をバージョンに使用）
		if ( file_exists( $js_file ) ) {
			wp_enqueue_script(
				'nsmm-script',
				NSMM_URL . 'assets/js/ns-mega-menu.js',
				array(),
				filemtime( $js_file ),
				true
			);
		}
	}
}