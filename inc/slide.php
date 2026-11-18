<?php
/**
 * Slide de destaques (artigos mais recentes) da página inicial.
 *
 * @package Klyvorenza
 */

defined( 'ABSPATH' ) || exit;

/** Renderiza o slide. */
function klyv_render_slide() {
	if ( ! klyv_opt( 'slide_on' ) ) {
		return '';
	}
	$n   = max( 1, min( 8, (int) klyv_opt( 'slide_count', 4 ) ) );
	$ids = klyv_cached(
		'slide_' . $n,
		function () use ( $n ) {
			$q = new WP_Query(
				array(
					'post_type'           => 'post',
					'posts_per_page'      => $n,
					'fields'              => 'ids',
					'no_found_rows'       => true,
					'ignore_sticky_posts' => true,
				)
			);
			return $q->posts;
		}
	);
	if ( ! $ids ) {
		return '';
	}
	klyv_shown( $ids );
	$auto = klyv_opt( 'slide_autoplay' ) ? '1' : '0';
	$h    = '<section class="klyv-slide" role="region" aria-roledescription="carousel" aria-label="' . esc_attr__( 'Artigos em destaque', 'klyvorenza' ) . '" data-auto="' . esc_attr( $auto ) . '" data-interval="' . esc_attr( max( 3, (int) klyv_opt( 'slide_interval', 6 ) ) * 1000 ) . '">';
	$h   .= '<div class="klyv-slide__track" tabindex="0" aria-live="off">';
	foreach ( $ids as $i => $id ) {
		$cats = get_the_category( $id );
		$img  = has_post_thumbnail( $id )
			? get_the_post_thumbnail(
				$id,
				'klyv-slide',
				array(
					'alt'           => '',
					'sizes'         => '(max-width:1280px) 100vw, 1240px',
					'decoding'      => 0 === $i ? 'sync' : 'async',
					'loading'       => 0 === $i ? 'eager' : 'lazy',
					'fetchpriority' => 0 === $i ? 'high' : 'auto',
				)
			)
			: '<span class="klyv-card__ph" aria-hidden="true"></span>';
		/* translators: 1: posição, 2: total. */
		$lbl = sprintf( __( '%1$d de %2$d', 'klyvorenza' ), $i + 1, count( $ids ) );
		$h  .= '<article class="klyv-slide__item" role="group" aria-roledescription="slide" aria-label="' . esc_attr( $lbl ) . '">';
		$h  .= '<a class="klyv-slide__img" href="' . esc_url( get_permalink( $id ) ) . '" tabindex="-1" aria-hidden="true">' . $img . '</a>';
		$h  .= '<div class="klyv-slide__txt">';
		if ( $cats ) {
			$h .= '<a class="klyv-chip" href="' . esc_url( get_category_link( $cats[0] ) ) . '">' . esc_html( $cats[0]->name ) . '</a>';
		}
		$h .= '<h2 class="klyv-slide__t"><a href="' . esc_url( get_permalink( $id ) ) . '">' . esc_html( get_the_title( $id ) ) . '</a></h2>';
		$h .= '<p class="klyv-slide__x">' . esc_html( wp_trim_words( get_the_excerpt( $id ), 20, '…' ) ) . '</p>';
		$h .= '<a class="klyv-btn" href="' . esc_url( get_permalink( $id ) ) . '">' . esc_html__( 'Ler artigo', 'klyvorenza' ) . '</a>';
		$h .= '</div></article>';
	}
	$h .= '</div>';
	if ( count( $ids ) > 1 ) {
		$h .= '<button type="button" class="klyv-slide__nav klyv-slide__prev" aria-label="' . esc_attr__( 'Slide anterior', 'klyvorenza' ) . '">' . klyv_icon( 'left' ) . '</button>';
		$h .= '<button type="button" class="klyv-slide__nav klyv-slide__next" aria-label="' . esc_attr__( 'Próximo slide', 'klyvorenza' ) . '">' . klyv_icon( 'right' ) . '</button>';
		$h .= '<div class="klyv-slide__dots">';
		foreach ( $ids as $i => $id ) {
			/* translators: %d: número do slide. */
			$h .= '<button type="button" aria-label="' . esc_attr( sprintf( __( 'Ir para o slide %d', 'klyvorenza' ), $i + 1 ) ) . '"' . ( 0 === $i ? ' aria-current="true"' : '' ) . '></button>';
		}
		$h .= '</div>';
	}
	return $h . '</section>';
}
