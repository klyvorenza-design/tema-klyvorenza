<?php
/**
 * Página inicial: seções automáticas por categoria, sem repetir artigos.
 *
 * @package Klyvorenza
 */

defined( 'ABSPATH' ) || exit;

/**
 * Categorias com ao menos um artigo, mais recente primeiro (ou ordem do painel).
 * Nenhuma lista fixa: categorias novas entram sozinhas.
 *
 * @param bool $for_home Aplica ocultas e ordem do painel.
 * @return WP_Term[]
 */
function klyv_active_categories( $for_home = false ) {
	$list = klyv_cached(
		'active_cats',
		function () {
			$terms = get_terms( array( 'taxonomy' => 'category', 'hide_empty' => true ) );
			if ( is_wp_error( $terms ) ) {
				return array();
			}
			$latest = array();
			foreach ( $terms as $t ) {
				$q = get_posts( array( 'category' => $t->term_id, 'numberposts' => 1, 'fields' => 'ids', 'no_found_rows' => true, 'suppress_filters' => true ) );
				$latest[ $t->term_id ] = $q ? (int) strtotime( get_post_field( 'post_date_gmt', $q[0] ) ) : 0;
			}
			usort(
				$terms,
				function ( $a, $b ) use ( $latest ) {
					return $latest[ $b->term_id ] <=> $latest[ $a->term_id ];
				}
			);
			return array_values(
				array_filter(
					$terms,
					function ( $t ) {
						return ! in_array( $t->slug, array( 'uncategorized', 'sem-categoria' ), true );
					}
				)
			);
		}
	);
	if ( ! $for_home ) {
		return $list;
	}
	$hidden = array_filter( array_map( 'trim', explode( ',', (string) klyv_opt( 'hidden_cats', '' ) ) ) );
	$order  = array_filter( array_map( 'trim', explode( ',', (string) klyv_opt( 'cat_order', '' ) ) ) );
	$list   = array_values( array_filter( $list, function ( $t ) use ( $hidden ) {
		return ! in_array( $t->slug, $hidden, true );
	} ) );
	if ( $order ) {
		usort(
			$list,
			function ( $a, $b ) use ( $order ) {
				$pa = array_search( $a->slug, $order, true );
				$pb = array_search( $b->slug, $order, true );
				$pa = false === $pa ? 9999 : $pa;
				$pb = false === $pb ? 9999 : $pb;
				return $pa <=> $pb;
			}
		);
	}
	return $list;
}

/**
 * IDs candidatos de uma categoria (em cache). A deduplicação é feita depois, por requisição.
 *
 * @param int $term_id Categoria.
 * @param int $n       Quantidade.
 * @return int[]
 */
function klyv_category_ids( $term_id, $n = 12 ) {
	return klyv_cached(
		'cat_ids_' . $term_id . '_' . $n,
		function () use ( $term_id, $n ) {
			$q = new WP_Query(
				array(
					'post_type'           => 'post',
					'cat'                 => $term_id,
					'posts_per_page'      => $n,
					'fields'              => 'ids',
					'no_found_rows'       => true,
					'ignore_sticky_posts' => true,
				)
			);
			return $q->posts;
		}
	);
}

/**
 * Pega até $n IDs não exibidos ainda e os registra.
 *
 * @param int[] $candidates IDs candidatos.
 * @param int   $n          Máximo.
 * @return int[]
 */
function klyv_take_unshown( $candidates, $n ) {
	$free = array_values( array_diff( array_map( 'intval', $candidates ), klyv_shown() ) );
	$free = array_slice( $free, 0, $n );
	klyv_shown( $free );
	return $free;
}

/**
 * Frases curtas de SEO para cada seção da página inicial.
 * Categorias usam a descrição do painel quando existe; senão, uma frase curada
 * (por slug) ou uma frase genérica com o nome da categoria.
 *
 * @param string       $key  Chave fixa (ultimos, glossario, stories) ou slug da categoria.
 * @param WP_Term|null $term Categoria, quando houver.
 * @return string Parágrafo HTML (vazio se não houver texto).
 */
function klyv_section_intro( $key, $term = null ) {
	$curated = array(
		'ultimos'    => __( 'Confira os artigos mais recentes sobre moda feminina, beleza e estilo de vida, com dicas práticas para o seu dia a dia.', 'klyvorenza' ),
		'glossario'  => __( 'Entenda os termos de moda e beleza em explicações curtas, claras e fáceis de consultar.', 'klyvorenza' ),
		'stories'    => __( 'Veja inspirações de looks, beleza e tendências em Web Stories rápidas, feitas para o celular.', 'klyvorenza' ),
		'especiais'  => __( 'Descubra as últimas tendências de moda feminina e renove seu guarda-roupa com peças exclusivas.', 'klyvorenza' ),
		'moda'       => __( 'Aprenda a montar looks versáteis, escolher peças certeiras e acompanhar as tendências de moda feminina.', 'klyvorenza' ),
		'beleza'     => __( 'Dicas de maquiagem, cabelo e cuidados com a pele para realçar a sua beleza natural.', 'klyvorenza' ),
		'cabelo'     => __( 'Cortes, penteados e cuidados com os fios para manter o cabelo bonito e saudável.', 'klyvorenza' ),
		'unhas'      => __( 'Ideias de nail art, cores em alta e cuidados para unhas bonitas e fortes.', 'klyvorenza' ),
		'maquiagem'  => __( 'Passo a passo, produtos e truques de maquiagem para todas as ocasiões.', 'klyvorenza' ),
		'acessorios' => __( 'Bolsas, joias e acessórios para dar personalidade a qualquer look.', 'klyvorenza' ),
		'natal'      => __( 'Ideias de looks, decoração e presentes para viver um Natal cheio de estilo.', 'klyvorenza' ),
		'ano-novo'   => __( 'Looks, cores e inspirações para receber o Ano Novo com elegância.', 'klyvorenza' ),
		'datas-festivas'      => __( 'Inspirações de looks e ideias para celebrar cada data comemorativa com estilo.', 'klyvorenza' ),
		'datas-comemorativas' => __( 'Inspirações de looks e ideias para celebrar cada data comemorativa com estilo.', 'klyvorenza' ),
	);
	$text = '';
	if ( $term instanceof WP_Term ) {
		$text = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( (string) $term->description ) ) );
		if ( mb_strlen( $text ) > 170 ) {
			$text = preg_replace( '/\s+\S*$/u', '', mb_substr( $text, 0, 169 ) ) . '…';
		}
		if ( ! $text && isset( $curated[ $term->slug ] ) ) {
			$text = $curated[ $term->slug ];
		}
		if ( ! $text ) {
			/* translators: %s: nome da categoria. */
			$text = sprintf( __( 'Descubra dicas, tendências e ideias de %s para renovar o seu estilo no dia a dia.', 'klyvorenza' ), $term->name );
		}
	} elseif ( isset( $curated[ $key ] ) ) {
		$text = $curated[ $key ];
	}
	$text = apply_filters( 'klyvorenza_section_intro', $text, $key, $term );
	return $text ? '<p class="klyv-sec__intro">' . esc_html( $text ) . '</p>' : '';
}

/** Últimos artigos. */
function klyv_render_latest() {
	$ids = klyv_cached(
		'latest_ids',
		function () {
			$q = new WP_Query( array( 'post_type' => 'post', 'posts_per_page' => 24, 'fields' => 'ids', 'no_found_rows' => true, 'ignore_sticky_posts' => true ) );
			return $q->posts;
		}
	);
	$ids = klyv_take_unshown( $ids, 6 );
	if ( ! $ids ) {
		return '';
	}
	$h = '<section class="klyv-sec" aria-labelledby="klyv-s-ult"><header class="klyv-sec__h"><h2 id="klyv-s-ult">' . esc_html__( 'Últimos artigos', 'klyvorenza' ) . '</h2></header>' . klyv_section_intro( 'ultimos' ) . '<div class="klyv-grid">';
	foreach ( $ids as $id ) {
		$h .= klyv_card( $id );
	}
	return $h . '</div></section>';
}

/** Seções por categoria, depois Glossário e Web Stories. */
function klyv_render_home_sections() {
	$min  = (int) apply_filters( 'klyvorenza_home_min_posts', 2 );
	$html = '';
	foreach ( klyv_active_categories( true ) as $t ) {
		$ids = klyv_take_if_enough( klyv_category_ids( $t->term_id, 16 ), 3, $min );
		if ( ! $ids ) {
			continue;
		}
		$sid   = 'klyv-s-' . $t->term_id;
		$html .= '<section class="klyv-sec" aria-labelledby="' . esc_attr( $sid ) . '"><header class="klyv-sec__h"><h2 id="' . esc_attr( $sid ) . '">' . esc_html( $t->name ) . '</h2><a class="klyv-more" href="' . esc_url( get_term_link( $t ) ) . '">' . esc_html__( 'Ver tudo', 'klyvorenza' ) . '</a></header>' . klyv_section_intro( $t->slug, $t ) . '<div class="klyv-grid">';
		foreach ( $ids as $id ) {
			$html .= klyv_card( $id );
		}
		$html .= '</div></section>';
	}

	// Glossário.
	$gt = klyv_glossary_type();
	$gp = get_page_by_path( 'glossario' );
	if ( $gt ) {
		$gids = klyv_cached(
			'gloss_ids',
			function () use ( $gt ) {
				$q = new WP_Query( array( 'post_type' => $gt, 'posts_per_page' => 6, 'fields' => 'ids', 'no_found_rows' => true ) );
				return $q->posts;
			}
		);
		if ( $gids ) {
			$html .= '<section class="klyv-sec" aria-labelledby="klyv-s-gl"><header class="klyv-sec__h"><h2 id="klyv-s-gl">' . esc_html__( 'Glossário', 'klyvorenza' ) . '</h2>' . ( $gp ? '<a class="klyv-more" href="' . esc_url( get_permalink( $gp ) ) . '">' . esc_html__( 'Índice A a Z', 'klyvorenza' ) . '</a>' : '' ) . '</header>' . klyv_section_intro( 'glossario' ) . '<ul class="klyv-terms" role="list">';
			foreach ( $gids as $gid ) {
				$html .= '<li><a href="' . esc_url( get_permalink( $gid ) ) . '"><strong>' . esc_html( get_the_title( $gid ) ) . '</strong><span>' . esc_html( wp_trim_words( get_the_excerpt( $gid ), 14, '…' ) ) . '</span></a></li>';
			}
			$html .= '</ul></section>';
		}
	} elseif ( $gp ) {
		$html .= '<section class="klyv-sec" aria-labelledby="klyv-s-gl"><header class="klyv-sec__h"><h2 id="klyv-s-gl">' . esc_html__( 'Glossário', 'klyvorenza' ) . '</h2><a class="klyv-more" href="' . esc_url( get_permalink( $gp ) ) . '">' . esc_html__( 'Abrir glossário', 'klyvorenza' ) . '</a></header>' . klyv_section_intro( 'glossario' ) . '</section>';
	}

	// Web Stories.
	$stories = klyv_render_stories( array( 'count' => 8 ) );
	if ( $stories ) {
		$types = klyv_story_types();
		$link  = get_post_type_archive_link( $types[0] );
		$html .= '<section class="klyv-sec" aria-labelledby="klyv-s-st"><header class="klyv-sec__h"><h2 id="klyv-s-st">' . esc_html__( 'Web Stories', 'klyvorenza' ) . '</h2>' . ( $link ? '<a class="klyv-more" href="' . esc_url( $link ) . '">' . esc_html__( 'Ver todos', 'klyvorenza' ) . '</a>' : '' ) . '</header>' . klyv_section_intro( 'stories' ) . $stories . '</section>';
	}
	return $html;
}

/**
 * Pega IDs só se houver o mínimo; senão a seção some (não registra nada).
 *
 * @param int[] $candidates Candidatos.
 * @param int   $n          Máximo.
 * @param int   $min        Mínimo.
 * @return int[]
 */
function klyv_take_if_enough( $candidates, $n, $min ) {
	$free = array_values( array_diff( array_map( 'intval', $candidates ), klyv_shown() ) );
	if ( count( $free ) < $min ) {
		return array();
	}
	return klyv_take_unshown( $free, $n );
}
