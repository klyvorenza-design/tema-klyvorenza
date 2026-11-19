<?php
/**
 * Walker do menu principal.
 *
 * @package Klyvorenza
 */

defined( 'ABSPATH' ) || exit;

/**
 * Walker que gera o mesmo HTML do menu automático (sanfona no mobile, submenu no desktop).
 */
class Klyv_Walker_Nav extends Walker_Nav_Menu {

	/**
	 * Prefixo dos IDs dos submenus (evita IDs repetidos).
	 *
	 * @var string
	 */
	private $prefix;

	/**
	 * ID do item atual.
	 *
	 * @var int
	 */
	private $cur = 0;

	/**
	 * Construtor.
	 *
	 * @param string $prefix Prefixo dos IDs.
	 */
	public function __construct( $prefix = 'klyv-sub-' ) {
		$this->prefix = $prefix;
	}

	/**
	 * Abre a lista de submenu.
	 *
	 * @param string   $output Saída.
	 * @param int      $depth  Profundidade.
	 * @param stdClass $args   Argumentos.
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ) {
		$output .= '<ul class="klyv-sub" id="' . esc_attr( $this->prefix . $this->cur ) . '" role="list">';
	}

	/**
	 * Fecha a lista de submenu.
	 *
	 * @param string   $output Saída.
	 * @param int      $depth  Profundidade.
	 * @param stdClass $args   Argumentos.
	 */
	public function end_lvl( &$output, $depth = 0, $args = null ) {
		$output .= '</ul>';
	}

	/**
	 * Abre o item.
	 *
	 * @param string   $output Saída.
	 * @param WP_Post  $item   Item.
	 * @param int      $depth  Profundidade.
	 * @param stdClass $args   Argumentos.
	 * @param int      $id     ID.
	 */
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$kids  = 0 === $depth && in_array( 'menu-item-has-children', (array) $item->classes, true ) && ( empty( $args->depth ) || (int) $args->depth > 1 );
		$title = esc_html( apply_filters( 'nav_menu_item_title', $item->title, $item, $args, $depth ) );
		$url   = (string) $item->url;
		$nolnk = '' === $url || '#' === $url;
		$cur   = ! empty( $item->current ) || ! empty( $item->current_item_ancestor );

		$this->cur = $item->ID;
		$output   .= '<li class="' . ( 0 === $depth ? 'klyv-menu__item' : '' ) . ( $kids ? ' has-sub' : '' ) . '">';
		$ctrl      = $this->prefix . $item->ID;

		if ( $kids && $nolnk ) {
			/* translators: %s: item do menu. */
			$lbl     = sprintf( __( 'Abrir submenu de %s', 'klyvorenza' ), wp_strip_all_tags( $item->title ) );
			$output .= '<button type="button" class="klyv-sub-toggle klyv-sub-toggle--label" aria-expanded="false" aria-controls="' . esc_attr( $ctrl ) . '" aria-label="' . esc_attr( $lbl ) . '"><span>' . $title . '</span>' . klyv_icon( 'chevron' ) . '</button>';
			return;
		}
		$attr    = '';
		$attr   .= $item->target ? ' target="' . esc_attr( $item->target ) . '"' : '';
		$attr   .= $item->target ? ' rel="noopener"' : '';
		$attr   .= $cur ? ' aria-current="page"' : '';
		$output .= '<a href="' . esc_url( $nolnk ? home_url( '/' ) : $url ) . '"' . $attr . '>' . $title . '</a>';
		if ( $kids ) {
			/* translators: %s: item do menu. */
			$lbl     = sprintf( __( 'Abrir submenu de %s', 'klyvorenza' ), wp_strip_all_tags( $item->title ) );
			$output .= '<button type="button" class="klyv-sub-toggle" aria-expanded="false" aria-controls="' . esc_attr( $ctrl ) . '" aria-label="' . esc_attr( $lbl ) . '">' . klyv_icon( 'chevron' ) . '</button>';
		}
	}

	/**
	 * Fecha o item.
	 *
	 * @param string   $output Saída.
	 * @param WP_Post  $item   Item.
	 * @param int      $depth  Profundidade.
	 * @param stdClass $args   Argumentos.
	 */
	public function end_el( &$output, $item, $depth = 0, $args = null ) {
		$output .= '</li>';
	}
}
