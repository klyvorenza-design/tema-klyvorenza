<?php
/**
 * Klyvorenza: carregamento enxuto. A lógica fica em inc/.
 *
 * @package Klyvorenza
 */

defined( 'ABSPATH' ) || exit;

define( 'KLYV_VERSION', '1.0.0' );
define( 'KLYV_DIR', get_template_directory() );
define( 'KLYV_URI', get_template_directory_uri() );

$klyv_files = array(
	'helpers',
	'setup',
	'options',
	'menu',
	'blocks',
	'slide',
	'home',
	'main-image',
	'toc',
	'share',
	'ads',
	'newsletter',
	'gtranslate',
	'seo',
	'llms',
);
foreach ( $klyv_files as $klyv_file ) {
	require_once KLYV_DIR . '/inc/' . $klyv_file . '.php';
}
unset( $klyv_files, $klyv_file );
