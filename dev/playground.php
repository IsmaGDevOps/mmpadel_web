<?php
/**
 * Prepara la demo de WordPress Playground. Lo descarga y ejecuta blueprint.json.
 *
 * WP-CLI falla dentro de Playground («memory access out of bounds»), así que
 * este script llama directamente a los comandos de MMPadel_CLI (paginas y
 * demo) con un WP_CLI mínimo que solo imprime los mensajes.
 *
 * phpcs:ignoreFile -- Script de la demo: no forma parte de la web.
 *
 * @package MMPadel
 */

namespace WP_CLI\Utils {

	/**
	 * Lee una opción de los argumentos, como la función real de WP-CLI.
	 *
	 * @param array  $assoc_args Opciones.
	 * @param string $flag       Nombre de la opción.
	 * @param mixed  $default    Valor si no está.
	 * @return mixed
	 */
	function get_flag_value( $assoc_args, $flag, $default = null ) {
		return $assoc_args[ $flag ] ?? $default;
	}
}

namespace {

	require_once '/wordpress/wp-load.php';

	/**
	 * Sustituto mínimo de WP-CLI para los comandos de MMPadel.
	 */
	class WP_CLI {
		public static function log( $message ) {
			echo $message, "\n";
		}

		public static function success( $message ) {
			echo 'OK: ', $message, "\n";
		}

		public static function error( $message ) {
			throw new RuntimeException( $message );
		}

		public static function confirm( $question, $assoc_args = array() ) {
			// En la demo siempre se confirma.
		}
	}

	// Fuera el contenido de ejemplo que trae WordPress de serie.
	foreach ( array( array( 'hello-world', 'post' ), array( 'sample-page', 'page' ), array( 'privacy-policy', 'page' ) ) as $item ) {
		$post = get_page_by_path( $item[0], OBJECT, $item[1] );
		if ( $post ) {
			wp_delete_post( $post->ID, true );
		}
	}

	// Enlaces permanentes /nombre-de-pagina/.
	global $wp_rewrite;
	$wp_rewrite->set_permalink_structure( '/%postname%/' );

	require_once MMPADEL_CORE_DIR . 'includes/class-mmpadel-cli.php';

	$cli = new MMPadel_CLI();
	$cli->paginas( array(), array() );
	$cli->demo( array(), array( 'yes' => true ) );

	// Reglas de los tipos de contenido del plugin (eventos, etc.).
	flush_rewrite_rules( false );
}
