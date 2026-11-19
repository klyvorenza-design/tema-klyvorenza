<?php
/**
 * Boas-vindas: ao ativar, abre direto o painel do tema e oferece o preparo das páginas.
 *
 * @package Klyvorenza
 */

defined( 'ABSPATH' ) || exit;

/** Redireciona para o painel do tema logo após a ativação. */
add_action(
	'admin_init',
	function () {
		if ( ! get_option( 'klyv_welcome' ) ) {
			return;
		}
		delete_option( 'klyv_welcome' );
		// Não redireciona em ativação em lote, requisições AJAX ou sem permissão.
		if ( wp_doing_ajax() || is_network_admin() || isset( $_GET['activate-multi'] ) || ! current_user_can( 'manage_options' ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return;
		}
		wp_safe_redirect( admin_url( 'themes.php?page=klyvorenza&bem-vinda=1' ) );
		exit;
	}
);

/**
 * Páginas que o tema usa e que ainda não existem.
 *
 * @return array Slug => título.
 */
function klyv_missing_pages() {
	$want = array(
		'blog'       => __( 'Blog', 'klyvorenza' ),
		'categorias' => __( 'Categorias', 'klyvorenza' ),
		'glossario'  => __( 'Glossário', 'klyvorenza' ),
	);
	$out  = array();
	foreach ( $want as $slug => $title ) {
		if ( ! get_page_by_path( $slug ) ) {
			$out[ $slug ] = $title;
		}
	}
	return $out;
}

/** Cria só as páginas que faltam. Nada existente é alterado ou apagado. */
add_action(
	'admin_post_klyv_setup_pages',
	function () {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Sem permissão.', 'klyvorenza' ), '', array( 'response' => 403 ) );
		}
		check_admin_referer( 'klyv_setup_pages' );
		foreach ( klyv_missing_pages() as $slug => $title ) {
			$id = wp_insert_post(
				array(
					'post_type'   => 'page',
					'post_status' => 'publish',
					'post_title'  => $title,
					'post_name'   => $slug,
				)
			);
			if ( $id && 'blog' === $slug && ! get_option( 'page_for_posts' ) ) {
				update_option( 'page_for_posts', $id );
			}
		}
		flush_rewrite_rules( false );
		klyv_bump_cache();
		wp_safe_redirect( admin_url( 'themes.php?page=klyvorenza&pronto=1' ) );
		exit;
	}
);

/** Cria o menu padrão a pedido (só se ainda não houver um menu no local principal). */
add_action(
	'admin_post_klyv_setup_menu',
	function () {
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			wp_die( esc_html__( 'Sem permissão.', 'klyvorenza' ), '', array( 'response' => 403 ) );
		}
		check_admin_referer( 'klyv_setup_menu' );
		klyv_create_default_menu();
		klyv_bump_cache();
		wp_safe_redirect( admin_url( 'themes.php?page=klyvorenza&pronto=1' ) );
		exit;
	}
);

/** Caixa de boas-vindas no topo da página de opções. */
function klyv_render_welcome() {
	// phpcs:disable WordPress.Security.NonceVerification
	$new  = isset( $_GET['bem-vinda'] );
	$done = isset( $_GET['pronto'] );
	// phpcs:enable
	$miss = klyv_missing_pages();
	if ( $done ) {
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Páginas criadas. O tema está pronto para usar.', 'klyvorenza' ) . '</p></div>';
	}
	echo '<div class="card" style="max-width:820px;padding:1px 20px 14px">';
	echo '<h2>' . esc_html( $new ? __( 'Klyvorenza ativado. Falta pouco.', 'klyvorenza' ) : __( 'Primeiros passos', 'klyvorenza' ) ) . '</h2><ol>';
	$blog = (int) get_option( 'page_for_posts' );
	/* translators: %s: estado. */
	echo '<li>' . esc_html( sprintf( __( 'Página de posts (Blog): %s', 'klyvorenza' ), $blog ? __( 'definida', 'klyvorenza' ) : __( 'não definida', 'klyvorenza' ) ) ) . '</li>';
	echo '<li>' . esc_html( $miss ? sprintf( /* translators: %s: lista de páginas. */ __( 'Páginas que faltam: %s', 'klyvorenza' ), implode( ', ', $miss ) ) : __( 'Todas as páginas do tema já existem (Blog, Categorias, Glossário).', 'klyvorenza' ) ) . '</li>';
	echo '<li>' . esc_html__( 'Confira os slugs de Especiais e Datas Festivas e as redes sociais abaixo.', 'klyvorenza' ) . '</li></ol><p>';
	if ( $miss ) {
		echo '<a class="button button-primary" href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=klyv_setup_pages' ), 'klyv_setup_pages' ) ) . '">' . esc_html__( 'Criar as páginas que faltam', 'klyvorenza' ) . '</a> ';
	}
	echo '<a class="button" href="' . esc_url( admin_url( 'nav-menus.php' ) ) . '">' . esc_html__( 'Editar o menu principal', 'klyvorenza' ) . '</a> ';
	if ( ! has_nav_menu( 'principal' ) ) {
		echo '<a class="button" href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=klyv_setup_menu' ), 'klyv_setup_menu' ) ) . '">' . esc_html__( 'Criar o menu principal', 'klyvorenza' ) . '</a> ';
	}
	echo '<a class="button" href="' . esc_url( home_url( '/' ) ) . '" target="_blank" rel="noopener">' . esc_html__( 'Ver o site', 'klyvorenza' ) . '</a> ';
	echo '<a class="button" href="' . esc_url( admin_url( 'site-editor.php' ) ) . '">' . esc_html__( 'Abrir o editor do site', 'klyvorenza' ) . '</a></p></div>';
}

/** Migração única (1.0.2): inclui o TikTok nos botões de compartilhamento já salvos. */
add_action(
	'admin_init',
	function () {
		if ( get_option( 'klyv_mig_102' ) ) {
			return;
		}
		update_option( 'klyv_mig_102', 1, false );
		$o = get_option( 'klyvorenza_options' );
		if ( is_array( $o ) && isset( $o['share_buttons'] ) && is_array( $o['share_buttons'] ) && ! in_array( 'tiktok', $o['share_buttons'], true ) ) {
			$pos = array_search( 'telegram', $o['share_buttons'], true );
			array_splice( $o['share_buttons'], false === $pos ? count( $o['share_buttons'] ) : $pos + 1, 0, 'tiktok' );
			update_option( 'klyvorenza_options', $o );
		}
	}
);
