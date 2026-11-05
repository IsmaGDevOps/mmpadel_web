<?php
/**
 * Desinstalación de MMPadel Core.
 *
 * Borra los ajustes y los permisos propios. NO borra tarifas, entrenadores,
 * eventos, FAQ, opiniones ni solicitudes: son contenido y datos de la escuela, y
 * perderlos por desinstalar el plugin sería irreversible. Si hay que
 * eliminarlos, se hace a mano desde el panel o con WP-CLI.
 *
 * @package MMPadel_Core
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'mmpadel_ajustes' );
delete_option( 'mmpadel_caps_version' );

$mmpadel_role = get_role( 'administrator' );
if ( $mmpadel_role ) {
	foreach ( array_keys( $mmpadel_role->capabilities ) as $mmpadel_cap ) {
		if ( str_contains( $mmpadel_cap, 'mmpadel_solicitud' ) ) {
			$mmpadel_role->remove_cap( $mmpadel_cap );
		}
	}
}
