<?php
/**
 * NS Mega Menu Walker
 * カスタムナビメニューウォーカークラス
 * 
 * @package NSMegaMenu
 * @since 0.10.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Custom Walker for Mega Menu
 */
class NSMM_Walker extends Walker_Nav_Menu {

	/**
	 * Parent stack for tracking top-level items
	 * 
	 * @var array
	 */
	protected $parents_stack = array();

	/**
	 * Start Level - output of sub UL
	 * 
	 * @param string   $output Passed by reference. Used to append additional content.
	 * @param int      $depth  Depth of menu item.
	 * @param stdClass $args   An object of wp_nav_menu() arguments.
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ) {
		$indent      = str_repeat( "\t", $depth );
		$parent_item = $this->get_current_parent_item();
		$mode        = $parent_item ? get_post_meta( $parent_item->ID, '_nsmm_mode', true ) : '';

		if ( 0 === $depth && in_array( $mode, array( 'mega-grid', 'mega-wide' ), true ) ) {
			$class = 'mega-grid' === $mode ? 'nsmm-mega nsmm-mega-grid' : 'nsmm-mega nsmm-mega-wide';
			$cols  = (int) get_post_meta( $parent_item->ID, '_nsmm_columns', true );
			$cols  = $cols ? $cols : 4;

			$output .= "\n{$indent}<div class=\"" . esc_attr( $class ) . "\" data-cols=\"" . esc_attr( $cols ) . "\"><ul class=\"nsmm-mega-list\">\n";
		} else {
			$output .= "\n{$indent}<ul class=\"sub-menu nsmm-sub\">\n";
		}
	}

	/**
	 * End Level - close of sub UL
	 * 
	 * @param string   $output Passed by reference. Used to append additional content.
	 * @param int      $depth  Depth of menu item.
	 * @param stdClass $args   An object of wp_nav_menu() arguments.
	 */
	public function end_lvl( &$output, $depth = 0, $args = null ) {
		$indent      = str_repeat( "\t", $depth );
		$parent_item = $this->get_current_parent_item();
		$mode        = $parent_item ? get_post_meta( $parent_item->ID, '_nsmm_mode', true ) : '';

		if ( 0 === $depth && in_array( $mode, array( 'mega-grid', 'mega-wide' ), true ) ) {
			$output .= "{$indent}</ul></div>\n";
		} else {
			$output .= "{$indent}</ul>\n";
		}
	}

	/**
	 * Start Element - output of menu item
	 * 
	 * @param string   $output Passed by reference. Used to append additional content.
	 * @param WP_Post  $item   Menu item data object.
	 * @param int      $depth  Depth of menu item.
	 * @param stdClass $args   An object of wp_nav_menu() arguments.
	 * @param int      $id     Current item ID.
	 */
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$classes      = empty( $item->classes ) ? array() : (array) $item->classes;
		$has_children = in_array( 'menu-item-has-children', $classes, true );

		// Build attributes
		$atts = '';
		$atts .= ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) . '"' : '';
		$atts .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target )     . '"' : '';
		$atts .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn )        . '"' : '';
		$atts .= ! empty( $item->url )        ? ' href="'   . esc_url( $item->url )         . '"' : ' href="#"';

		// Get menu item meta
		$title        = apply_filters( 'the_title', $item->title, $item->ID );
		$desc         = trim( $item->description );
		$thumb_id     = (int) get_post_meta( $item->ID, '_nsmm_thumb_id', true );
		$thumb_html   = $thumb_id ? wp_get_attachment_image( $thumb_id, 'medium', false, array( 'class' => 'nsmm-thumb' ) ) : '';

		// Parent mega mode detection
		$parent_item  = $this->get_current_parent_item();
		$parent_mode  = $parent_item ? get_post_meta( $parent_item->ID, '_nsmm_mode', true ) : '';

		// Build li classes
		$li_classes = array( 'menu-item', 'menu-item-' . $item->ID );
		if ( $has_children ) {
			$li_classes[] = 'menu-item-has-children';
		}
		if ( 0 === $depth ) {
			$li_classes[] = 'nsmm-top';
		}
		if ( 'mega-grid' === $parent_mode ) {
			$li_classes[] = 'nsmm-grid-item';
		}
		if ( 'mega-wide' === $parent_mode ) {
			$li_classes[] = 'nsmm-wide-item';
		}

		$output .= '<li class="' . esc_attr( implode( ' ', array_filter( $li_classes ) ) ) . '">';

		// Build anchor content
		$item_output = '<a class="nsmm-link"' . $atts . '>';

		// Add thumbnail for grid items
		if ( 'mega-grid' === $parent_mode && 1 === $depth && $thumb_html ) {
			$item_output .= '<span class="nsmm-thumbwrap">' . $thumb_html . '</span>';
		}

		$item_output .= '<span class="nsmm-title">' . esc_html( $title ) . '</span>';

		// Add description for mega menu items
		if ( $parent_mode && 1 === $depth && $desc ) {
			$item_output .= '<span class="nsmm-desc">' . esc_html( $desc ) . '</span>';
		}

		$item_output .= '</a>';

		$output .= $item_output;
	}

	/**
	 * End Element - close menu item
	 * 
	 * @param string   $output Passed by reference. Used to append additional content.
	 * @param WP_Post  $item   Menu item data object.
	 * @param int      $depth  Depth of menu item.
	 * @param stdClass $args   An object of wp_nav_menu() arguments.
	 */
	public function end_el( &$output, $item, $depth = 0, $args = null ) {
		$output .= "</li>\n";
	}

	/**
	 * Display Element - enhanced parent tracking
	 * 
	 * @param object $element           Data object.
	 * @param array  $children_elements List of elements to continue traversing.
	 * @param int    $max_depth         Max depth to traverse.
	 * @param int    $depth             Depth of current element.
	 * @param array  $args              Arguments.
	 * @param string $output            Passed by reference. Used to append additional content.
	 */
	public function display_element( $element, &$children_elements, $max_depth, $depth = 0, $args = array(), &$output = '' ) {
		if ( 0 === $depth ) {
			$this->parents_stack[ $depth ] = $element;
		} else {
			$this->parents_stack[ $depth ] = isset( $this->parents_stack[ $depth - 1 ] ) ? $this->parents_stack[ $depth - 1 ] : null;
		}

		parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );

		if ( isset( $this->parents_stack[ $depth ] ) && $this->parents_stack[ $depth ] === $element ) {
			unset( $this->parents_stack[ $depth ] );
		}
	}

	/**
	 * Get current parent item
	 * 
	 * @return object|null Parent item object
	 */
	protected function get_current_parent_item() {
		return isset( $this->parents_stack[0] ) ? $this->parents_stack[0] : null;
	}
}