<?php
/**
 * SEO técnico: meta, Open Graph, canonical e JSON-LD.
 * Cede a vez quando há plugin de SEO ativo (Yoast, Rank Math, AIOSEO, SEOPress).
 *
 * @package Klyvorenza
 */

defined( 'ABSPATH' ) || exit;

add_filter(
	'wp_robots',
	function ( $r ) {
		$r['max-image-preview'] = 'large';
		$r['max-snippet']       = '-1';
		$r['max-video-preview'] = '-1';
		return $r;
	}
);

/** Descrição da página atual (140 a 155 caracteres quando possível). */
function klyv_meta_description() {
	$d = '';
	if ( is_singular() ) {
		$d = get_the_excerpt() ? get_the_excerpt() : wp_strip_all_tags( get_post_field( 'post_content', get_queried_object_id() ) );
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$d = term_description();
		if ( ! $d ) {
			/* translators: %s: nome da categoria. */
			$d = sprintf( __( 'Artigos sobre %s na Klyvorenza: dicas práticas, tendências e ideias para o seu dia a dia.', 'klyvorenza' ), single_term_title( '', false ) );
		}
	} elseif ( is_home() || is_front_page() ) {
		$d = get_bloginfo( 'description' );
	} elseif ( is_search() ) {
		/* translators: %s: termo pesquisado. */
		$d = sprintf( __( 'Resultados da busca por %s na Klyvorenza.', 'klyvorenza' ), get_search_query() );
	}
	$d = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( (string) $d ) ) );
	if ( mb_strlen( $d ) > 155 ) {
		$d = mb_substr( $d, 0, 154 );
		$d = preg_replace( '/\s+\S*$/u', '', $d ) . '…';
	}
	return $d;
}

/** URL canônica da página atual. */
function klyv_canonical() {
	if ( is_singular() ) {
		return get_permalink();
	}
	if ( is_category() || is_tag() || is_tax() ) {
		return get_term_link( get_queried_object() );
	}
	if ( is_home() && ! is_front_page() ) {
		return get_permalink( get_option( 'page_for_posts' ) );
	}
	if ( is_front_page() ) {
		return home_url( '/' );
	}
	if ( is_post_type_archive() ) {
		return get_post_type_archive_link( get_query_var( 'post_type' ) );
	}
	return '';
}

add_action(
	'wp_head',
	function () {
		if ( klyv_seo_plugin_active() || klyv_is_story() || is_404() ) {
			return;
		}
		$desc  = klyv_meta_description();
		$canon = klyv_canonical();
		$title = wp_get_document_title();
		$img   = '';
		$type  = 'website';
		if ( is_singular() ) {
			$img  = (string) get_the_post_thumbnail_url( get_queried_object_id(), 'klyv-social' );
			$type = is_singular( 'post' ) ? 'article' : 'website';
		}
		if ( ! $img ) {
			$img = KLYV_URI . '/assets/logo/simbolo.png';
		}
		if ( $desc ) {
			echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
		}
		if ( $canon && ! is_singular() && ! is_wp_error( $canon ) ) {
			echo '<link rel="canonical" href="' . esc_url( $canon ) . '">' . "\n";
		}
		$og = array(
			'og:locale'      => get_locale(),
			'og:type'        => $type,
			'og:title'       => $title,
			'og:description' => $desc,
			'og:url'         => $canon,
			'og:site_name'   => get_bloginfo( 'name' ),
			'og:image'       => $img,
		);
		foreach ( $og as $k => $v ) {
			if ( $v && ! is_wp_error( $v ) ) {
				echo '<meta property="' . esc_attr( $k ) . '" content="' . esc_attr( $v ) . '">' . "\n";
			}
		}
		if ( is_singular( 'post' ) ) {
			echo '<meta property="article:published_time" content="' . esc_attr( get_the_date( 'c' ) ) . '">' . "\n";
			echo '<meta property="article:modified_time" content="' . esc_attr( get_the_modified_date( 'c' ) ) . '">' . "\n";
		}
		echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
		echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '">' . "\n";
		if ( $desc ) {
			echo '<meta name="twitter:description" content="' . esc_attr( $desc ) . '">' . "\n";
		}
		echo '<meta name="twitter:image" content="' . esc_url( $img ) . '">' . "\n";
	},
	5
);

/**
 * Perguntas e respostas: título terminado em "?" seguido de parágrafo.
 *
 * @param string $html Conteúdo.
 * @return array
 */
function klyv_extract_faq( $html ) {
	$out = array();
	if ( preg_match_all( '/<h[2-4][^>]*>(.*?\?)\s*<\/h[2-4]>\s*<p[^>]*>(.*?)<\/p>/is', $html, $m, PREG_SET_ORDER ) ) {
		foreach ( $m as $x ) {
			$q = trim( wp_strip_all_tags( $x[1] ) );
			$a = trim( wp_strip_all_tags( $x[2] ) );
			if ( $q && $a ) {
				$out[] = array( '@type' => 'Question', 'name' => $q, 'acceptedAnswer' => array( '@type' => 'Answer', 'text' => $a ) );
			}
		}
	}
	return $out;
}

add_action(
	'wp_head',
	function () {
		if ( klyv_seo_plugin_active() || klyv_is_story() || is_404() ) {
			return;
		}
		$site  = home_url( '/' );
		$graph = array();

		$graph[] = array(
			'@type'           => 'WebSite',
			'@id'             => $site . '#website',
			'url'             => $site,
			'name'            => get_bloginfo( 'name' ),
			'description'     => get_bloginfo( 'description' ),
			'inLanguage'      => 'pt-BR',
			'publisher'       => array( '@id' => $site . '#organization' ),
			'potentialAction' => array(
				'@type'       => 'SearchAction',
				'target'      => array( '@type' => 'EntryPoint', 'urlTemplate' => $site . '?s={search_term_string}' ),
				'query-input' => 'required name=search_term_string',
			),
		);
		$same = array_values( array_filter( wp_list_pluck( klyv_socials(), 'url' ) ) );
		$graph[] = array(
			'@type' => 'Organization',
			'@id'   => $site . '#organization',
			'name'  => get_bloginfo( 'name' ),
			'url'   => $site,
			'logo'  => array( '@type' => 'ImageObject', 'url' => KLYV_URI . '/assets/logo/logo-site.png', 'width' => 450, 'height' => 200 ),
			'sameAs' => $same,
		);

		if ( ! is_front_page() ) {
			$crumbs = array();
			foreach ( klyv_breadcrumb_items() as $i => $it ) {
				$c = array( '@type' => 'ListItem', 'position' => $i + 1, 'name' => wp_strip_all_tags( $it[0] ) );
				if ( $it[1] ) {
					$c['item'] = $it[1];
				}
				$crumbs[] = $c;
			}
			$graph[] = array( '@type' => 'BreadcrumbList', 'itemListElement' => $crumbs );
		}

		if ( is_singular( 'post' ) ) {
			$id   = get_queried_object_id();
			$main = klyv_main_image_data( $id );
			$iurl = $main ? wp_get_attachment_image_url( $main['id'], 'klyv-principal' ) : '';
			$post = array(
				'@type'            => 'BlogPosting',
				'@id'              => get_permalink() . '#article',
				'mainEntityOfPage' => get_permalink(),
				'headline'         => mb_substr( wp_strip_all_tags( get_the_title() ), 0, 110 ),
				'description'      => klyv_meta_description(),
				'datePublished'    => get_the_date( 'c' ),
				'dateModified'     => get_the_modified_date( 'c' ),
				'inLanguage'       => 'pt-BR',
				'author'           => array( '@type' => 'Person', 'name' => get_the_author(), 'url' => get_author_posts_url( (int) get_post_field( 'post_author', $id ) ) ),
				'publisher'        => array( '@id' => $site . '#organization' ),
				'isPartOf'         => array( '@id' => $site . '#website' ),
			);
			if ( $iurl ) {
				$meta = wp_get_attachment_metadata( $main['id'] );
				$post['image'] = array( '@type' => 'ImageObject', 'url' => $iurl, 'width' => 1200, 'height' => 675 );
				unset( $meta );
			}
			$cats = get_the_category();
			if ( $cats ) {
				$post['articleSection'] = $cats[0]->name;
			}
			$graph[] = $post;
			$faq     = klyv_extract_faq( apply_filters( 'the_content', get_post_field( 'post_content', $id ) ) );
			if ( count( $faq ) >= 2 ) {
				$graph[] = array( '@type' => 'FAQPage', 'mainEntity' => $faq );
			}
		} elseif ( is_category() ) {
			$t       = get_queried_object();
			$graph[] = array( '@type' => 'CollectionPage', 'name' => $t->name, 'url' => get_term_link( $t ), 'description' => klyv_meta_description(), 'inLanguage' => 'pt-BR', 'isPartOf' => array( '@id' => $site . '#website' ) );
		} elseif ( is_page( 'glossario' ) || ( klyv_glossary_type() && is_post_type_archive( klyv_glossary_type() ) ) ) {
			$terms = array();
			$pt    = klyv_glossary_type();
			$posts = $pt ? get_posts( array( 'post_type' => $pt, 'posts_per_page' => 200, 'orderby' => 'title', 'order' => 'ASC', 'no_found_rows' => true ) ) : array();
			foreach ( $posts as $p ) {
				$terms[] = array( '@type' => 'DefinedTerm', 'name' => get_the_title( $p ), 'description' => wp_strip_all_tags( get_the_excerpt( $p ) ), 'url' => get_permalink( $p ), 'inDefinedTermSet' => $site . 'glossario/' );
			}
			$graph[] = array( '@type' => 'DefinedTermSet', '@id' => $site . 'glossario/#set', 'name' => __( 'Glossário', 'klyvorenza' ), 'url' => $site . 'glossario/', 'hasDefinedTerm' => $terms );
		} elseif ( klyv_glossary_type() && is_singular( klyv_glossary_type() ) ) {
			$graph[] = array( '@type' => 'DefinedTerm', 'name' => get_the_title(), 'description' => klyv_meta_description(), 'url' => get_permalink(), 'inDefinedTermSet' => $site . 'glossario/' );
		}

		echo '<script type="application/ld+json">' . wp_json_encode( array( '@context' => 'https://schema.org', '@graph' => $graph ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput
	},
	6
);
