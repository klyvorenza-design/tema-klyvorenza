<?php
/**
 * /llms.txt e robots.txt amigáveis a rastreadores de busca e de IA.
 *
 * @package Klyvorenza
 */

defined( 'ABSPATH' ) || exit;

/** Conteúdo do llms.txt (em Markdown, em cache). */
function klyv_llms_text() {
	return klyv_cached(
		'llms',
		function () {
			$o  = '# ' . get_bloginfo( 'name' ) . "\n\n";
			$o .= '> ' . ( get_bloginfo( 'description' ) ? get_bloginfo( 'description' ) : 'Blog brasileiro (pt-BR) de moda feminina, acessórios, maquiagem, cuidados com a pele, cabelos, unhas e perfumaria.' ) . "\n\n";
			$o .= "## Principais seções\n\n";
			$o .= '- [Início](' . home_url( '/' ) . ")\n";
			foreach ( klyv_main_menu() as $it ) {
				if ( home_url( '/' ) !== $it['url'] && '#' !== $it['url'] ) {
					$o .= '- [' . $it['title'] . '](' . $it['url'] . ")\n";
					foreach ( $it['children'] as $c ) {
						$o .= '  - [' . $c['title'] . '](' . $c['url'] . ")\n";
					}
				}
			}
			$o .= "\n## Categorias\n\n";
			foreach ( klyv_active_categories() as $t ) {
				$o .= '- [' . $t->name . '](' . get_term_link( $t ) . ') (' . (int) $t->count . " artigos)\n";
			}
			$o .= "\n## Artigos recentes\n\n";
			foreach ( get_posts(
				array(
					'numberposts'   => 25,
					'no_found_rows' => true,
				)
			) as $p ) {
				$o .= '- [' . wp_strip_all_tags( get_the_title( $p ) ) . '](' . get_permalink( $p ) . '): ' . wp_trim_words( wp_strip_all_tags( get_the_excerpt( $p ) ), 24, '…' ) . "\n";
			}
			$about = get_page_by_path( 'sobre' );
			if ( $about ) {
				$o .= "\n## Sobre\n\n- [" . get_the_title( $about ) . '](' . get_permalink( $about ) . ")\n";
			}
			return $o;
		},
		HOUR_IN_SECONDS
	);
}

add_action(
	'parse_request',
	function ( $wp ) {
		if ( 'llms.txt' !== $wp->request ) {
			return;
		}
		status_header( 200 );
		header( 'Content-Type: text/plain; charset=utf-8' );
		header( 'X-Robots-Tag: noindex' );
		echo klyv_llms_text(); // phpcs:ignore WordPress.Security.EscapeOutput
		exit;
	}
);

/** Robots.txt: não bloqueia rastreadores legítimos de busca e de IA. */
add_filter(
	'robots_txt',
	function ( $output, $public ) {
		if ( ! $public ) {
			return $output;
		}
		$bots = array( 'Googlebot', 'Bingbot', 'GPTBot', 'OAI-SearchBot', 'ChatGPT-User', 'PerplexityBot', 'ClaudeBot', 'Claude-SearchBot', 'Google-Extended', 'Applebot-Extended' );
		$add  = "\n# Rastreadores de busca e de IA liberados (llms.txt em " . home_url( '/llms.txt' ) . ")\n";
		foreach ( $bots as $b ) {
			$add .= "User-agent: $b\nAllow: /\nDisallow: /wp-admin/\nAllow: /wp-admin/admin-ajax.php\n\n";
		}
		return $output . $add;
	},
	20,
	2
);
