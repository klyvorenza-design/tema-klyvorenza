<?php
/**
 * Menu do WordPress (Aparência > Menus) no menu principal, com menu padrão automático.
 *
 * @package Klyvorenza
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/class-klyv-walker-nav.php';

add_action(
	'after_setup_theme',
	function () {
		add_theme_support( 'menus' );
		register_nav_menus( array( 'principal' => __( 'Menu principal', 'klyvorenza' ) ) );
	}
);

/**
 * Categorias permitidas nos submenus automáticos, na ordem de exibição.
 *
 * @return array[] especiais e datas: listas de slugs.
 */
function klyv_menu_whitelist() {
	return array(
		'especiais' => array( 'perfumes-e-fragrancias', 'moda-gestante', 'praias-do-brasil', 'historia-da-moda', 'moda-por-estado' ),
		'datas'     => array( 'natal', 'ano-novo' ),
	);
}

/**
 * Menu do WordPress renderizado, ou vazio se nenhum menu estiver atribuído.
 *
 * @param string $class  Classes do <nav>.
 * @param bool   $subs   Inclui submenus.
 * @param string $prefix Prefixo de IDs.
 * @return string
 */
function klyv_wp_menu_html( $class, $subs, $prefix ) {
	if ( ! has_nav_menu( 'principal' ) ) {
		return '';
	}
	$html = wp_nav_menu(
		array(
			'theme_location'       => 'principal',
			'container'            => 'nav',
			'container_class'      => $class,
			'container_aria_label' => __( 'Menu principal', 'klyvorenza' ),
			'menu_class'           => 'klyv-menu',
			'items_wrap'           => '<ul class="%2$s" role="list">%3$s</ul>',
			'walker'               => new Klyv_Walker_Nav( $prefix ),
			'depth'                => $subs ? 2 : 1,
			'fallback_cb'          => false,
			'echo'                 => false,
		)
	);
	return $html ? $html : '';
}

/**
 * Cria o "Menu principal" com os itens padrão e o atribui ao local, se ainda não houver menu.
 * Só cria; nunca altera um menu existente.
 *
 * @return bool Verdadeiro se criou.
 */
function klyv_create_default_menu() {
	$locs = get_nav_menu_locations();
	if ( ! empty( $locs['principal'] ) && wp_get_nav_menu_object( $locs['principal'] ) ) {
		return false;
	}
	$menu = wp_get_nav_menu_object( 'Menu principal' );
	$mid  = $menu ? (int) $menu->term_id : (int) wp_create_nav_menu( 'Menu principal' );
	if ( ! $mid || is_wp_error( $mid ) ) {
		return false;
	}
	if ( $menu && wp_get_nav_menu_items( $mid ) ) {
		$locs['principal'] = $mid;
		set_theme_mod( 'nav_menu_locations', $locs );
		return true;
	}
	$add  = function ( $args ) use ( $mid ) {
		$id = wp_update_nav_menu_item( $mid, 0, array_merge( array( 'menu-item-status' => 'publish' ), $args ) );
		return is_wp_error( $id ) ? 0 : (int) $id;
	};
	$page = function ( $slug, $title, $fallback ) use ( $add ) {
		$p = get_page_by_path( $slug );
		if ( $p ) {
			return $add(
				array(
					'menu-item-title'     => $title,
					'menu-item-type'      => 'post_type',
					'menu-item-object'    => 'page',
					'menu-item-object-id' => $p->ID,
				)
			);
		}
		return $add(
			array(
				'menu-item-title' => $title,
				'menu-item-type'  => 'custom',
				'menu-item-url'   => $fallback,
			)
		);
	};
	$term = function ( $slugs, $title, $parent ) use ( $add ) {
		foreach ( (array) $slugs as $slug ) {
			$t = get_term_by( 'slug', $slug, 'category' );
			if ( $t ) {
				return $add(
					array(
						'menu-item-title'     => $title,
						'menu-item-type'      => 'taxonomy',
						'menu-item-object'    => 'category',
						'menu-item-object-id' => $t->term_id,
						'menu-item-parent-id' => $parent,
					)
				);
			}
		}
		return 0;
	};

	$add(
		array(
			'menu-item-title' => __( 'Iniciar', 'klyvorenza' ),
			'menu-item-type'  => 'custom',
			'menu-item-url'   => home_url( '/' ),
		)
	);
	$page( 'blog', __( 'Blog', 'klyvorenza' ), home_url( '/blog/' ) );
	$page( 'glossario', __( 'Glossário', 'klyvorenza' ), home_url( '/glossario/' ) );
	$st = klyv_story_types();
	$add(
		array(
			'menu-item-title' => __( 'Web Stories', 'klyvorenza' ),
			'menu-item-type'  => 'custom',
			'menu-item-url'   => ( $st && get_post_type_archive_link( $st[0] ) ) ? get_post_type_archive_link( $st[0] ) : home_url( '/fws-story/' ),
		)
	);

	$wl  = klyv_menu_whitelist();
	$esp = $term( array( 'especiais' ), __( 'Especiais', 'klyvorenza' ), 0 );
	if ( ! $esp ) {
		$esp = $add(
			array(
				'menu-item-title' => __( 'Especiais', 'klyvorenza' ),
				'menu-item-type'  => 'custom',
				'menu-item-url'   => '#',
			)
		);
	}
	foreach ( $wl['especiais'] as $slug ) {
		$t = get_term_by( 'slug', $slug, 'category' );
		if ( $t ) {
			$term( array( $slug ), $t->name, $esp );
		}
	}
	$dat = $term( array( 'datas-festivas', 'datas-comemorativas' ), __( 'Datas Festivas', 'klyvorenza' ), 0 );
	if ( ! $dat ) {
		$dat = $add(
			array(
				'menu-item-title' => __( 'Datas Festivas', 'klyvorenza' ),
				'menu-item-type'  => 'custom',
				'menu-item-url'   => '#',
			)
		);
	}
	foreach ( $wl['datas'] as $slug ) {
		$t = get_term_by( 'slug', $slug, 'category' );
		if ( $t ) {
			$term( array( $slug ), $t->name, $dat );
		}
	}
	$locs['principal'] = $mid;
	set_theme_mod( 'nav_menu_locations', $locs );
	return true;
}

/** Uma vez por site (também em atualização do tema): cria o menu padrão se não houver um. */
add_action(
	'admin_init',
	function () {
		if ( get_option( 'klyv_menu_done' ) || ! current_user_can( 'edit_theme_options' ) || wp_doing_ajax() ) {
			return;
		}
		klyv_create_default_menu();
		update_option( 'klyv_menu_done', 1, false );
		klyv_bump_cache();
	},
	5
);
