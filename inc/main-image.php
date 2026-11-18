<?php
/**
 * Imagem principal do artigo (meta própria) com fallback para a imagem de destaque.
 *
 * @package Klyvorenza
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'init',
	function () {
		$auth = function () {
			return current_user_can( 'edit_posts' );
		};
		register_post_meta(
			'post',
			'_klyv_main_id',
			array(
				'single'            => true,
				'type'              => 'integer',
				'show_in_rest'      => true,
				'sanitize_callback' => 'absint',
				'auth_callback'     => $auth,
			)
		);
		register_post_meta(
			'post',
			'_klyv_main_alt',
			array(
				'single'            => true,
				'type'              => 'string',
				'show_in_rest'      => true,
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback'     => $auth,
			)
		);
		register_post_meta(
			'post',
			'_klyv_main_caption',
			array(
				'single'            => true,
				'type'              => 'string',
				'show_in_rest'      => true,
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback'     => $auth,
			)
		);
	}
);

/** Painel no editor (JS puro, sem build). */
add_action(
	'enqueue_block_editor_assets',
	function () {
		wp_enqueue_script(
			'klyvorenza-main-image',
			KLYV_URI . '/assets/js/editor-imagem-principal.js',
			array( 'wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data', 'wp-core-data', 'wp-block-editor', 'wp-i18n' ),
			KLYV_VERSION,
			true
		);
	}
);

/**
 * Dados da imagem do topo do artigo.
 *
 * @param int $post_id Post.
 * @return array|null { id, alt, caption }
 */
function klyv_main_image_data( $post_id ) {
	$id = (int) get_post_meta( $post_id, '_klyv_main_id', true );
	if ( $id && wp_attachment_is_image( $id ) ) {
		$alt = (string) get_post_meta( $post_id, '_klyv_main_alt', true );
		if ( '' === $alt ) {
			$alt = (string) get_post_meta( $id, '_wp_attachment_image_alt', true );
		}
		return array(
			'id'      => $id,
			'alt'     => $alt,
			'caption' => (string) get_post_meta( $post_id, '_klyv_main_caption', true ),
		);
	}
	$thumb = get_post_thumbnail_id( $post_id );
	if ( $thumb ) {
		return array(
			'id'      => (int) $thumb,
			'alt'     => (string) get_post_meta( $thumb, '_wp_attachment_image_alt', true ),
			'caption' => '',
		);
	}
	// Artigos antigos: primeira imagem do conteúdo.
	if ( preg_match( '/wp-image-(\d+)/', (string) get_post_field( 'post_content', $post_id ), $m ) && wp_attachment_is_image( (int) $m[1] ) ) {
		return array(
			'id'      => (int) $m[1],
			'alt'     => (string) get_post_meta( (int) $m[1], '_wp_attachment_image_alt', true ),
			'caption' => '',
		);
	}
	return null;
}

/** Bloco: imagem principal (LCP do artigo). */
function klyv_render_main_image() {
	if ( ! is_singular() ) {
		return '';
	}
	$d = klyv_main_image_data( get_the_ID() );
	if ( ! $d ) {
		return '';
	}
	$img = wp_get_attachment_image(
		$d['id'],
		'klyv-principal',
		false,
		array(
			'alt'           => $d['alt'],
			'loading'       => 'eager',
			'fetchpriority' => 'high',
			'decoding'      => 'async',
			'sizes'         => '(max-width:1240px) 100vw, 1240px',
		)
	);
	if ( ! $img ) {
		return '';
	}
	$cap = $d['caption'] ? '<figcaption>' . esc_html( $d['caption'] ) . '</figcaption>' : '';
	return '<figure class="klyv-main-img">' . $img . $cap . '</figure>';
}

/**
 * Evita repetir a mesma imagem: se a imagem do topo é a de destaque,
 * nada mais a repete na tela (o tema só a usa em cartões e no compartilhamento).
 */
