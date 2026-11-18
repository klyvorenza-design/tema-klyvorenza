<?php
/**
 * Funções auxiliares, cache e controle de IDs exibidos.
 *
 * @package Klyvorenza
 */

defined( 'ABSPATH' ) || exit;

/**
 * Valores padrão das opções do tema.
 *
 * @return array
 */
function klyv_defaults() {
	return array(
		'adsense_pub'        => '',
		'ads_in_1'           => '',
		'ads_in_2'           => '',
		'ads_in_3'           => '',
		'ads_side_left'      => '',
		'ads_side_right'     => '',
		'ads_on_in_1'        => 1,
		'ads_on_in_2'        => 1,
		'ads_on_in_3'        => 1,
		'ads_on_left'        => 1,
		'ads_on_right'       => 1,
		'social_facebook'    => '',
		'social_instagram'   => '',
		'social_pinterest'   => '',
		'social_extra'       => '',
		'news_on'            => 1,
		'news_delay'         => 20,
		'news_title'         => __( 'Receba as novidades da Klyvorenza', 'klyvorenza' ),
		'news_text'          => __( 'Dicas de moda, beleza e cuidados direto no seu e-mail. Sem spam, e você sai quando quiser.', 'klyvorenza' ),
		'news_double_optin'  => 1,
		'share_buttons'      => array( 'whatsapp', 'facebook', 'pinterest', 'x', 'telegram', 'email', 'copiar' ),
		'sidebars_on'        => 1,
		'slide_on'           => 1,
		'slide_count'        => 4,
		'slide_interval'     => 6,
		'slide_autoplay'     => 1,
		'parent_especiais'   => 'especiais',
		'parent_datas'       => 'datas-festivas',
		'hidden_cats'        => '',
		'cat_order'          => '',
		'glossary_post_type' => '',
		'popular_cats'       => '',
	);
}

/**
 * Lê uma opção do tema.
 *
 * @param string $key     Chave.
 * @param mixed  $default Valor se não existir.
 * @return mixed
 */
function klyv_opt( $key, $default = null ) {
	static $opts = null;
	if ( null === $opts ) {
		$opts = wp_parse_args( (array) get_option( 'klyvorenza_options', array() ), klyv_defaults() );
	}
	if ( isset( $opts[ $key ] ) ) {
		return $opts[ $key ];
	}
	return $default;
}

/**
 * Pós-tipo do Fast Web Stories (detecta pelo slug fws-story; nunca presume).
 *
 * @return string[]
 */
function klyv_story_types() {
	static $types = null;
	if ( null !== $types ) {
		return $types;
	}
	$types = array();
	foreach ( get_post_types( array( 'public' => true ), 'objects' ) as $pt ) {
		$slug = is_array( $pt->rewrite ) && isset( $pt->rewrite['slug'] ) ? $pt->rewrite['slug'] : '';
		if ( 'fws-story' === $slug || 'fws_story' === $pt->name || false !== strpos( $pt->name, 'web_story' ) || false !== strpos( $pt->name, 'web-story' ) ) {
			$types[] = $pt->name;
		}
	}
	$types = (array) apply_filters( 'klyvorenza_story_post_types', $types );
	return $types;
}

/**
 * Estamos em uma página de story (AMP). O tema não interfere nela.
 *
 * @return bool
 */
function klyv_is_story() {
	$types = klyv_story_types();
	return $types && is_singular( $types );
}

/**
 * Pós-tipo do glossário, se existir (opção ou detecção).
 *
 * @return string
 */
function klyv_glossary_type() {
	$pt = klyv_opt( 'glossary_post_type', '' );
	if ( $pt && post_type_exists( $pt ) ) {
		return $pt;
	}
	foreach ( array( 'glossario', 'glossary', 'dlm_glossary', 'cmtt_glossary' ) as $try ) {
		if ( post_type_exists( $try ) ) {
			return $try;
		}
	}
	return '';
}

/**
 * Plugin de SEO ativo?
 *
 * @return bool
 */
function klyv_seo_plugin_active() {
	$active = defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'AIOSEO_VERSION' ) || defined( 'SEOPRESS_VERSION' ) || defined( 'THE_SEO_FRAMEWORK_VERSION' );
	return (bool) apply_filters( 'klyvorenza_seo_plugin_active', $active );
}

/**
 * IDs de posts já exibidos na página (controle global, no servidor).
 *
 * @param int[]|null $add IDs a registrar.
 * @return int[]
 */
function klyv_shown( $add = null ) {
	static $shown = array();
	if ( is_array( $add ) ) {
		$shown = array_values( array_unique( array_merge( $shown, array_map( 'intval', $add ) ) ) );
	}
	return $shown;
}

/**
 * Versão do cache. Incrementada quando conteúdo ou categorias mudam.
 *
 * @return int
 */
function klyv_cache_ver() {
	return (int) get_option( 'klyv_cache_ver', 1 );
}

/**
 * Invalida o cache (posts, categorias).
 */
function klyv_bump_cache() {
	update_option( 'klyv_cache_ver', klyv_cache_ver() + 1, false );
}
add_action( 'save_post', 'klyv_bump_cache' );
add_action( 'deleted_post', 'klyv_bump_cache' );
add_action( 'trashed_post', 'klyv_bump_cache' );
add_action( 'transition_post_status', 'klyv_bump_cache' );
add_action( 'created_term', 'klyv_bump_cache' );
add_action( 'edited_term', 'klyv_bump_cache' );
add_action( 'delete_term', 'klyv_bump_cache' );
add_action( 'update_option_klyvorenza_options', 'klyv_bump_cache' );

/**
 * Cache simples por transient com versão.
 *
 * @param string   $key      Chave.
 * @param callable $callback Gera o valor.
 * @param int      $ttl      Segundos.
 * @return mixed
 */
function klyv_cached( $key, $callback, $ttl = 6 * HOUR_IN_SECONDS ) {
	$k   = 'klyv_' . md5( $key . '|' . klyv_cache_ver() );
	$val = get_transient( $k );
	if ( false === $val ) {
		$val = call_user_func( $callback );
		set_transient( $k, $val, $ttl );
	}
	return $val;
}

/**
 * Ícones SVG inline (leves).
 *
 * @param string $name Nome.
 * @return string
 */
function klyv_icon( $name ) {
	$p = array(
		'search'    => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
		'menu'      => '<path d="M4 7h16M4 12h16M4 17h16"/>',
		'close'     => '<path d="M6 6l12 12M18 6 6 18"/>',
		'sun'       => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M4.9 4.9l1.4 1.4m11.4 11.4 1.4 1.4M2 12h2m16 0h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>',
		'moon'      => '<path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/>',
		'chevron'   => '<path d="m6 9 6 6 6-6"/>',
		'left'      => '<path d="m15 6-6 6 6 6"/>',
		'right'     => '<path d="m9 6 6 6-6 6"/>',
		'link'      => '<path d="M10 14a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1 1M14 10a4 4 0 0 0-5.7 0l-3 3a4 4 0 0 0 5.7 5.7l1-1"/>',
		'mail'      => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
		'whatsapp'  => '<path d="M3 21l1.6-4.7A8.5 8.5 0 1 1 8 19.5z"/><path d="M9 9c0 3 3 6 6 6l1-1.5-2-1-1 .7c-1-.4-1.8-1.200-2.200-2.200l.7-1-1-2z"/>',
		'facebook'  => '<path d="M14 8h3V4h-3a4 4 0 0 0-4 4v3H7v4h3v6h4v-6h3l1-4h-4V8z"/>',
		'pinterest' => '<circle cx="12" cy="12" r="9"/><path d="M10.500 20.500 12 13m-1.500-3.500c1-2 4.500-1.500 4.500 1 0 2-1.500 3.500-3 3.500"/>',
		'x'         => '<path d="m4 4 16 16M20 4 4 20"/>',
		'telegram'  => '<path d="M21 4 3 11l5.500 2L18 7l-8 7.500V19l3-3 4.500 3z"/>',
		'linkedin'  => '<rect x="3" y="3" width="18" height="18" rx="3"/><path d="M8 11v5M8 8v.01M12 16v-5m0 2a3 3 0 0 1 5 0v3"/>',
		'instagram' => '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><path d="M17.500 6.500v.01"/>',
		'youtube'   => '<rect x="2.500" y="5.500" width="19" height="13" rx="4"/><path d="m10 9.500 5 2.500-5 2.500z"/>',
		'tiktok'    => '<path d="M14 3v12a4 4 0 1 1-4-4m4-8c0 3 2 5 5 5"/>',
		'threads'   => '<path d="M17.500 9A5.500 5.500 0 1 0 12 18c3 0 5-1.500 5-4s-2.500-3.500-5-3.500-3 1-3 2 1.500 1.700 3 1.700 3-1 3-4"/>',
	);
	$d = isset( $p[ $name ] ) ? $p[ $name ] : $p['link'];
	return '<svg class="klyv-i" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $d . '</svg>';
}

/**
 * Perfis sociais configurados.
 *
 * @return array[] Cada item: name, icon, url.
 */
function klyv_socials() {
	$out  = array();
	$base = array(
		'facebook'  => 'Facebook',
		'instagram' => 'Instagram',
		'pinterest' => 'Pinterest',
	);
	foreach ( $base as $k => $label ) {
		$url = klyv_opt( 'social_' . $k );
		if ( $url ) {
			$out[] = array(
				'name' => $label,
				'icon' => $k,
				'url'  => $url,
			);
		}
	}
	// Extras: uma por linha, "youtube|https://...".
	foreach ( preg_split( '/\R/', (string) klyv_opt( 'social_extra', '' ) ) as $line ) {
		$parts = array_map( 'trim', explode( '|', $line, 2 ) );
		if ( 2 === count( $parts ) && $parts[1] ) {
			$out[] = array(
				'name' => ucfirst( $parts[0] ),
				'icon' => strtolower( $parts[0] ),
				'url'  => $parts[1],
			);
		}
	}
	return $out;
}

/**
 * HTML dos links sociais.
 *
 * @param string $class Classe extra.
 * @return string
 */
function klyv_socials_html( $class = '' ) {
	$items = klyv_socials();
	if ( ! $items ) {
		return '';
	}
	$h = '<ul class="klyv-social ' . esc_attr( $class ) . '" role="list">';
	foreach ( $items as $s ) {
		/* translators: %s: nome da rede social. */
		$label = sprintf( __( 'Klyvorenza no %s', 'klyvorenza' ), $s['name'] );
		$h    .= '<li><a href="' . esc_url( $s['url'] ) . '" rel="me noopener" target="_blank" aria-label="' . esc_attr( $label ) . '">' . klyv_icon( $s['icon'] ) . '</a></li>';
	}
	return $h . '</ul>';
}

/**
 * Imagem do tema (logo) com srcset WebP.
 *
 * @param string $kind logo|simbolo.
 * @param array  $attr Atributos extras.
 * @return string
 */
function klyv_logo_img( $kind = 'logo', $attr = array() ) {
	$u = KLYV_URI . '/assets/logo/';
	if ( 'simbolo' === $kind ) {
		$w   = 96;
		$h   = 96;
		$img = sprintf(
			'<img src="%1$ssimbolo-1x.webp" srcset="%1$ssimbolo-1x.webp 1x, %1$ssimbolo-2x.webp 2x" width="%2$d" height="%3$d" alt="%4$s" decoding="async"%5$s>',
			esc_url( $u ),
			$w,
			$h,
			esc_attr( $attr['alt'] ?? '' ),
			isset( $attr['lazy'] ) && $attr['lazy'] ? ' loading="lazy"' : ''
		);
		return $img;
	}
	$fetch = ! empty( $attr['priority'] ) ? ' fetchpriority="high"' : '';
	$tpl   = '<img class="%6$s" src="%1$s%2$s-1x.webp" srcset="%1$s%2$s-1x.webp 1x, %1$s%2$s-2x.webp 2x" width="225" height="100" alt="%3$s" decoding="async"%4$s%5$s>';
	$alt   = get_bloginfo( 'name' );
	return sprintf( $tpl, esc_url( $u ), 'logo-site', esc_attr( $alt ), $fetch, '', 'klyv-logo klyv-logo--light' )
		. sprintf( $tpl, esc_url( $u ), 'logo-site-dark', '', '', ' loading="lazy" aria-hidden="true"', 'klyv-logo klyv-logo--dark' );
}
