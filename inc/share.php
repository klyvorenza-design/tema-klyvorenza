<?php
/**
 * Botões de compartilhamento (links simples, sem SDK externo).
 *
 * @package Klyvorenza
 */

defined( 'ABSPATH' ) || exit;

/** Bloco de compartilhamento. *
 *
 * @param array $atts Atributos do bloco.
 */
function klyv_render_share( $atts = array() ) {
	if ( ! is_singular() ) {
		return '';
	}
	$on = (array) klyv_opt( 'share_buttons', array() );
	if ( ! $on ) {
		return '';
	}
	$url   = rawurlencode( get_permalink() );
	$title = rawurlencode( wp_strip_all_tags( get_the_title() ) );
	$img   = rawurlencode( (string) get_the_post_thumbnail_url( get_the_ID(), 'klyv-social' ) );
	$defs  = array(
		'whatsapp'  => array( 'WhatsApp', 'https://api.whatsapp.com/send?text=' . $title . '%20' . $url ),
		'facebook'  => array( 'Facebook', 'https://www.facebook.com/sharer/sharer.php?u=' . $url ),
		'pinterest' => array( 'Pinterest', 'https://pinterest.com/pin/create/button/?url=' . $url . '&media=' . $img . '&description=' . $title ),
		'x'         => array( 'X', 'https://twitter.com/intent/tweet?url=' . $url . '&text=' . $title ),
		'telegram'  => array( 'Telegram', 'https://t.me/share/url?url=' . $url . '&text=' . $title ),
		'email'     => array( __( 'E-mail', 'klyvorenza' ), 'mailto:?subject=' . $title . '&body=' . $url ),
		'linkedin'  => array( 'LinkedIn', 'https://www.linkedin.com/sharing/share-offsite/?url=' . $url ),
	);
	$h     = '<div class="klyv-share"><span class="klyv-share__l">' . esc_html__( 'Compartilhar', 'klyvorenza' ) . '</span><ul role="list">';
	$tt    = klyv_opt( 'social_tiktok' );
	foreach ( array_unique( $on ) as $k ) {
		if ( 'tiktok' === $k ) {
			// O TikTok não tem link de compartilhamento por URL: copia o link do artigo e abre o TikTok.
			$h .= '<li><button type="button" data-copy="' . esc_attr( get_permalink() ) . '" data-open="' . esc_url( $tt ? $tt : 'https://www.tiktok.com/' ) . '" aria-label="' . esc_attr__( 'Compartilhar no TikTok (copia o link do artigo e abre o TikTok)', 'klyvorenza' ) . '">' . klyv_icon( 'tiktok' ) . '</button></li>';
		} elseif ( 'copiar' === $k ) {
			$h .= '<li><button type="button" data-copy="' . esc_attr( get_permalink() ) . '" aria-label="' . esc_attr__( 'Copiar link do artigo', 'klyvorenza' ) . '">' . klyv_icon( 'link' ) . '</button></li>';
		} elseif ( isset( $defs[ $k ] ) ) {
			/* translators: %s: rede social. */
			$label = sprintf( __( 'Compartilhar no %s', 'klyvorenza' ), $defs[ $k ][0] );
			$h    .= '<li><a href="' . esc_url( $defs[ $k ][1] ) . '" target="_blank" rel="noopener noreferrer" aria-label="' . esc_attr( $label ) . '">' . klyv_icon( $k ) . '</a></li>';
		}
	}
	return $h . '</ul></div>';
}
