<?php
/**
 * Configuração do tema, assets, limpeza do WordPress e modo escuro.
 *
 * @package Klyvorenza
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'after_setup_theme',
	function () {
		load_theme_textdomain( 'klyvorenza', KLYV_DIR . '/languages' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
		add_theme_support( 'editor-styles' );
		add_editor_style( 'assets/css/main.css' );
		add_post_type_support( 'page', 'excerpt' );

		register_block_pattern_category( 'klyvorenza', array( 'label' => 'Klyvorenza' ) );
		set_post_thumbnail_size( 600, 400, true );
		add_image_size( 'klyv-principal', 1200, 675, true );
		add_image_size( 'klyv-card', 600, 400, true );
		add_image_size( 'klyv-social', 1200, 630, true );
		add_image_size( 'klyv-story', 1080, 1440, true );
		add_image_size( 'klyv-slide', 1280, 720, true );
	}
);

add_filter(
	'image_size_names_choose',
	function ( $sizes ) {
		return array_merge(
			$sizes,
			array(
				'klyv-principal' => __( 'Principal do artigo (1200x675)', 'klyvorenza' ),
				'klyv-card'      => __( 'Cartão (600x400)', 'klyvorenza' ),
				'klyv-social'    => __( 'Compartilhamento (1200x630)', 'klyvorenza' ),
				'klyv-story'     => __( 'Web Story (1080x1440)', 'klyvorenza' ),
			)
		);
	}
);

/** Estilos e scripts (nada é carregado em páginas de Web Stories). */
add_action(
	'wp_enqueue_scripts',
	function () {
		if ( klyv_is_story() ) {
			return;
		}
		wp_enqueue_style( 'klyvorenza', KLYV_URI . '/assets/css/main.css', array(), KLYV_VERSION );
		wp_enqueue_script(
			'klyvorenza',
			KLYV_URI . '/assets/js/theme.js',
			array(),
			KLYV_VERSION,
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);
		wp_localize_script(
			'klyvorenza',
			'klyvI18n',
			array(
				'copied' => __( 'Link copiado', 'klyvorenza' ),
				'dark'   => __( 'Ativar modo escuro', 'klyvorenza' ),
				'light'  => __( 'Ativar modo claro', 'klyvorenza' ),
			)
		);
		if ( is_front_page() && klyv_opt( 'slide_on' ) ) {
			wp_enqueue_script(
				'klyvorenza-slider',
				KLYV_URI . '/assets/js/slider.js',
				array(),
				KLYV_VERSION,
				array(
					'strategy'  => 'defer',
					'in_footer' => true,
				)
			);
		}
		if ( klyv_newsletter_active() ) {
			wp_enqueue_script(
				'klyvorenza-news',
				KLYV_URI . '/assets/js/newsletter.js',
				array(),
				KLYV_VERSION,
				array(
					'strategy'  => 'defer',
					'in_footer' => true,
				)
			);
			wp_localize_script(
				'klyvorenza-news',
				'klyvNews',
				array(
					'rest'     => esc_url_raw( rest_url( 'klyvorenza/v1/newsletter' ) ),
					'delay'    => max( 3, (int) klyv_opt( 'news_delay', 20 ) ) * 1000,
					'popup'    => klyv_newsletter_popup_allowed(),
					'ok'       => __( 'Quase lá! Confira seu e-mail para confirmar a inscrição.', 'klyvorenza' ),
					'okDirect' => __( 'Inscrição feita. Obrigada!', 'klyvorenza' ),
					'error'    => __( 'Não foi possível enviar agora. Tente de novo em instantes.', 'klyvorenza' ),
				)
			);
		}
		if ( ! is_singular() ) {
			wp_dequeue_style( 'wp-block-library-theme' );
		}
	},
	20
);

/** CSS crítico mínimo e script do modo escuro, antes da pintura. */
add_action(
	'wp_head',
	function () {
		if ( klyv_is_story() ) {
			return;
		}
		echo '<script>(function(){try{var t=localStorage.getItem("klyv-theme");if(!t){t=matchMedia("(prefers-color-scheme: dark)").matches?"dark":"light"}document.documentElement.dataset.theme=t}catch(e){}})();</script>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput
		echo '<link rel="preload" href="' . esc_url( KLYV_URI . '/assets/fonts/playfair-display-latin-700-normal.woff2' ) . '" as="font" type="font/woff2" crossorigin>' . "\n";
	},
	0
);

/** Limpeza: emojis, wp-embed, wlwmanifest, rsd, versão do gerador. */
add_action(
	'init',
	function () {
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
		remove_action( 'wp_head', 'wlwmanifest_link' );
		remove_action( 'wp_head', 'rsd_link' );
		remove_action( 'wp_head', 'wp_generator' );
		remove_action( 'wp_head', 'wp_shortlink_wp_head' );
		remove_action( 'wp_head', 'rest_output_link_wp_head' );
		remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
		remove_action( 'wp_head', 'wp_oembed_add_host_js' );
	}
);
add_filter( 'wp_should_load_separate_core_block_assets', '__return_true' );
add_filter( 'should_load_separate_core_block_assets', '__return_true' );
add_filter( 'the_generator', '__return_empty_string' );
add_action(
	'wp_enqueue_scripts',
	function () {
		wp_dequeue_script( 'wp-embed' );
		wp_deregister_script( 'wp-embed' );
	},
	100
);
add_filter(
	'tiny_mce_plugins',
	function ( $p ) {
		return array_diff( $p, array( 'wpemoji' ) );
	}
);

/** Classes no body: barras laterais e tema. */
add_filter(
	'body_class',
	function ( $c ) {
		$c[] = klyv_opt( 'sidebars_on' ) ? 'klyv-sidebars' : 'klyv-no-sidebars';
		return $c;
	}
);

/** Ativação: define a página /blog/ como página de posts, sem apagar nada. */
add_action(
	'after_switch_theme',
	function () {
		klyv_bump_cache();
		if ( function_exists( 'klyv_news_install' ) ) {
			klyv_news_install();
		}
		if ( 'page' !== get_option( 'show_on_front' ) || ! get_option( 'page_for_posts' ) ) {
			$blog = get_page_by_path( 'blog' );
			if ( $blog && ! get_option( 'page_for_posts' ) ) {
				update_option( 'page_for_posts', $blog->ID );
				if ( 'page' !== get_option( 'show_on_front' ) ) {
					// Mantém a página inicial atual, se houver; senão deixa o template front-page.html atuar.
					update_option( 'show_on_front', get_option( 'page_on_front' ) ? 'page' : get_option( 'show_on_front' ) );
				}
			}
		}
		flush_rewrite_rules( false );
		update_option( 'klyv_welcome', 1, false );
	}
);

/** Template do arquivo de Web Stories para o tipo detectado. */
add_filter(
	'archive_template_hierarchy',
	function ( $templates ) {
		$types = klyv_story_types();
		if ( $types && is_post_type_archive( $types ) ) {
			array_unshift( $templates, 'archive-fws-story.php' );
		}
		return $templates;
	}
);
