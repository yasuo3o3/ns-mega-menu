<?php
/**
 * NS Mega Menu Admin
 * 管理画面機能（メニュー編集画面のカスタムフィールド等）
 *
 * @package NSMegaMenu
 * @since 0.10.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin functionality class
 */
class NSMM_Admin {

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
	 * Initialize admin hooks
	 */
	private function init_hooks() {
		add_action( 'admin_menu', array( $this, 'add_classic_menu_link' ) );
		add_action( 'wp_nav_menu_item_custom_fields', array( $this, 'add_custom_fields' ), 10, 4 );
		add_action( 'wp_update_nav_menu_item', array( $this, 'save_custom_fields' ), 10, 3 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
	}

	/**
	 * Add classic menu link for block themes
	 */
	public function add_classic_menu_link() {
		add_theme_page(
			__( 'クラシックメニュー', 'ns-mega-menu' ),
			__( 'クラシックメニュー', 'ns-mega-menu' ),
			'edit_theme_options',
			'nav-menus.php'
		);
	}

	/**
	 * Add custom fields to menu items
	 *
	 * @param int      $item_id Menu item ID.
	 * @param WP_Post  $item    Menu item data.
	 * @param int      $depth   Menu depth.
	 * @param stdClass $args    Menu arguments.
	 */
	public function add_custom_fields( $item_id, $item, $depth, $args ) {
		// セキュリティ：nonce フィールドを追加（初回のみ）
		static $nonce_added = false;
		if ( ! $nonce_added ) {
			wp_nonce_field( 'ns_mega_menu_save', 'ns_mega_menu_nonce' );
			$nonce_added = true;
		}

		if ( 0 === $depth ) {
			$this->render_parent_fields( $item_id );
		} else {
			$this->render_child_fields( $item_id );
		}
	}

	/**
	 * Render parent menu item fields
	 *
	 * @param int $item_id Menu item ID.
	 */
	private function render_parent_fields( $item_id ) {
		$mode = get_post_meta( $item_id, '_nsmm_mode', true );
		$cols = (int) get_post_meta( $item_id, '_nsmm_columns', true );
		if ( ! $cols ) {
			$cols = 4;
		}
		?>
		<div class="field-nsmm-mode description-wide">
			<span class="description"><?php esc_html_e( 'NS Mega Menu: 親メニューの表示タイプ', 'ns-mega-menu' ); ?></span>
			<select name="nsmm_mode[<?php echo esc_attr( $item_id ); ?>]" style="width:100%;">
				<option value=""><?php esc_html_e( '（指定なし：通常の小さなサブメニュー）', 'ns-mega-menu' ); ?></option>
				<option value="mega-grid" <?php selected( $mode, 'mega-grid' ); ?>><?php esc_html_e( 'メガメニュー（画像グリッド）', 'ns-mega-menu' ); ?></option>
				<option value="mega-wide" <?php selected( $mode, 'mega-wide' ); ?>><?php esc_html_e( 'メガメニュー（ワイド）', 'ns-mega-menu' ); ?></option>
			</select>
		</div>
		<div class="field-nsmm-columns description-wide" style="margin-top:6px;">
			<label>
				<?php esc_html_e( '列数（画像グリッド/ワイド共通）', 'ns-mega-menu' ); ?>
				<input type="number" min="2" max="6" name="nsmm_columns[<?php echo esc_attr( $item_id ); ?>]" value="<?php echo esc_attr( $cols ); ?>" style="width:80px;">
			</label>
		</div>
		<?php
	}

	/**
	 * Render child menu item fields
	 *
	 * @param int $item_id Menu item ID.
	 */
	private function render_child_fields( $item_id ) {
		$thumb_id  = (int) get_post_meta( $item_id, '_nsmm_thumb_id', true );
		$thumb_src = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'thumbnail' ) : '';
		?>
		<div class="field-nsmm-thumb description-wide">
			<span class="description"><?php esc_html_e( 'NS Mega Menu: サムネイル（画像グリッド用）', 'ns-mega-menu' ); ?></span>
			<div class="nsmm-thumb-field">
				<input type="hidden" class="nsmm-thumb-id" name="nsmm_thumb_id[<?php echo esc_attr( $item_id ); ?>]" value="<?php echo esc_attr( $thumb_id ); ?>">
				<img class="nsmm-thumb-preview" src="<?php echo esc_url( $thumb_src ); ?>" style="max-width:80px; max-height:80px; display:<?php echo $thumb_src ? 'inline-block' : 'none'; ?>;" alt="">
				<button type="button" class="button nsmm-set-thumb"><?php esc_html_e( '画像を選択', 'ns-mega-menu' ); ?></button>
				<button type="button" class="button nsmm-remove-thumb" style="display:<?php echo $thumb_src ? 'inline-block' : 'none'; ?>;"><?php esc_html_e( '削除', 'ns-mega-menu' ); ?></button>
			</div>
		</div>
		<?php
	}

	/**
	 * Save custom fields
	 *
	 * @param int   $menu_id         Menu ID.
	 * @param int   $menu_item_db_id Menu item database ID.
	 * @param array $args          Menu item arguments.
	 */
	public function save_custom_fields( $menu_id, $menu_item_db_id, $args ) {
		// セキュリティ：nonce チェック
		if ( ! isset( $_POST['ns_mega_menu_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['ns_mega_menu_nonce'] ), 'ns_mega_menu_save' ) ) {
			return;
		}

		// 管理者権限の確認
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// リファラーチェック（追加のセキュリティ層）
		check_admin_referer( 'ns_mega_menu_save', 'ns_mega_menu_nonce' );

		// Parent: Mode
		if ( isset( $_POST['nsmm_mode'][ $menu_item_db_id ] ) ) {
			$mode = sanitize_text_field( wp_unslash( $_POST['nsmm_mode'][ $menu_item_db_id ] ) );
			if ( in_array( $mode, array( '', 'mega-grid', 'mega-wide' ), true ) ) {
				if ( '' === $mode ) {
					delete_post_meta( $menu_item_db_id, '_nsmm_mode' );
				} else {
					update_post_meta( $menu_item_db_id, '_nsmm_mode', $mode );
				}
			}
		}

		// Parent: Columns
		if ( isset( $_POST['nsmm_columns'][ $menu_item_db_id ] ) ) {
			$cols = (int) $_POST['nsmm_columns'][ $menu_item_db_id ];
			$cols = max( 2, min( 6, $cols ) );
			update_post_meta( $menu_item_db_id, '_nsmm_columns', $cols );
		}

		// Child: Thumbnail
		if ( isset( $_POST['nsmm_thumb_id'][ $menu_item_db_id ] ) ) {
			$thumb_id = (int) $_POST['nsmm_thumb_id'][ $menu_item_db_id ];
			if ( $thumb_id > 0 && wp_attachment_is_image( $thumb_id ) ) {
				update_post_meta( $menu_item_db_id, '_nsmm_thumb_id', $thumb_id );
			} else {
				delete_post_meta( $menu_item_db_id, '_nsmm_thumb_id' );
			}
		}
	}

	/**
	 * Enqueue admin scripts
	 *
	 * @param string $hook Admin page hook.
	 */
	public function enqueue_admin_scripts( $hook ) {
		if ( 'nav-menus.php' !== $hook ) {
			return;
		}

		wp_enqueue_media();

		$script = "
		(function() {
			'use strict';

			// メディアライブラリからサムネイル選択
			document.addEventListener('click', function(e) {
				if (!e.target.classList.contains('nsmm-set-thumb')) return;
				
				e.preventDefault();
				
				var wrap = e.target.closest('.nsmm-thumb-field');
				if (!wrap) return;

				var frame = wp.media({
					title: '" . esc_js( __( 'サムネイルを選択', 'ns-mega-menu' ) ) . "',
					button: { text: '" . esc_js( __( '選択', 'ns-mega-menu' ) ) . "' },
					multiple: false
				});

				frame.on('select', function() {
					var attachment = frame.state().get('selection').first().toJSON();
					var thumbId = wrap.querySelector('.nsmm-thumb-id');
					var thumbPreview = wrap.querySelector('.nsmm-thumb-preview');
					var removeBtn = wrap.querySelector('.nsmm-remove-thumb');

					if (thumbId) thumbId.value = attachment.id;
					if (thumbPreview) {
						thumbPreview.src = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
						thumbPreview.style.display = 'inline-block';
					}
					if (removeBtn) removeBtn.style.display = 'inline-block';
				});

				frame.open();
			});

			// サムネイル削除
			document.addEventListener('click', function(e) {
				if (!e.target.classList.contains('nsmm-remove-thumb')) return;
				
				e.preventDefault();
				
				var wrap = e.target.closest('.nsmm-thumb-field');
				if (!wrap) return;

				var thumbId = wrap.querySelector('.nsmm-thumb-id');
				var thumbPreview = wrap.querySelector('.nsmm-thumb-preview');

				if (thumbId) thumbId.value = '';
				if (thumbPreview) {
					thumbPreview.style.display = 'none';
					thumbPreview.src = '';
				}
				e.target.style.display = 'none';
			});
		})();
		";

		wp_add_inline_script( 'wp-util', $script );
	}
}
