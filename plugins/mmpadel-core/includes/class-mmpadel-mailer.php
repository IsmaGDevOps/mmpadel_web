<?php
/**
 * Emails del formulario: aviso a MMPadel y respuesta automática al interesado.
 *
 * Texto plano: se lee bien en cualquier cliente de correo y en el móvil.
 *
 * @package MMPadel_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Envío de emails.
 */
final class MMPadel_Mailer {

	/**
	 * Aviso a MMPadel con los datos ordenados. Responder al email contesta al interesado.
	 *
	 * @param int   $id     ID de la solicitud.
	 * @param array $values Datos del formulario.
	 * @param array $origin Origen.
	 * @return bool
	 */
	public static function notify_school( $id, array $values, array $origin ) {
		$to = mmpadel_get_option( 'email_destino' );
		if ( ! is_email( $to ) ) {
			return false;
		}

		$schema   = MMPadel_Form::schema();
		$sections = MMPadel_Form::sections();
		$lines    = array();

		foreach ( $sections as $section_key => $section_title ) {
			$lines[] = mb_strtoupper( $section_title );
			foreach ( $schema as $key => $field ) {
				if ( $field['section'] !== $section_key ) {
					continue;
				}
				$value = MMPadel_Form::display_value( $key, $values[ $key ], $schema );
				if ( 'textarea' === $field['type'] && '' !== $value ) {
					$lines[] = $field['short'] . ":\n" . $value;
				} else {
					$lines[] = $field['short'] . ': ' . ( '' !== $value ? $value : '—' );
				}
			}
			$lines[] = '';
		}

		$lines[] = mb_strtoupper( __( 'Origen', 'mmpadel-core' ) );
		$lines[] = self::origin_summary( $origin );
		$lines[] = '';
		$lines[] = __( 'Ver la solicitud en el panel:', 'mmpadel-core' );
		$lines[] = admin_url( 'post.php?post=' . (int) $id . '&action=edit' );

		$subject = sprintf(
			/* translators: 1: nombre, 2: nivel. */
			__( 'Nueva solicitud: %1$s (%2$s)', 'mmpadel-core' ),
			$values['nombre'],
			MMPadel_Form::display_value( 'nivel', $values['nivel'], $schema )
		);

		$headers = array( 'Content-Type: text/plain; charset=UTF-8' );
		if ( is_email( $values['email'] ) ) {
			$headers[] = 'Reply-To: ' . self::header_name( $values['nombre'] ) . ' <' . $values['email'] . '>';
		}

		return wp_mail( $to, self::header_text( $subject ), implode( "\n", $lines ), $headers );
	}

	/**
	 * Respuesta automática al interesado.
	 *
	 * @param array $values Datos del formulario.
	 * @return bool
	 */
	public static function autoreply( array $values ) {
		if ( ! is_email( $values['email'] ) ) {
			return false;
		}

		$site  = get_bloginfo( 'name' );
		$texto = mmpadel_get_option( 'autorespuesta_texto' );
		if ( '' === $texto ) {
			$texto = __( "Hemos recibido tu solicitud para encontrar grupo. La revisamos y te escribimos en 1-2 días laborables por WhatsApp o email con una propuesta de grupo y horario.\n\nSi tienes cualquier duda, responde a este email.", 'mmpadel-core' );
		}

		/* translators: %s: nombre del interesado. */
		$lines = array( sprintf( __( 'Hola, %s:', 'mmpadel-core' ), self::first_name( $values['nombre'] ) ), '', $texto, '' );

		$canal = mmpadel_get_option( 'canal_plazas_url' );
		if ( $canal ) {
			$lines[] = __( 'Mientras tanto, únete a nuestro canal de plazas libres para enterarte antes que nadie de los huecos que quedan:', 'mmpadel-core' );
			$lines[] = $canal;
			$lines[] = '';
		}

		$lines[]  = __( 'Un saludo,', 'mmpadel-core' );
		$lines[]  = $site;
		$telefono = mmpadel_get_option( 'telefono' );
		if ( $telefono ) {
			$lines[] = $telefono;
		}
		$lines[] = home_url( '/' );

		$headers = array( 'Content-Type: text/plain; charset=UTF-8' );
		$reply   = mmpadel_get_option( 'email_publico' );
		if ( is_email( $reply ) ) {
			$headers[] = 'Reply-To: ' . self::header_name( $site ) . ' <' . $reply . '>';
		}

		/* translators: %s: nombre del sitio. */
		$subject = sprintf( __( 'Hemos recibido tu solicitud – %s', 'mmpadel-core' ), $site );

		return wp_mail( $values['email'], self::header_text( $subject ), implode( "\n", $lines ), $headers );
	}

	/**
	 * Resumen del origen en una línea por dato.
	 *
	 * @param array $origin Origen.
	 * @return string
	 */
	public static function origin_summary( array $origin ) {
		$labels = array(
			'utm_source'   => 'utm_source',
			'utm_medium'   => 'utm_medium',
			'utm_campaign' => 'utm_campaign',
			'utm_term'     => 'utm_term',
			'utm_content'  => 'utm_content',
			'referrer'     => __( 'Procedencia', 'mmpadel-core' ),
			'landing'      => __( 'Página de entrada', 'mmpadel-core' ),
		);

		$lines = array();
		foreach ( $labels as $key => $label ) {
			if ( ! empty( $origin[ $key ] ) ) {
				$lines[] = $label . ': ' . $origin[ $key ];
			}
		}
		return $lines ? implode( "\n", $lines ) : __( 'Directo o desconocido', 'mmpadel-core' );
	}

	/**
	 * Texto apto para una cabecera: sin saltos de línea (evita inyectar cabeceras).
	 *
	 * @param string $text Texto.
	 * @return string
	 */
	private static function header_text( $text ) {
		return trim( preg_replace( '/[\r\n\t]+/', ' ', (string) $text ) );
	}

	/**
	 * Nombre para «Nombre <email>»: sin saltos de línea ni caracteres que rompan la cabecera.
	 *
	 * @param string $name Nombre.
	 * @return string
	 */
	private static function header_name( $name ) {
		return str_replace( array( '"', '<', '>', ',', ';', ':' ), '', self::header_text( $name ) );
	}

	/**
	 * Primer nombre para el saludo.
	 *
	 * @param string $name Nombre completo.
	 * @return string
	 */
	private static function first_name( $name ) {
		$parts = preg_split( '/\s+/u', trim( $name ) );
		return $parts && '' !== $parts[0] ? $parts[0] : $name;
	}
}
