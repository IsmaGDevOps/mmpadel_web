<?php
/**
 * Funciones del tema MMPadel.
 *
 * El tema solo se ocupa de la presentación. Los contenidos propios (tarifas,
 * equipo, eventos, FAQ, formulario) y los ajustes de contacto viven en el
 * plugin mmpadel-core; si el plugin no está activo, el tema sigue funcionando.
 *
 * @package MMPadel
 */

defined( 'ABSPATH' ) || exit;

define( 'MMPADEL_THEME_VERSION', '0.1.0' );

require get_template_directory() . '/inc/setup.php';
require get_template_directory() . '/inc/assets.php';
require get_template_directory() . '/inc/template-tags.php';
