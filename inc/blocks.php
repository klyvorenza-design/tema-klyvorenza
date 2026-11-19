<?php
/**
 * Registro dos blocos dinâmicos (renderizados no servidor, sem build de JS).
 *
 * @package Klyvorenza
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registra um bloco só de servidor (aparece no editor com controles automáticos).
 *
 * @param string   $name  Nome sem prefixo.
 * @param string   $title Título.
 * @param callable $cb    Renderização.
 * @param array    $attrs Atributos.
 */
function klyv_block( $name, $title, $cb, $attrs = array() ) {
	register_block_type(
		'klyvorenza/' . $name,
		array(
			'api_version'     => 3,
			'title'           => $title,
			'category'        => 'theme',
			'render_callback' => $cb,
			'attributes'      => $attrs,
			'supports'        => array(
				'autoRegister' => true,
				'html'         => false,
				'align'        => array( 'wide', 'full' ),
				'className'    => true,
			),
		)
	);
}

add_action(
	'init',
	function () {
		klyv_block(
			'cabecalho',
			__( 'Cabeçalho Klyvorenza', 'klyvorenza' ),
			'klyv_render_header',
			array(
				'variant' => array(
					'type'    => 'string',
					'default' => '',
				),
			)
		);
		klyv_block( 'rodape', __( 'Rodapé Klyvorenza', 'klyvorenza' ), 'klyv_render_footer' );
		klyv_block( 'slide', __( 'Slide de destaques', 'klyvorenza' ), 'klyv_render_slide' );
		klyv_block( 'home-secoes', __( 'Seções da home (categorias automáticas)', 'klyvorenza' ), 'klyv_render_home_sections' );
		klyv_block( 'ultimos', __( 'Últimos artigos', 'klyvorenza' ), 'klyv_render_latest' );
		klyv_block(
			'stories',
			__( 'Cartões de Web Stories', 'klyvorenza' ),
			'klyv_render_stories',
			array(
				'count' => array(
					'type'    => 'number',
					'default' => 8,
				),
			)
		);
		klyv_block( 'glossario', __( 'Glossário A a Z', 'klyvorenza' ), 'klyv_render_glossary' );
		klyv_block( 'categorias', __( 'Hub de categorias', 'klyvorenza' ), 'klyv_render_category_hub' );
		klyv_block( 'imagem-principal', __( 'Imagem principal do artigo', 'klyvorenza' ), 'klyv_render_main_image' );
		klyv_block( 'migalhas', __( 'Migalhas de pão', 'klyvorenza' ), 'klyv_render_breadcrumbs' );
		klyv_block(
			'compartilhar',
			__( 'Botões de compartilhamento', 'klyvorenza' ),
			'klyv_render_share',
			array(
				'sticky' => array(
					'type'    => 'boolean',
					'default' => false,
				),
			)
		);
		klyv_block( 'relacionados', __( 'Artigos relacionados', 'klyvorenza' ), 'klyv_render_related' );
		klyv_block( 'caixa-autor', __( 'Caixa do autor', 'klyvorenza' ), 'klyv_render_author_box' );
		klyv_block( 'newsletter', __( 'Newsletter (formulário)', 'klyvorenza' ), 'klyv_render_newsletter' );
		klyv_block(
			'barra',
			__( 'Barra lateral (anúncio)', 'klyvorenza' ),
			'klyv_render_sidebar_ad',
			array(
				'side' => array(
					'type'    => 'string',
					'default' => 'left',
				),
			)
		);
		klyv_block( 'categorias-chips', __( 'Filtro por categoria', 'klyvorenza' ), 'klyv_render_cat_filter' );
		klyv_block( 'redes', __( 'Redes sociais', 'klyvorenza' ), 'klyv_render_socials_block' );
		klyv_block( 'termo-pesquisado', __( 'Termo pesquisado e sugestões', 'klyvorenza' ), 'klyv_render_search_helper' );
		klyv_block( 'data-atualizacao', __( 'Data de atualização', 'klyvorenza' ), 'klyv_render_modified' );
	}
);

/** Bloco de redes sociais. */
function klyv_render_socials_block() {
	$h = klyv_socials_html();
	return $h ? '<div class="klyv-socials-wrap">' . $h . '</div>' : '';
}

/** Data de atualização (E-E-A-T). */
function klyv_render_modified() {
	if ( ! in_the_loop() && ! is_singular() ) {
		return '';
	}
	$pub = get_the_date( 'c' );
	$mod = get_the_modified_date( 'c' );
	if ( ! $mod || $mod === $pub ) {
		return '';
	}
	return '<p class="klyv-mod">' . esc_html__( 'Atualizado em', 'klyvorenza' ) . ' <time datetime="' . esc_attr( $mod ) . '">' . esc_html( get_the_modified_date() ) . '</time></p>';
}

/**
 * Cartão de artigo.
 *
 * @param int   $id   ID do post.
 * @param array $args eager, size.
 * @return string
 */
function klyv_card( $id, $args = array() ) {
	$args  = wp_parse_args(
		$args,
		array(
			'excerpt' => true,
			'size'    => 'klyv-card',
			'sizes'   => '(max-width:600px) 92vw, (max-width:1024px) 46vw, 380px',
		)
	);
	$cats  = get_the_category( $id );
	$cat   = $cats ? $cats[0] : null;
	$thumb = has_post_thumbnail( $id )
		? get_the_post_thumbnail(
			$id,
			$args['size'],
			array(
				'loading'  => 'lazy',
				'decoding' => 'async',
				'sizes'    => $args['sizes'],
				'alt'      => '',
			)
		)
		: '<span class="klyv-card__ph" aria-hidden="true"></span>';
	$h     = '<article class="klyv-card">';
	$h    .= '<a class="klyv-card__img" href="' . esc_url( get_permalink( $id ) ) . '" tabindex="-1" aria-hidden="true">' . $thumb . '</a>';
	$h    .= '<div class="klyv-card__body">';
	if ( $cat ) {
		$h .= '<a class="klyv-chip" href="' . esc_url( get_category_link( $cat ) ) . '">' . esc_html( $cat->name ) . '</a>';
	}
	$h .= '<h3 class="klyv-card__t"><a href="' . esc_url( get_permalink( $id ) ) . '">' . esc_html( get_the_title( $id ) ) . '</a></h3>';
	$h .= '<time class="klyv-card__d" datetime="' . esc_attr( get_the_date( 'c', $id ) ) . '">' . esc_html( get_the_date( '', $id ) ) . '</time>';
	if ( $args['excerpt'] ) {
		$h .= '<p class="klyv-card__x">' . esc_html( wp_trim_words( get_the_excerpt( $id ), 22, '…' ) ) . '</p>';
	}
	return $h . '</div></article>';
}

/** Migalhas de pão com marcação microdata mínima (o JSON-LD vai no head). */
function klyv_breadcrumb_items() {
	$items = array( array( __( 'Início', 'klyvorenza' ), home_url( '/' ) ) );
	if ( is_singular( 'post' ) ) {
		$cats = get_the_category();
		if ( $cats ) {
			$c   = $cats[0];
			$anc = array_reverse( get_ancestors( $c->term_id, 'category', 'taxonomy' ) );
			foreach ( $anc as $aid ) {
				$t       = get_term( $aid, 'category' );
				$items[] = array( $t->name, get_term_link( $t ) );
			}
			$items[] = array( $c->name, get_category_link( $c ) );
		}
		$items[] = array( get_the_title(), '' );
	} elseif ( is_category() ) {
		$c   = get_queried_object();
		$anc = array_reverse( get_ancestors( $c->term_id, 'category', 'taxonomy' ) );
		foreach ( $anc as $aid ) {
			$t       = get_term( $aid, 'category' );
			$items[] = array( $t->name, get_term_link( $t ) );
		}
		$items[] = array( $c->name, '' );
	} elseif ( is_page() ) {
		foreach ( array_reverse( get_post_ancestors( get_the_ID() ) ) as $pid ) {
			$items[] = array( get_the_title( $pid ), get_permalink( $pid ) );
		}
		$items[] = array( get_the_title(), '' );
	} elseif ( is_search() ) {
		$items[] = array( __( 'Busca', 'klyvorenza' ), '' );
	} elseif ( is_home() ) {
		$items[] = array( __( 'Blog', 'klyvorenza' ), '' );
	} elseif ( is_archive() ) {
		$items[] = array( wp_strip_all_tags( get_the_archive_title() ), '' );
	}
	return $items;
}

/** Bloco de migalhas. */
function klyv_render_breadcrumbs() {
	if ( is_front_page() ) {
		return '';
	}
	$items = klyv_breadcrumb_items();
	$last  = count( $items ) - 1;
	$h     = '<nav class="klyv-crumbs" aria-label="' . esc_attr__( 'Migalhas de pão', 'klyvorenza' ) . '"><ol>';
	foreach ( $items as $i => $it ) {
		$h .= '<li>' . ( $i < $last && $it[1] ? '<a href="' . esc_url( $it[1] ) . '">' . esc_html( $it[0] ) . '</a>' : '<span aria-current="page">' . esc_html( $it[0] ) . '</span>' ) . '</li>';
	}
	return $h . '</ol></nav>';
}

/** Caixa do autor. */
function klyv_render_author_box() {
	$id = (int) get_the_author_meta( 'ID' );
	if ( ! $id ) {
		return '';
	}
	$bio = get_the_author_meta( 'description', $id );
	$h   = '<aside class="klyv-author" aria-label="' . esc_attr__( 'Sobre a autora', 'klyvorenza' ) . '">';
	$h  .= get_avatar(
		$id,
		72,
		'',
		'',
		array(
			'loading' => 'lazy',
			'class'   => 'klyv-author__av',
		)
	);
	$h  .= '<div><p class="klyv-author__n">' . esc_html( get_the_author_meta( 'display_name', $id ) ) . '</p>';
	if ( $bio ) {
		$h .= '<p class="klyv-author__b">' . esc_html( $bio ) . '</p>';
	}
	$h .= '<a href="' . esc_url( get_author_posts_url( $id ) ) . '">' . esc_html__( 'Ver todos os artigos', 'klyvorenza' ) . '</a></div></aside>';
	return $h;
}

/** Relacionados por categoria. */
function klyv_render_related() {
	if ( ! is_singular( 'post' ) ) {
		return '';
	}
	$id   = get_the_ID();
	$cats = wp_get_post_categories( $id );
	if ( ! $cats ) {
		return '';
	}
	$ids = klyv_cached(
		'rel_' . $id,
		function () use ( $id, $cats ) {
			$q = new WP_Query(
				array(
					'post_type'              => 'post',
					'posts_per_page'         => 3,
					'post__not_in'           => array( $id ),
					'category__in'           => $cats,
					'fields'                 => 'ids',
					'no_found_rows'          => true,
					'ignore_sticky_posts'    => true,
					'update_post_term_cache' => false,
				)
			);
			return $q->posts;
		}
	);
	if ( ! $ids ) {
		return '';
	}
	$h = '<section class="klyv-related" aria-labelledby="klyv-rel-t"><h2 id="klyv-rel-t">' . esc_html__( 'Leia também', 'klyvorenza' ) . '</h2><div class="klyv-grid">';
	foreach ( $ids as $rid ) {
		$h .= klyv_card( $rid );
	}
	return $h . '</div></section>';
}

/**
 * Grupos da página de categorias: título e nomes na ordem pedida.
 *
 * @return array[]
 */
function klyv_category_groups() {
	return array(
		array(
			'title' => __( 'Moda Feminina', 'klyvorenza' ),
			'note'  => __( 'Confira as categorias abaixo.', 'klyvorenza' ),
			'names' => array(
				'Roupas'             => array( 'roupas' ),
				'Vestidos'           => array( 'vestidos' ),
				'Blusas'             => array( 'blusas' ),
				'Saias'              => array( 'saias' ),
				'Plus Size'          => array( 'plus-size' ),
				'Casacos e Jaquetas' => array( 'casacos-e-jaquetas', 'casacos-e-jaqueta', 'casacos-e-jaquetas-2' ),
				'Maiôs'              => array( 'maios' ),
				'Biquínis'           => array( 'biquinis' ),
				'Calças'             => array( 'calcas' ),
				'Shorts'             => array( 'shorts', 'shorts-e-macacao' ),
				'Macacão'            => array( 'macacao', 'shorts-e-macacao' ),
			),
		),
		array(
			'title' => __( 'Cuidados Femininos', 'klyvorenza' ),
			'note'  => __( 'Confira as categorias abaixo.', 'klyvorenza' ),
			'names' => array(
				'Beleza & Cuidados'   => array( 'beleza-cuidados', 'beleza-e-cuidados', 'beleza' ),
				'Cuidados com a Pele' => array( 'cuidados-com-a-pele' ),
				'Maquiagem'           => array( 'maquiagem' ),
				'Cabelos'             => array( 'cabelos', 'cabelo' ),
				'Unhas'               => array( 'unhas' ),
			),
		),
	);
}

/**
 * Cartão de uma categoria.
 *
 * @param WP_Term $t Categoria.
 * @return string
 */
function klyv_category_card( $t ) {
	$ids = klyv_category_ids( $t->term_id, 1 );
	$img = $ids && has_post_thumbnail( $ids[0] ) ? get_the_post_thumbnail(
		$ids[0],
		'klyv-card',
		array(
			'loading'  => 'lazy',
			'decoding' => 'async',
			'alt'      => '',
			'sizes'    => '(max-width:600px) 92vw, 300px',
		)
	) : '<span class="klyv-card__ph" aria-hidden="true"></span>';
	/* translators: %d: número de artigos. */
	$n = sprintf( _n( '%d artigo', '%d artigos', (int) $t->count, 'klyvorenza' ), (int) $t->count );
	return '<a class="klyv-cat" href="' . esc_url( get_term_link( $t ) ) . '"><span class="klyv-card__img">' . $img . '</span><span class="klyv-cat__n">' . esc_html( $t->name ) . '</span><span class="klyv-cat__c">' . esc_html( $n ) . '</span></a>';
}

/** Página de categorias: Moda Feminina, Cuidados Femininos e, se houver, as demais. */
function klyv_render_category_hub() {
	$all   = klyv_active_categories(); // Só categorias com artigos.
	$by_id = array();
	foreach ( $all as $t ) {
		$by_id[ $t->term_id ] = $t;
	}
	$shown = array();
	$html  = '';
	foreach ( klyv_category_groups() as $g ) {
		$cards = '';
		foreach ( $g['names'] as $name => $slugs ) {
			$found = null;
			foreach ( $all as $t ) {
				if ( in_array( $t->slug, $slugs, true ) || sanitize_title( $t->name ) === sanitize_title( $name ) ) {
					$found = $t;
					break;
				}
			}
			if ( $found && ! isset( $shown[ $found->term_id ] ) ) {
				$shown[ $found->term_id ] = true;
				$cards                   .= klyv_category_card( $found );
			}
		}
		if ( $cards ) {
			$html .= '<section class="klyv-cats-sec"><h2>' . esc_html( $g['title'] ) . '</h2><p class="klyv-cats-sec__n">' . esc_html( $g['note'] ) . '</p><div class="klyv-cats">' . $cards . '</div></section>';
		}
	}
	// Demais categorias (novas), fora de Especiais e Datas Festivas.
	$skip = array();
	foreach ( array( 'especiais', 'datas-festivas', 'datas-comemorativas' ) as $slug ) {
		$p = get_term_by( 'slug', $slug, 'category' );
		if ( $p ) {
			$skip[ $p->term_id ] = true;
		}
	}
	$extra = '';
	foreach ( $all as $t ) {
		if ( isset( $shown[ $t->term_id ] ) || isset( $skip[ $t->term_id ] ) || isset( $skip[ $t->parent ] ) ) {
			continue;
		}
		$extra .= klyv_category_card( $t );
	}
	if ( $extra ) {
		$html .= '<section class="klyv-cats-sec"><h2>' . esc_html__( 'Mais categorias', 'klyvorenza' ) . '</h2><div class="klyv-cats">' . $extra . '</div></section>';
	}
	return $html ? $html : '<p>' . esc_html__( 'Nenhuma categoria com artigos ainda.', 'klyvorenza' ) . '</p>';
}

/** Cartões de Web Stories (proporção 3:4). *
 *
 * @param array $atts Atributos do bloco.
 */
function klyv_render_stories( $atts = array() ) {
	$types = klyv_story_types();
	if ( ! $types ) {
		return '';
	}
	$n   = isset( $atts['count'] ) ? max( 1, (int) $atts['count'] ) : 8;
	$ids = klyv_cached(
		'stories_' . $n,
		function () use ( $types, $n ) {
			$q = new WP_Query(
				array(
					'post_type'      => $types,
					'posts_per_page' => $n,
					'fields'         => 'ids',
					'no_found_rows'  => true,
				)
			);
			return $q->posts;
		}
	);
	if ( ! $ids ) {
		return '';
	}
	$h = '<div class="klyv-stories">';
	foreach ( $ids as $id ) {
		$img = has_post_thumbnail( $id ) ? get_the_post_thumbnail(
			$id,
			'klyv-story',
			array(
				'loading'  => 'lazy',
				'decoding' => 'async',
				'alt'      => '',
				'sizes'    => '(max-width:600px) 44vw, 240px',
			)
		) : '<span class="klyv-card__ph" aria-hidden="true"></span>';
		$h  .= '<a class="klyv-story" href="' . esc_url( get_permalink( $id ) ) . '">' . $img . '<span class="klyv-story__t">' . esc_html( get_the_title( $id ) ) . '</span></a>';
	}
	return $h . '</div>';
}

/**
 * Termos do glossário (tipo de conteúdo próprio ou páginas filhas de /glossario/).
 *
 * @param string $order  title|date.
 * @param int    $limit  Máximo.
 * @return WP_Post[]
 */
function klyv_glossary_posts( $order = 'title', $limit = 500 ) {
	$pt = klyv_glossary_type();
	if ( $pt ) {
		return get_posts(
			array(
				'post_type'      => $pt,
				'posts_per_page' => $limit,
				'orderby'        => $order,
				'order'          => 'title' === $order ? 'ASC' : 'DESC',
				'no_found_rows'  => true,
			)
		);
	}
	$page = get_page_by_path( 'glossario' );
	if ( ! $page ) {
		return array();
	}
	return get_pages(
		array(
			'child_of'    => $page->ID,
			'sort_column' => 'title' === $order ? 'post_title' : 'post_date',
			'sort_order'  => 'title' === $order ? 'ASC' : 'DESC',
		)
	);
}

/**
 * Letra inicial de um termo (sem acento, A a Z, ou #).
 *
 * @param WP_Post $p Termo.
 * @return string
 */
function klyv_term_letter( $p ) {
	$l = strtoupper( substr( remove_accents( get_the_title( $p ) ), 0, 1 ) );
	return ctype_alpha( $l ) ? $l : '#';
}

/**
 * Um termo por letra (o mais recente de cada), em ordem A a Z. Sem repetir letras.
 *
 * @return WP_Post[] Indexado pela letra.
 */
function klyv_glossary_one_per_letter() {
	return klyv_cached(
		'gloss_letters',
		function () {
			$out = array();
			foreach ( klyv_glossary_posts( 'date', 500 ) as $p ) { // Do mais novo para o mais antigo.
				$l = klyv_term_letter( $p );
				if ( ! isset( $out[ $l ] ) ) {
					$out[ $l ] = $p;
				}
			}
			ksort( $out );
			return $out;
		}
	);
}

/** Glossário A a Z com marcação DefinedTermSet. */
function klyv_render_glossary() {
	$posts = klyv_glossary_posts( 'title', 500 );
	if ( ! $posts ) {
		return '';
	}
	$groups = array();
	foreach ( $posts as $p ) {
		$groups[ klyv_term_letter( $p ) ][] = $p;
	}
	ksort( $groups );
	$h = '<nav class="klyv-az" aria-label="' . esc_attr__( 'Índice de A a Z', 'klyvorenza' ) . '">';
	foreach ( array_keys( $groups ) as $l ) {
		$h .= '<a href="#letra-' . esc_attr( strtolower( $l ) ) . '">' . esc_html( $l ) . '</a>';
	}
	$h .= '</nav><div class="klyv-gloss">';
	foreach ( $groups as $l => $list ) {
		$h .= '<section id="letra-' . esc_attr( strtolower( $l ) ) . '"><h2>' . esc_html( $l ) . '</h2><dl>';
		foreach ( $list as $p ) {
			$h .= '<dt><a href="' . esc_url( get_permalink( $p ) ) . '">' . esc_html( get_the_title( $p ) ) . '</a></dt><dd>' . esc_html( wp_trim_words( get_the_excerpt( $p ), 30, '…' ) ) . '</dd>';
		}
		$h .= '</dl></section>';
	}
	return $h . '</div>';
}

/** Destaque do termo pesquisado e sugestões. */
function klyv_render_search_helper() {
	if ( ! is_search() ) {
		return '';
	}
	$q = get_search_query();
	$h = '<div class="klyv-search-head"><p class="klyv-search-head__t">' . sprintf( /* translators: %s: termo. */ esc_html__( 'Resultados para %s', 'klyvorenza' ), '<mark>' . esc_html( $q ) . '</mark>' ) . '</p>' . klyv_search_form( 'klyv-q-r' ) . '</div>';
	global $wp_query;
	if ( empty( $wp_query->found_posts ) ) {
		$h .= '<div class="klyv-empty"><p>' . esc_html__( 'Não encontramos nada com esse termo. Confira a grafia ou explore uma categoria:', 'klyvorenza' ) . '</p><ul class="klyv-chips" role="list">';
		foreach ( klyv_popular_cats() as $c ) {
			$h .= '<li><a href="' . esc_url( $c['url'] ) . '">' . esc_html( $c['name'] ) . '</a></li>';
		}
		$h .= '</ul></div>';
	}
	return $h;
}

/** Realça o termo pesquisado em títulos e resumos. */
add_filter(
	'the_title',
	function ( $title, $id = 0 ) {
		return klyv_highlight( $title );
	},
	20,
	2
);
add_filter( 'get_the_excerpt', 'klyv_highlight', 20 );
/**
 * Marca o termo buscado (apenas no loop de busca, fora do admin).
 *
 * @param string $text Texto.
 * @return string
 */
function klyv_highlight( $text ) {
	if ( is_admin() || ! is_search() || ! in_the_loop() || ! $text ) {
		return $text;
	}
	$q = trim( get_search_query( false ) );
	if ( strlen( $q ) < 2 ) {
		return $text;
	}
	return preg_replace( '/(' . preg_quote( esc_html( $q ), '/' ) . ')/iu', '<mark>$1</mark>', $text );
}

/** Barra lateral: anúncio (se ligado) mais atalho de categorias. *
 *
 * @param array $atts Atributos do bloco.
 */
function klyv_render_sidebar_ad( $atts ) {
	if ( ! klyv_opt( 'sidebars_on' ) ) {
		return '';
	}
	$side = isset( $atts['side'] ) && 'right' === $atts['side'] ? 'right' : 'left';
	return klyv_ad_slot( 'ads_side_' . $side, 'ads_on_' . $side, 'side' );
}

/** Filtro por categoria (links para as categorias com artigos). */
function klyv_render_cat_filter() {
	$cur = is_category() ? get_queried_object_id() : 0;
	$h   = '<nav class="klyv-filter" aria-label="' . esc_attr__( 'Filtrar por categoria', 'klyvorenza' ) . '"><ul class="klyv-chips" role="list">';
	foreach ( klyv_active_categories() as $t ) {
		$h .= '<li><a href="' . esc_url( get_term_link( $t ) ) . '"' . ( $cur === $t->term_id ? ' aria-current="page"' : '' ) . '>' . esc_html( $t->name ) . '</a></li>';
	}
	return $h . '</ul></nav>';
}
