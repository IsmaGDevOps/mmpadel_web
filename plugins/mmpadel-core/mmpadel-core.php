<?php
/**
 * Plugin Name:       MMPadel Core
 * Description:       Contenidos y lógica de la web de MMPadel: tarifas, entrenadores, pools y eventos, preguntas frecuentes, formulario «Encuentra tu grupo» y solicitudes.
 * Version:           0.1.0
 * Requires at least: 6.6
 * Requires PHP:      8.1
 * Author:            MMPadel
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       mmpadel-core
 *
 * @package MMPadel_Core
 */

defined( 'ABSPATH' ) || exit;

define( 'MMPADEL_CORE_VERSION', '0.1.0' );
define( 'MMPADEL_CORE_FILE', __FILE__ );
define( 'MMPADEL_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'MMPADEL_CORE_URL', plugin_dir_url( __FILE__ ) );

require MMPADEL_CORE_DIR . 'includes/functions.php';
require MMPADEL_CORE_DIR . 'includes/class-mmpadel-fields.php';
require MMPADEL_CORE_DIR . 'includes/class-mmpadel-post-types.php';
require MMPADEL_CORE_DIR . 'includes/class-mmpadel-settings.php';
require MMPADEL_CORE_DIR . 'includes/class-mmpadel-shortcodes.php';
require MMPADEL_CORE_DIR . 'includes/class-mmpadel-form.php';
require MMPADEL_CORE_DIR . 'includes/class-mmpadel-mailer.php';

MMPadel_Fields::init();
MMPadel_Post_Types::init();
MMPadel_Settings::init();
MMPadel_Shortcodes::init();
MMPadel_Form::init();

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require MMPADEL_CORE_DIR . 'includes/class-mmpadel-cli.php';
	WP_CLI::add_command( 'mmpadel', 'MMPadel_CLI' );
}

register_activation_hook( __FILE__, array( 'MMPadel_Post_Types', 'activate' ) );
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
