<?php
/**
 * Sumário automático (H2/H3) e IDs nos títulos do artigo.
 *
 * @package Klyvorenza
 */

defined( 'ABSPATH' ) || exit;

add_filter(
	'the_content',
	function ( $content ) {
		if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}
		$items = array();
		$used  = array();
		$out   = preg_replace_callback(
			'/<h([23])([^>]*)>(.*?)<\/h\1>/is',
			function ( $m ) use ( &$items, &$used ) {
				$text = trim( wp_strip_all_tags( $m[3] ) );
				if ( '' === $text ) {
					return $m[0];
				}
				if ( preg_match( '/\sid=["\']([^"\']+)["\']/', $m[2], $idm ) ) {
					$id = $idm[1];
				} else {
					$id = sanitize_title( $text );
					$id = $id ? $id : 'secao';
					$n  = 2;
					$b  = $id;
					while ( isset( $used[ $id ] ) ) {
						$id = $b . '-' . $n++;
					}
					$m[2] .= ' id="' . esc_attr( $id ) . '"';
				}
				$used[ $id ] = true;
				$items[]     = array( (int) $m[1], $id, $text );
				return '<h' . $m[1] . $m[2] . '>' . $m[3] . '</h' . $m[1] . '>';
			},
			$content
		);
		if ( count( $items ) < 3 ) {
			return $out;
		}
		$toc = '<nav class="klyv-toc" aria-labelledby="klyv-toc-t"><p class="klyv-toc__t" id="klyv-toc-t">' . esc_html__( 'Neste artigo', 'klyvorenza' ) . '</p><ol>';
		foreach ( $items as $it ) {
			$toc .= '<li class="l' . (int) $it[0] . '"><a href="#' . esc_attr( $it[1] ) . '">' . esc_html( $it[2] ) . '</a></li>';
		}
		$toc .= '</ol></nav>';
		// Insere antes do primeiro título.
		$pos = strpos( $out, '<h' );
		return false === $pos ? $out : substr( $out, 0, $pos ) . $toc . substr( $out, $pos );
	},
	8
);
