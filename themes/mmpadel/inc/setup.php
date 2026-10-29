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
 * Favicon del tema mientras no se suba un icono del sitio desde el panel, y
 * color de la barra del navegador en el móvil.
 */
function mmpadel_theme_head_icons() {
	if ( ! has_site_icon() ) {
		$icon = mmpadel_theme_image_url( 'favicon.png' );
		printf( '<link rel="icon" href="%s" sizes="512x512" type="image/png">' . "\n", esc_url( $icon ) );
		printf( '<link rel="apple-touch-icon" href="%s">' . "\n", esc_url( $icon ) );
	}
	echo '<meta name="theme-color" content="#2A2763">' . "\n";
}
add_action( 'wp_head', 'mmpadel_theme_head_icons', 2 );

/**
 * Logo de MMPadel en la pantalla de acceso al panel.
 */
function mmpadel_theme_login_logo() {
	wp_add_inline_style(
		'login',
		sprintf(
			'#login h1 a { background-image: url(%s); background-size: contain; width: 120px; height: 120px; border-radius: 12px; }',
			esc_url( mmpadel_theme_image_url( 'logommpadel.webp' ) )
		)
	);
}
add_action( 'login_enqueue_scripts', 'mmpadel_theme_login_logo' );

/**
 * El logo del acceso enlaza a la web y se anuncia con su nombre.
 *
 * @return string
 */
function mmpadel_theme_login_url() {
	return home_url( '/' );
}
add_filter( 'login_headerurl', 'mmpadel_theme_login_url' );

/**
 * Texto accesible del logo del acceso.
 *
 * @return string
 */
function mmpadel_theme_login_text() {
	return get_bloginfo( 'name' );
}
add_filter( 'login_headertext', 'mmpadel_theme_login_text' );

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
 * Clase en body para las páginas interiores con foto de portada: la franja
 * de la foto empieza justo bajo la cabecera.
 *
 * @param string[] $classes Clases del body.
 * @return string[]
 */
function mmpadel_theme_body_class( $classes ) {
	if ( is_page() && ! is_front_page() && mmpadel_theme_cover( get_queried_object_id() ) ) {
		$classes[] = 'tiene-portada';
	}
	return $classes;
}
add_filter( 'body_class', 'mmpadel_theme_body_class' );

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
