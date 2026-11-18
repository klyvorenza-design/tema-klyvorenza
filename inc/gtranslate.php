<?php
/**
 * Compatibilidade com o GTranslate: barra fina no topo e carregamento tardio.
 * Nenhum arquivo do plugin é alterado e as marcações dele (hreflang etc.) permanecem.
 *
 * @package Klyvorenza
 */

defined( 'ABSPATH' ) || exit;

/** HTML das bandeiras (shortcode oficial), ou vazio. */
function klyv_gtranslate_html() {
	if ( ! shortcode_exists( 'gtranslate' ) ) {
		return '';
	}
	return '<div class="klyv-gt" data-klyv-gt>' . do_shortcode( '[gtranslate]' ) . '</div>';
}

/** Adia os scripts externos do GTranslate até a primeira interação ou load. */
add_filter(
	'script_loader_tag',
	function ( $tag, $handle, $src ) {
		if ( is_admin() || false === stripos( $handle . $src, 'gtranslate' ) ) {
			return $tag;
		}
		$tag = preg_replace( '/\ssrc=/', ' data-src=', $tag, 1 );
		return preg_replace( '/<script\b/', '<script type="text/plain" data-klyv-delay', $tag, 1 );
	},
	10,
	3
);
