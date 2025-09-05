<?php
/**
 * NS Mega Menu Core
 * メインプラグイン機能の初期化とコーディネート
 *
 * @package NSMegaMenu
 * @since 0.10.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Core class
 */
class NSMM_Core {

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
		$this->load_dependencies();
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks() {
		add_action( 'init', array( $this, 'init' ) );
		add_shortcode( 'ns_mega_menu', array( $this, 'shortcode_handler' ) );
	}

	/**
	 * Load dependencies
	 */
	private function load_dependencies() {
		require_once NSMM_PATH . 'inc/class-walker.php';
		require_once NSMM_PATH . 'inc/class-admin.php';
		require_once NSMM_PATH . 'inc/class-frontend.php';
		require_once NSMM_PATH . 'inc/functions.php';
	}


	/**
	 * Initialize plugin
	 */
	public function init() {
		// Initialize admin functionality
		if ( is_admin() ) {
			NSMM_Admin::get_instance();
		}

		// Initialize frontend functionality
		NSMM_Frontend::get_instance();
	}

	/**
	 * Shortcode handler
	 * [ns_mega_menu location="primary" menu="" container="nav" class="nsmm nsmm-default"]
	 *
	 * @param array $atts Shortcode attributes
	 * @return string Menu HTML
	 */
	public function shortcode_handler( $atts ) {
		$atts = shortcode_atts(
			array(
				'location'  => '',
				'menu'      => '',
				'container' => 'nav',
				'class'     => 'nsmm nsmm-default',
			),
			$atts,
			'ns_mega_menu'
		);

		$args = array(
			'echo'            => false,
			'container'       => $atts['container'] ?: 'nav',
			'container_class' => esc_attr( $atts['class'] ),
			'fallback_cb'     => '__return_empty_string',
			'walker'          => new NSMM_Walker(),
		);

		if ( ! empty( $atts['location'] ) ) {
			$args['theme_location'] = sanitize_key( $atts['location'] );
		} elseif ( ! empty( $atts['menu'] ) ) {
			$args['menu'] = sanitize_text_field( $atts['menu'] );
		}

		return wp_nav_menu( $args );
	}

	/**
	 * Get plugin options
	 *
	 * @return array Plugin options
	 */
	public function get_options() {
		return get_option( 'ns_mega_menu', array() );
	}

	/**
	 * Update plugin options
	 *
	 * @param array $options Options array
	 * @return bool Success status
	 */
	public function update_options( $options ) {
		return update_option( 'ns_mega_menu', $options );
	}
}
