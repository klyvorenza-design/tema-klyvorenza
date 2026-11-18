<?php
/**
 * Google AdSense: 3 anúncios no artigo, 2 nas laterais, carregamento tardio.
 *
 * @package Klyvorenza
 */

defined( 'ABSPATH' ) || exit;

/** Páginas sem anúncio (institucionais, política, termos, contato). */
function klyv_ads_allowed() {
	static $ok = null;
	if ( null !== $ok ) {
		return $ok;
	}
	$ok = is_singular( 'post' ) && ! klyv_is_story() && (bool) klyv_opt( 'adsense_pub' );
	return $ok = (bool) apply_filters( 'klyvorenza_ads_allowed', $ok );
}

/**
 * Um espaço de anúncio. Retorna vazio se desligado, sem código ou página sem anúncio.
 *
 * @param string $key  Opção com o código.
 * @param string $on   Opção que liga o espaço.
 * @param string $kind in|side.
 * @return string
 */
function klyv_ad_slot( $key, $on, $kind ) {
	if ( ! klyv_ads_allowed() || ! klyv_opt( $on ) ) {
		return '';
	}
	$code = trim( (string) klyv_opt( $key, '' ) );
	if ( '' === $code ) {
		return '';
	}
	// O script da biblioteca é carregado por nós, depois da interação.
	$code = preg_replace( '#<script[^>]+adsbygoogle\.js[^>]*>\s*</script>#i', '', $code );
	$GLOBALS['klyv_ads_used'] = true;
	return '<aside class="klyv-ad klyv-ad--' . esc_attr( $kind ) . '" aria-label="' . esc_attr__( 'Publicidade', 'klyvorenza' ) . '"><span class="klyv-ad__l">' . esc_html__( 'Publicidade', 'klyvorenza' ) . '</span><div class="klyv-ad__c">' . $code . '</div></aside>'; // phpcs:ignore WordPress.Security.EscapeOutput -- código de anúncio salvo por administrador.
}

/** Insere os anúncios no conteúdo, com espaçamento e longe de títulos, imagens e listas. */
add_filter(
	'the_content',
	function ( $content ) {
		if ( ! klyv_ads_allowed() || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}
		$parts = explode( '</p>', $content );
		$total = count( $parts ) - 1; // número de parágrafos fechados.
		if ( $total < 7 ) {
			return $content;
		}
		$safe = function ( $i ) use ( $parts, $total ) { // anúncio após o parágrafo $i (0-based).
			if ( $i < 0 || $i >= $total - 1 ) {
				return false;
			}
			$next = ltrim( $parts[ $i + 1 ] );
			$here = $parts[ $i ];
			return 0 === strpos( $next, '<p' ) && false === strpos( $here, '<img' ) && false === strpos( $here, '<figure' );
		};
		$wanted = array(
			array( 'ads_in_1', 'ads_on_in_1', 2 ),
			array( 'ads_in_2', 'ads_on_in_2', (int) floor( $total / 2 ) ),
			array( 'ads_in_3', 'ads_on_in_3', $total - 3 ),
		);
		$used   = array();
		$ins    = array();
		foreach ( $wanted as $w ) {
			$slot = klyv_ad_slot( $w[0], $w[1], 'in' );
			if ( '' === $slot ) {
				continue;
			}
			$pos = null;
			for ( $d = 0; $d <= 4 && null === $pos; $d++ ) {
				foreach ( array( $w[2] + $d, $w[2] - $d ) as $c ) {
					$far = true;
					foreach ( $used as $u ) {
						if ( abs( $u - $c ) < 4 ) {
							$far = false;
						}
					}
					if ( $far && $safe( $c ) ) {
						$pos = $c;
						break;
					}
				}
			}
			if ( null !== $pos ) {
				$used[]       = $pos;
				$ins[ $pos ] = $slot;
			}
		}
		$out = '';
		foreach ( $parts as $i => $part ) {
			$out .= $part . ( $i < $total ? '</p>' : '' );
			if ( isset( $ins[ $i ] ) ) {
				$out .= $ins[ $i ];
			}
		}
		return $out;
	},
	20
);

/** Carrega a biblioteca depois da interação ou do load, respeitando o consentimento. */
add_action(
	'wp_footer',
	function () {
		if ( empty( $GLOBALS['klyv_ads_used'] ) || ! klyv_opt( 'adsense_pub' ) ) {
			return;
		}
		if ( ! apply_filters( 'klyvorenza_ads_consent', true ) ) {
			return;
		}
		$src = 'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=' . rawurlencode( klyv_opt( 'adsense_pub' ) );
		?>
<script>(function(){var d=0,s=<?php echo wp_json_encode( $src ); ?>;function go(){if(d||window.klyvAdsConsent===false)return;d=1;var e=document.createElement("script");e.async=true;e.crossOrigin="anonymous";e.src=s;document.head.appendChild(e)}["scroll","pointerdown","keydown","touchstart"].forEach(function(t){addEventListener(t,go,{once:true,passive:true})});addEventListener("load",function(){setTimeout(go,4000)});addEventListener("klyvorenza:consent",go)})();</script>
		<?php
	},
	30
);
