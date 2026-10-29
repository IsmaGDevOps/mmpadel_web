<?php
/**
 * Soportes del tema y menús.
 *
 * @package MMPadel
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registra los soportes del tema y las ubicaciones de menú.
 */
function mmpadel_theme_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'editor-styles' );
	add_theme_support(
		'html5',
		array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
	);
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 96,
			'width'       => 320,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	add_editor_style( 'assets/css/editor.css' );

	add_image_size( 'mmpadel-tarjeta', 640, 400, true );

	register_nav_menus(
		array(
			'principal' => __( 'Menú principal', 'mmpadel' ),
			'legal'     => __( 'Menú legal (pie)', 'mmpadel' ),
		)
	);
}
add_action( 'after_setup_theme', 'mmpadel_theme_setup' );

/**
 * Estilos de bloque propios, elegibles desde el editor.
 */
function mmpadel_theme_block_styles() {
	register_block_style(
		'core/button',
		array(
			'name'  => 'cta',
			'label' => __( 'Destacado', 'mmpadel' ),
		)
	);
}
add_action( 'init', 'mmpadel_theme_block_styles' );

/**
 * Marca el documento como «con JavaScript» lo antes posible.
 *
 * Sin JS el menú móvil se muestra desplegado; con JS se pliega tras el botón.
 */
function mmpadel_theme_js_class() {
	wp_print_inline_script_tag( "document.documentElement.classList.replace('no-js','js');" );
}
add_action( 'wp_head', 'mmpadel_theme_js_class', 1 );

/**
 * Extracto más corto para las tarjetas.
 *
 * @return int
 */
function mmpadel_theme_excerpt_length() {
	return 24;
}
add_filter( 'excerpt_length', 'mmpadel_theme_excerpt_length' );

/**
 * Sustituye «[...]» por «…» al final del extracto.
 *
 * @return string
 */
function mmpadel_theme_excerpt_more() {
	return '…';
}
add_filter( 'excerpt_more', 'mmpadel_theme_excerpt_more' );
