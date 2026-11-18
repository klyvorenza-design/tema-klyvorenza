<?php
/**
 * Cabeçalho, menu principal automático, busca e rodapé.
 *
 * @package Klyvorenza
 */

defined( 'ABSPATH' ) || exit;

/**
 * Localiza o "pai" de um grupo do menu: categoria (preferida) ou página.
 *
 * @param string[] $slugs Slugs candidatos, em ordem.
 * @return array|null { type: term|page, obj }
 */
function klyv_find_parent( $slugs ) {
	foreach ( array_filter( $slugs ) as $slug ) {
		$t = get_term_by( 'slug', $slug, 'category' );
		if ( $t && ! is_wp_error( $t ) ) {
			return array( 'type' => 'term', 'obj' => $t );
		}
	}
	foreach ( array_filter( $slugs ) as $slug ) {
		$p = get_page_by_path( $slug );
		if ( $p ) {
			return array( 'type' => 'page', 'obj' => $p );
		}
	}
	return null;
}

/**
 * Filhos de um item do menu (só categorias com artigos publicados, ou páginas filhas).
 *
 * @param array|null $parent Retorno de klyv_find_parent().
 * @param string[]   $skip   Slugs a ignorar.
 * @return array[] Cada item: title, url.
 */
function klyv_menu_children( $parent, $skip = array() ) {
	if ( ! $parent ) {
		return array();
	}
	$key = 'menu_' . $parent['type'] . '_' . ( 'term' === $parent['type'] ? $parent['obj']->term_id : $parent['obj']->ID );
	return klyv_cached(
		$key . implode( ',', $skip ),
		function () use ( $parent, $skip ) {
			$out = array();
			if ( 'term' === $parent['type'] ) {
				$terms = get_terms(
					array(
						'taxonomy'   => 'category',
						'parent'     => $parent['obj']->term_id,
						'hide_empty' => true,
						'orderby'    => 'name',
					)
				);
				foreach ( is_wp_error( $terms ) ? array() : $terms as $t ) {
					if ( in_array( $t->slug, $skip, true ) ) {
						continue;
					}
					$out[] = array( 'title' => $t->name, 'url' => get_term_link( $t ) );
				}
			} else {
				foreach ( get_pages( array( 'child_of' => $parent['obj']->ID, 'parent' => $parent['obj']->ID, 'sort_column' => 'menu_order,post_title' ) ) as $pg ) {
					$out[] = array( 'title' => get_the_title( $pg ), 'url' => get_permalink( $pg ) );
				}
			}
			return $out;
		}
	);
}

/**
 * Itens do menu principal, na ordem pedida.
 *
 * @return array[]
 */
function klyv_main_menu() {
	$blog_id = (int) get_option( 'page_for_posts' );
	$blog    = $blog_id ? get_permalink( $blog_id ) : home_url( '/blog/' );

	$gloss_page = get_page_by_path( 'glossario' );
	$gloss      = $gloss_page ? get_permalink( $gloss_page ) : home_url( '/glossario/' );

	$stories = klyv_story_types();
	$st_url  = $stories ? get_post_type_archive_link( $stories[0] ) : '';
	$st_url  = $st_url ? $st_url : home_url( '/fws-story/' );

	$esp   = klyv_find_parent( array( klyv_opt( 'parent_especiais' ), 'especiais' ) );
	$datas = klyv_find_parent( array( klyv_opt( 'parent_datas' ), 'datas-festivas', 'datas-comemorativas' ) );

	$esp_url   = $esp ? ( 'term' === $esp['type'] ? get_term_link( $esp['obj'] ) : get_permalink( $esp['obj'] ) ) : home_url( '/categorias/' );
	$datas_url = $datas ? ( 'term' === $datas['type'] ? get_term_link( $datas['obj'] ) : get_permalink( $datas['obj'] ) ) : home_url( '/categorias/' );

	// Datas comemorativas dentro de Especiais não duplicam o item "Datas Festivas".
	$skip = array( 'datas-comemorativas', 'datas-festivas', 'natal', 'ano-novo' );

	$items = array(
		array( 'title' => __( 'Iniciar', 'klyvorenza' ), 'url' => home_url( '/' ), 'children' => array() ),
		array( 'title' => __( 'Blog', 'klyvorenza' ), 'url' => $blog, 'children' => array() ),
		array( 'title' => __( 'Glossário', 'klyvorenza' ), 'url' => $gloss, 'children' => array() ),
		array( 'title' => __( 'Web Stories', 'klyvorenza' ), 'url' => $st_url, 'children' => array() ),
		array( 'title' => __( 'Especiais', 'klyvorenza' ), 'url' => is_wp_error( $esp_url ) ? '#' : $esp_url, 'children' => klyv_menu_children( $esp, $skip ) ),
		array( 'title' => __( 'Datas Festivas', 'klyvorenza' ), 'url' => is_wp_error( $datas_url ) ? '#' : $datas_url, 'children' => klyv_menu_children( $datas ) ),
	);
	return apply_filters( 'klyvorenza_main_menu', $items );
}

/**
 * Menu como HTML (usado no cabeçalho e no rodapé).
 *
 * @param string $class Classe.
 * @param bool   $subs  Inclui submenus interativos.
 * @return string
 */
function klyv_menu_html( $class = 'klyv-nav', $subs = true ) {
	global $wp;
	$cur  = home_url( isset( $wp->request ) ? $wp->request : '' );
	$html = '<ul class="klyv-menu" role="list">';
	foreach ( klyv_main_menu() as $i => $it ) {
		$kids   = $subs ? $it['children'] : array();
		$is_cur = untrailingslashit( $cur ) === untrailingslashit( $it['url'] );
		$html  .= '<li class="klyv-menu__item' . ( $kids ? ' has-sub' : '' ) . '">';
		$html  .= '<a href="' . esc_url( $it['url'] ) . '"' . ( $is_cur ? ' aria-current="page"' : '' ) . '>' . esc_html( $it['title'] ) . '</a>';
		if ( $kids ) {
			$id    = 'klyv-sub-' . $i;
			$html .= '<button type="button" class="klyv-sub-toggle" aria-expanded="false" aria-controls="' . esc_attr( $id ) . '" aria-label="' . esc_attr( sprintf( /* translators: %s: item do menu */ __( 'Abrir submenu de %s', 'klyvorenza' ), $it['title'] ) ) . '">' . klyv_icon( 'chevron' ) . '</button>';
			$html .= '<ul class="klyv-sub" id="' . esc_attr( $id ) . '" role="list">';
			foreach ( $kids as $k ) {
				$html .= '<li><a href="' . esc_url( $k['url'] ) . '">' . esc_html( $k['title'] ) . '</a></li>';
			}
			$html .= '</ul>';
		}
		$html .= '</li>';
	}
	return '<nav class="' . esc_attr( $class ) . '" aria-label="' . esc_attr__( 'Menu principal', 'klyvorenza' ) . '">' . $html . '</ul></nav>';
}

/**
 * Categorias sugeridas na busca.
 *
 * @return array[]
 */
function klyv_popular_cats() {
	return klyv_cached(
		'popular_cats',
		function () {
			$slugs = array_filter( array_map( 'trim', explode( ',', (string) klyv_opt( 'popular_cats', '' ) ) ) );
			$args  = array( 'taxonomy' => 'category', 'hide_empty' => true, 'number' => 8, 'orderby' => 'count', 'order' => 'DESC' );
			if ( $slugs ) {
				$args['slug']    = $slugs;
				$args['orderby'] = 'name';
			}
			$out = array();
			foreach ( (array) get_terms( $args ) as $t ) {
				if ( $t instanceof WP_Term && 'uncategorized' !== $t->slug && 'sem-categoria' !== $t->slug ) {
					$out[] = array( 'name' => $t->name, 'url' => get_term_link( $t ) );
				}
			}
			return $out;
		}
	);
}

/** Formulário de busca. */
function klyv_search_form( $id = 'klyv-q' ) {
	return '<form role="search" method="get" class="klyv-search-form" action="' . esc_url( home_url( '/' ) ) . '">'
		. '<label class="screen-reader-text" for="' . esc_attr( $id ) . '">' . esc_html__( 'Pesquisar no site', 'klyvorenza' ) . '</label>'
		. '<input id="' . esc_attr( $id ) . '" type="search" name="s" value="' . esc_attr( get_search_query() ) . '" placeholder="' . esc_attr__( 'O que você procura?', 'klyvorenza' ) . '" enterkeyhint="search" autocomplete="off" required>'
		. '<button type="submit">' . esc_html__( 'Pesquisar', 'klyvorenza' ) . '</button></form>';
}

/** Cabeçalho completo. */
function klyv_render_header() {
	$pop = '';
	foreach ( klyv_popular_cats() as $c ) {
		$pop .= '<li><a href="' . esc_url( $c['url'] ) . '">' . esc_html( $c['name'] ) . '</a></li>';
	}
	ob_start();
	?>
	<a class="klyv-skip screen-reader-text" href="#conteudo"><?php esc_html_e( 'Pular para o conteúdo', 'klyvorenza' ); ?></a>
	<div class="klyv-topbar"><div class="klyv-topbar__in"><?php echo klyv_gtranslate_html(); // phpcs:ignore WordPress.Security.EscapeOutput ?></div></div>
	<div class="klyv-head">
		<div class="klyv-head__in">
			<a class="klyv-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"><?php echo klyv_logo_img( 'logo', array( 'priority' => true ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
			<?php echo klyv_menu_html(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<div class="klyv-actions">
				<button type="button" class="klyv-btn-i" id="klyv-open-search" aria-label="<?php esc_attr_e( 'Pesquisar', 'klyvorenza' ); ?>" aria-controls="klyv-search" aria-expanded="false"><?php echo klyv_icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
				<button type="button" class="klyv-btn-i" id="klyv-theme" aria-pressed="false" aria-label="<?php esc_attr_e( 'Ativar modo escuro', 'klyvorenza' ); ?>"><span class="i-moon"><?php echo klyv_icon( 'moon' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span><span class="i-sun"><?php echo klyv_icon( 'sun' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span></button>
				<button type="button" class="klyv-btn-i klyv-burger" id="klyv-burger" aria-label="<?php esc_attr_e( 'Abrir menu', 'klyvorenza' ); ?>" aria-controls="klyv-drawer" aria-expanded="false"><?php echo klyv_icon( 'menu' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
			</div>
		</div>
		<?php
		// Painel mobile: reaproveita o mesmo menu em sanfona.
		?>
		<div class="klyv-drawer" id="klyv-drawer" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Menu', 'klyvorenza' ); ?>" hidden>
			<div class="klyv-drawer__bar">
				<?php echo klyv_logo_img( 'logo' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<button type="button" class="klyv-btn-i" data-close aria-label="<?php esc_attr_e( 'Fechar menu', 'klyvorenza' ); ?>"><?php echo klyv_icon( 'close' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
			</div>
			<?php echo klyv_menu_html( 'klyv-nav klyv-nav--drawer' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<div class="klyv-drawer__extra"><?php echo klyv_search_form( 'klyv-q-m' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
		</div>
		<div class="klyv-drawer-back" data-close hidden></div>
	</div>
	<div class="klyv-search" id="klyv-search" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Pesquisar', 'klyvorenza' ); ?>" hidden>
		<div class="klyv-search__box">
			<button type="button" class="klyv-btn-i klyv-search__x" data-close aria-label="<?php esc_attr_e( 'Fechar pesquisa', 'klyvorenza' ); ?>"><?php echo klyv_icon( 'close' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
			<?php echo klyv_search_form(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php if ( $pop ) : ?>
				<p class="klyv-search__lbl"><?php esc_html_e( 'Categorias populares', 'klyvorenza' ); ?></p>
				<ul class="klyv-chips" role="list"><?php echo $pop; // phpcs:ignore WordPress.Security.EscapeOutput ?></ul>
			<?php endif; ?>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

/** Rodapé. */
function klyv_render_footer() {
	$pages = array(
		'sobre'                  => __( 'Sobre', 'klyvorenza' ),
		'galeria'                => __( 'Galeria', 'klyvorenza' ),
		'contato'                => __( 'Contato', 'klyvorenza' ),
		'links-na-bio'           => __( 'Links na Bio', 'klyvorenza' ),
		'mapa-do-site'           => __( 'Mapa do Site', 'klyvorenza' ),
		'politica-de-privacidade' => __( 'Política de Privacidade', 'klyvorenza' ),
		'termos-de-uso'          => __( 'Termos de Uso', 'klyvorenza' ),
	);
	$li = '';
	foreach ( $pages as $slug => $label ) {
		$p   = get_page_by_path( $slug );
		$url = $p ? get_permalink( $p ) : home_url( '/' . $slug . '/' );
		$li .= '<li><a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a></li>';
	}
	ob_start();
	?>
	<div class="klyv-foot">
		<div class="klyv-foot__in">
			<div class="klyv-foot__brand">
				<a class="klyv-foot__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php echo klyv_logo_img( 'simbolo', array( 'alt' => '', 'lazy' => true ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span></a>
				<p><?php echo esc_html( get_bloginfo( 'description' ) ? get_bloginfo( 'description' ) : __( 'Moda, beleza e cuidados para o seu dia a dia.', 'klyvorenza' ) ); ?></p>
				<?php echo klyv_socials_html( 'klyv-social--foot' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</div>
			<div>
				<h2 class="klyv-foot__h"><?php esc_html_e( 'Explorar', 'klyvorenza' ); ?></h2>
				<?php echo klyv_menu_html( 'klyv-foot__nav', false ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</div>
			<div>
				<h2 class="klyv-foot__h"><?php esc_html_e( 'Institucional', 'klyvorenza' ); ?></h2>
				<ul class="klyv-foot__nav" role="list"><?php echo $li; // phpcs:ignore WordPress.Security.EscapeOutput ?></ul>
			</div>
		</div>
		<p class="klyv-foot__copy">
			<?php
			/* translators: 1: ano, 2: nome do site. */
			echo esc_html( sprintf( __( '© %1$s %2$s. Todos os direitos reservados.', 'klyvorenza' ), gmdate( 'Y' ), get_bloginfo( 'name' ) ) );
			?>
		</p>
	</div>
	<?php
	return ob_get_clean();
}
