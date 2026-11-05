<?php
/**
 * Carga de estilos y scripts.
 *
 * Sin proceso de compilación: CSS y JS se sirven tal cual.
 *
 * @package MMPadel
 */

defined( 'ABSPATH' ) || exit;

/**
 * Versión de un recurso del tema. En local usa la fecha del archivo para
 * evitar la caché del navegador mientras se desarrolla.
 *
 * @param string $relative_path Ruta relativa al tema.
 * @return string
 */
function mmpadel_theme_asset_version( $relative_path ) {
	if ( 'local' === wp_get_environment_type() ) {
		$file = get_template_directory() . '/' . $relative_path;
		if ( file_exists( $file ) ) {
			return (string) filemtime( $file );
		}
	}
	return MMPADEL_THEME_VERSION;
}

/**
 * Encola los recursos del front.
 */
function mmpadel_theme_enqueue_assets() {
	wp_enqueue_style(
		'mmpadel',
		get_template_directory_uri() . '/assets/css/main.css',
		array(),
		mmpadel_theme_asset_version( 'assets/css/main.css' )
	);

	wp_enqueue_script(
		'mmpadel-navigation',
		get_template_directory_uri() . '/assets/js/navigation.js',
		array(),
		mmpadel_theme_asset_version( 'assets/js/navigation.js' ),
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);
}
add_action( 'wp_enqueue_scripts', 'mmpadel_theme_enqueue_assets' );

/**
 * Pista 3D (three.js) de la sección «Qué trabajamos en cada nivel».
 *
 * Solo se carga en las páginas con un bloque de clase «mmpadel-pista». Este
 * módulo pesa poco; three.js (assets/vendor/three) lo descarga él mismo
 * cuando la sección se acerca a la pantalla.
 */
function mmpadel_theme_enqueue_pista() {
	if ( ! is_singular() || ! str_contains( (string) get_post_field( 'post_content', get_queried_object_id() ), 'mmpadel-pista' ) ) {
		return;
	}

	wp_enqueue_script_module(
		'mmpadel-pista',
		get_template_directory_uri() . '/assets/js/pista.js',
		array(),
		mmpadel_theme_asset_version( 'assets/js/pista.js' )
	);
}
add_action( 'wp_enqueue_scripts', 'mmpadel_theme_enqueue_pista' );

/**
 * Rally de cinco estrellas (three.js) en el panel del marcador de Google.
 *
 * Solo se carga en las páginas con un bloque de clase «mmpadel-opiniones-nota»
 * (la página Opiniones). Como la pista, descarga three.js cuando hace falta.
 */
function mmpadel_theme_enqueue_estrellas() {
	if ( ! is_singular() || ! str_contains( (string) get_post_field( 'post_content', get_queried_object_id() ), 'mmpadel-opiniones-nota' ) ) {
		return;
	}

	wp_enqueue_script_module(
		'mmpadel-estrellas',
		get_template_directory_uri() . '/assets/js/estrellas.js',
		array(),
		mmpadel_theme_asset_version( 'assets/js/estrellas.js' )
	);
}
add_action( 'wp_enqueue_scripts', 'mmpadel_theme_enqueue_estrellas' );
