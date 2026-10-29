<?php
/**
 * Plugin Name: MMPadel – correo en local
 * Description: Solo para desarrollo. Intercepta wp_mail y guarda cada email en wp-content/mmpadel-mail.log en lugar de enviarlo.
 * Author: MMPadel
 *
 * Este archivo lo monta wp-env como mu-plugin. No se sube a producción.
 *
 * @package MMPadel
 */

defined( 'ABSPATH' ) || exit;

if ( 'local' !== wp_get_environment_type() ) {
	return;
}

add_filter(
	'pre_wp_mail',
	static function ( $short_circuit, $atts ) {
		$to      = is_array( $atts['to'] ) ? implode( ', ', $atts['to'] ) : $atts['to'];
		$headers = is_array( $atts['headers'] ) ? implode( "\n", $atts['headers'] ) : (string) $atts['headers'];

		$entry  = str_repeat( '=', 72 ) . "\n";
		$entry .= 'Fecha:   ' . wp_date( 'Y-m-d H:i:s' ) . "\n";
		$entry .= 'Para:    ' . $to . "\n";
		$entry .= 'Asunto:  ' . $atts['subject'] . "\n";
		$entry .= "Cabeceras:\n" . trim( $headers ) . "\n";
		$entry .= str_repeat( '-', 72 ) . "\n";
		$entry .= $atts['message'] . "\n\n";

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- log local de desarrollo.
		file_put_contents( WP_CONTENT_DIR . '/mmpadel-mail.log', $entry, FILE_APPEND | LOCK_EX );

		return true;
	},
	10,
	2
);
