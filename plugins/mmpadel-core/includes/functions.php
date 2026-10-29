<?php
/**
 * Funciones públicas del plugin. Las usan el tema y las plantillas.
 *
 * @package MMPadel_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Lee un ajuste de «Ajustes MMPadel».
 *
 * @param string $key Clave del ajuste.
 * @return string Valor o cadena vacía.
 */
function mmpadel_get_option( $key ) {
	$options = get_option( MMPadel_Settings::OPTION, array() );
	$value   = is_array( $options ) && isset( $options[ $key ] ) ? (string) $options[ $key ] : '';

	if ( '' === $value && 'email_destino' === $key ) {
		$value = (string) get_option( 'admin_email' );
	}

	return $value;
}

/**
 * Lee un campo propio de un contenido (tarifa, entrenador, evento…).
 *
 * @param string   $key     Clave del campo, sin prefijo.
 * @param int|null $post_id ID del contenido; por defecto el actual.
 * @return string
 */
function mmpadel_get_field( $key, $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	return (string) get_post_meta( (int) $post_id, MMPadel_Fields::meta_key( $key ), true );
}

/**
 * Precio en formato español: «45 €» o «45,50 €».
 *
 * @param string $price Precio guardado («45.50»).
 * @return string
 */
function mmpadel_format_price( $price ) {
	if ( '' === $price || ! is_numeric( $price ) ) {
		return '';
	}
	$decimals = floor( (float) $price ) === (float) $price ? 0 : 2;
	/* translators: %s: importe. */
	return sprintf( __( '%s €', 'mmpadel-core' ), number_format_i18n( (float) $price, $decimals ) );
}

/**
 * Enlace tel: a partir de un teléfono escrito a mano.
 *
 * @param string $phone Teléfono («952 00 00 00»).
 * @return string
 */
function mmpadel_get_tel_url( $phone ) {
	$digits = preg_replace( '/[^0-9+]/', '', (string) $phone );
	if ( '' === $digits ) {
		return '';
	}
	if ( 9 === strlen( $digits ) ) {
		$digits = '+34' . $digits;
	}
	return 'tel:' . $digits;
}

/**
 * Enlace de WhatsApp (wa.me).
 *
 * @param string $number Número; si no se indica, el de Ajustes MMPadel.
 * @param string $text   Mensaje inicial opcional.
 * @return string
 */
function mmpadel_get_whatsapp_url( $number = '', $text = '' ) {
	$number = '' !== $number ? $number : mmpadel_get_option( 'whatsapp' );
	$digits = preg_replace( '/\D/', '', (string) $number );
	if ( '' === $digits ) {
		return '';
	}
	if ( 9 === strlen( $digits ) ) {
		$digits = '34' . $digits;
	}
	$url = 'https://wa.me/' . $digits;
	if ( '' !== $text ) {
		$url = add_query_arg( 'text', rawurlencode( $text ), $url );
	}
	return $url;
}

/**
 * URL de una página por su slug, con la ruta prevista como reserva.
 *
 * @param string $slug Slug de la página.
 * @return string
 */
function mmpadel_get_page_url( $slug ) {
	$page = get_page_by_path( $slug );
	return $page ? (string) get_permalink( $page ) : home_url( '/' . $slug . '/' );
}

/**
 * Carga una plantilla del plugin. El tema puede sustituirla copiándola a
 * {tema}/mmpadel/{nombre}.php.
 *
 * @param string $name Nombre de la plantilla, sin extensión.
 * @param array  $args Variables para la plantilla.
 * @return string HTML generado.
 */
function mmpadel_render_template( $name, array $args = array() ) {
	$name     = sanitize_file_name( $name );
	$template = locate_template( 'mmpadel/' . $name . '.php' );
	if ( ! $template ) {
		$template = MMPADEL_CORE_DIR . 'templates/' . $name . '.php';
	}
	if ( ! file_exists( $template ) ) {
		return '';
	}

	ob_start();
	load_template( $template, false, $args );
	return (string) ob_get_clean();
}
