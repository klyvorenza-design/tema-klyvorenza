<?php
/**
 * Painel Aparência > Opções Klyvorenza (API de Configurações).
 *
 * @package Klyvorenza
 */

defined( 'ABSPATH' ) || exit;

/**
 * Definição dos campos por seção.
 *
 * @return array
 */
function klyv_option_fields() {
	$share = array(
		'whatsapp'  => 'WhatsApp',
		'facebook'  => 'Facebook',
		'pinterest' => 'Pinterest',
		'x'         => 'X (Twitter)',
		'telegram'  => 'Telegram',
		'email'     => 'E-mail',
		'linkedin'  => 'LinkedIn',
		'copiar'    => __( 'Copiar link', 'klyvorenza' ),
	);
	return array(
		'adsense'    => array(
			'title'  => 'AdSense',
			'fields' => array(
				'adsense_pub'    => array( 'text', __( 'ID do editor (ca-pub-...)', 'klyvorenza' ) ),
				'ads_in_1'       => array( 'code', __( 'Código do anúncio 1 do artigo (após o 3º parágrafo)', 'klyvorenza' ) ),
				'ads_on_in_1'    => array( 'check', __( 'Ligar anúncio 1 do artigo', 'klyvorenza' ) ),
				'ads_in_2'       => array( 'code', __( 'Código do anúncio 2 do artigo (meio do texto)', 'klyvorenza' ) ),
				'ads_on_in_2'    => array( 'check', __( 'Ligar anúncio 2 do artigo', 'klyvorenza' ) ),
				'ads_in_3'       => array( 'code', __( 'Código do anúncio 3 do artigo (antes da conclusão)', 'klyvorenza' ) ),
				'ads_on_in_3'    => array( 'check', __( 'Ligar anúncio 3 do artigo', 'klyvorenza' ) ),
				'ads_side_left'  => array( 'code', __( 'Código do anúncio da barra esquerda', 'klyvorenza' ) ),
				'ads_on_left'    => array( 'check', __( 'Ligar anúncio da barra esquerda', 'klyvorenza' ) ),
				'ads_side_right' => array( 'code', __( 'Código do anúncio da barra direita', 'klyvorenza' ) ),
				'ads_on_right'   => array( 'check', __( 'Ligar anúncio da barra direita', 'klyvorenza' ) ),
			),
		),
		'social'     => array(
			'title'  => __( 'Redes sociais', 'klyvorenza' ),
			'fields' => array(
				'social_facebook'  => array( 'url', 'Facebook' ),
				'social_instagram' => array( 'url', 'Instagram' ),
				'social_pinterest' => array( 'url', 'Pinterest' ),
				'social_extra'     => array( 'textarea', __( 'Outras redes (uma por linha, formato nome|url. Ex.: youtube|https://...)', 'klyvorenza' ) ),
			),
		),
		'newsletter' => array(
			'title'  => 'Newsletter',
			'fields' => array(
				'news_on'           => array( 'check', __( 'Ligar newsletter e pop-up', 'klyvorenza' ) ),
				'news_delay'        => array( 'number', __( 'Segundos até abrir o pop-up', 'klyvorenza' ) ),
				'news_title'        => array( 'text', __( 'Título', 'klyvorenza' ) ),
				'news_text'         => array( 'textarea', __( 'Texto', 'klyvorenza' ) ),
				'news_double_optin' => array( 'check', __( 'Confirmar por e-mail (double opt-in)', 'klyvorenza' ) ),
			),
		),
		'share'      => array(
			'title'  => __( 'Compartilhamento', 'klyvorenza' ),
			'fields' => array(
				'share_buttons' => array( 'multi', __( 'Botões exibidos', 'klyvorenza' ), $share ),
			),
		),
		'layout'     => array(
			'title'  => 'Layout',
			'fields' => array(
				'sidebars_on' => array( 'check', __( 'Mostrar barras laterais no desktop (artigos)', 'klyvorenza' ) ),
			),
		),
		'slide'      => array(
			'title'  => __( 'Slide da home', 'klyvorenza' ),
			'fields' => array(
				'slide_on'       => array( 'check', __( 'Ligar slide', 'klyvorenza' ) ),
				'slide_count'    => array( 'number', __( 'Número de slides', 'klyvorenza' ) ),
				'slide_interval' => array( 'number', __( 'Segundos entre slides', 'klyvorenza' ) ),
				'slide_autoplay' => array( 'check', __( 'Rolagem automática', 'klyvorenza' ) ),
			),
		),
		'categorias' => array(
			'title'  => __( 'Categorias, menus e glossário', 'klyvorenza' ),
			'fields' => array(
				'parent_especiais'   => array( 'text', __( 'Slug da categoria-mãe de "Especiais"', 'klyvorenza' ) ),
				'parent_datas'       => array( 'text', __( 'Slug da categoria-mãe de "Datas Festivas"', 'klyvorenza' ) ),
				'hidden_cats'        => array( 'text', __( 'Categorias ocultas na home (slugs separados por vírgula)', 'klyvorenza' ) ),
				'cat_order'          => array( 'text', __( 'Ordem das seções da home (slugs separados por vírgula; as demais vêm depois, pela data)', 'klyvorenza' ) ),
				'popular_cats'       => array( 'text', __( 'Categorias sugeridas na busca (slugs, vírgula). Vazio = mais populares', 'klyvorenza' ) ),
				'glossary_post_type' => array( 'text', __( 'Tipo de conteúdo do glossário (deixe vazio para detectar)', 'klyvorenza' ) ),
			),
		),
	);
}

add_action(
	'admin_menu',
	function () {
		add_theme_page( __( 'Opções Klyvorenza', 'klyvorenza' ), __( 'Opções Klyvorenza', 'klyvorenza' ), 'manage_options', 'klyvorenza', 'klyv_render_options' );
	}
);

add_action(
	'admin_init',
	function () {
		register_setting(
			'klyvorenza',
			'klyvorenza_options',
			array(
				'type'              => 'array',
				'sanitize_callback' => 'klyv_sanitize_options',
				'default'           => klyv_defaults(),
			)
		);
	}
);

/**
 * Sanitiza as opções conforme o tipo de campo.
 *
 * @param mixed $in Dados recebidos.
 * @return array
 */
function klyv_sanitize_options( $in ) {
	if ( ! current_user_can( 'manage_options' ) ) {
		return get_option( 'klyvorenza_options', array() );
	}
	$in  = is_array( $in ) ? $in : array();
	$out = array();
	foreach ( klyv_option_fields() as $section ) {
		foreach ( $section['fields'] as $key => $f ) {
			$v = isset( $in[ $key ] ) ? wp_unslash( $in[ $key ] ) : null;
			switch ( $f[0] ) {
				case 'check':
					$out[ $key ] = empty( $v ) ? 0 : 1;
					break;
				case 'number':
					$out[ $key ] = max( 1, absint( $v ) );
					break;
				case 'url':
					$out[ $key ] = esc_url_raw( (string) $v );
					break;
				case 'multi':
					$out[ $key ] = array_values( array_intersect( array_keys( $f[2] ), (array) $v ) );
					break;
				case 'code':
					// Código de anúncio: só administradores com unfiltered_html podem salvar scripts.
					$out[ $key ] = current_user_can( 'unfiltered_html' ) ? (string) $v : wp_kses_post( (string) $v );
					break;
				case 'textarea':
					$out[ $key ] = sanitize_textarea_field( (string) $v );
					break;
				default:
					$out[ $key ] = sanitize_text_field( (string) $v );
			}
		}
	}
	$out['slide_count'] = min( 8, $out['slide_count'] );
	return $out;
}

/** Página de opções. */
function klyv_render_options() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$o = wp_parse_args( (array) get_option( 'klyvorenza_options', array() ), klyv_defaults() );
	echo '<div class="wrap"><h1>' . esc_html__( 'Opções Klyvorenza', 'klyvorenza' ) . '</h1>';
	klyv_render_welcome();
	echo '<form method="post" action="options.php">';
	settings_fields( 'klyvorenza' );
	foreach ( klyv_option_fields() as $section ) {
		echo '<h2>' . esc_html( $section['title'] ) . '</h2><table class="form-table" role="presentation">';
		foreach ( $section['fields'] as $key => $f ) {
			$name = 'klyvorenza_options[' . $key . ']';
			$val  = $o[ $key ] ?? '';
			echo '<tr><th scope="row">' . esc_html( $f[1] ) . '</th><td>';
			switch ( $f[0] ) {
				case 'check':
					printf( '<input type="checkbox" name="%s" value="1" %s>', esc_attr( $name ), checked( ! empty( $val ), true, false ) );
					break;
				case 'multi':
					foreach ( $f[2] as $k => $label ) {
						printf( '<label style="display:inline-block;margin-right:14px"><input type="checkbox" name="%s[]" value="%s" %s> %s</label>', esc_attr( $name ), esc_attr( $k ), checked( in_array( $k, (array) $val, true ), true, false ), esc_html( $label ) );
					}
					break;
				case 'textarea':
				case 'code':
					printf( '<textarea class="large-text%s" rows="4" name="%s">%s</textarea>', 'code' === $f[0] ? ' code' : '', esc_attr( $name ), esc_textarea( (string) $val ) );
					break;
				case 'number':
					printf( '<input type="number" min="1" class="small-text" name="%s" value="%s">', esc_attr( $name ), esc_attr( $val ) );
					break;
				default:
					printf( '<input type="%s" class="regular-text" name="%s" value="%s">', 'url' === $f[0] ? 'url' : 'text', esc_attr( $name ), esc_attr( (string) $val ) );
			}
			echo '</td></tr>';
		}
		echo '</table>';
	}
	submit_button();
	echo '</form>';
	if ( function_exists( 'klyv_news_export_link' ) ) {
		echo '<h2>' . esc_html__( 'Contatos da newsletter', 'klyvorenza' ) . '</h2><p><a class="button" href="' . esc_url( klyv_news_export_link() ) . '">' . esc_html__( 'Exportar CSV', 'klyvorenza' ) . '</a></p>';
	}
	echo '</div>';
}
