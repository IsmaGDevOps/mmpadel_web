<?php
/**
 * Formulario «Encuentra tu grupo»: render, validación, antispam y guardado.
 *
 * Flujo (funciona sin JavaScript):
 *   1. [mmpadel_formulario] pinta el formulario, que se envía a admin-post.php.
 *   2. handle() comprueba nonce, honeypot, tiempo mínimo y límite por IP;
 *      valida y sanitiza en servidor.
 *   3. Si hay errores, los guarda en un transient y vuelve a la página con
 *      ?mmpadel_error={clave}: el formulario se repinta con los datos ya
 *      rellenos.
 *   4. Si todo va bien, guarda la Solicitud, envía los emails y vuelve con
 *      ?mmpadel_ok={clave}. La clave es de un solo uso: recargar la página no
 *      repite el aviso ni el evento form_submit.
 *
 * El esquema de campos (schema()) lo comparten el formulario, los emails, la
 * ficha de la solicitud en el panel y la exportación CSV.
 *
 * @package MMPadel_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Formulario de solicitud.
 */
final class MMPadel_Form {

	const ACTION      = 'mmpadel_solicitud';
	const NONCE       = 'mmpadel_form';
	const META_PREFIX = '_mmpadel_sol_';

	/**
	 * Segundos mínimos entre que se carga el formulario y se envía.
	 */
	const MIN_SECONDS = 3;

	/**
	 * Envíos permitidos por IP cada 10 minutos.
	 */
	const MAX_PER_IP = 5;

	/**
	 * Campos de origen que se aceptan (los rellena mmpadel-tracking.js).
	 */
	const ORIGIN_KEYS = array( 'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'referrer', 'landing' );

	/**
	 * Engancha las acciones.
	 */
	public static function init() {
		add_shortcode( 'mmpadel_formulario', array( __CLASS__, 'shortcode' ) );
		add_action( 'admin_post_nopriv_' . self::ACTION, array( __CLASS__, 'handle' ) );
		add_action( 'admin_post_' . self::ACTION, array( __CLASS__, 'handle' ) );
	}

	/**
	 * Opciones de los campos de elección.
	 *
	 * Provisionales: hay que cuadrarlas con los campos de alta del software de
	 * gestión cuando se elija. Se pueden cambiar con el filtro
	 * mmpadel_form_options sin tocar el plugin.
	 *
	 * @return array<string, array<string, string>>
	 */
	public static function options() {
		$tipos = array();
		$terms = get_terms(
			array(
				'taxonomy'   => MMPadel_Post_Types::TAX_TIPO_CLASE,
				'hide_empty' => false,
			)
		);
		foreach ( is_array( $terms ) ? $terms : array() as $term ) {
			$tipos[ $term->slug ] = $term->name;
		}
		$tipos['no-lo-se'] = __( 'Que me aconsejéis', 'mmpadel-core' );

		$options = array(
			'experiencia' => array(
				'nunca'    => __( 'Nunca he jugado', 'mmpadel-core' ),
				'menos-6m' => __( 'Menos de 6 meses', 'mmpadel-core' ),
				'6m-2a'    => __( 'Entre 6 meses y 2 años', 'mmpadel-core' ),
				'mas-2a'   => __( 'Más de 2 años', 'mmpadel-core' ),
			),
			'nivel'       => array(
				'iniciacion'      => __( 'Iniciación', 'mmpadel-core' ),
				'intermedio'      => __( 'Intermedio', 'mmpadel-core' ),
				'intermedio-alto' => __( 'Intermedio alto', 'mmpadel-core' ),
				'avanzado'        => __( 'Avanzado', 'mmpadel-core' ),
				'no-lo-se'        => __( 'No lo sé', 'mmpadel-core' ),
			),
			'objetivo'    => array(
				'aprender'   => __( 'Aprender desde cero', 'mmpadel-core' ),
				'mejorar'    => __( 'Mejorar mi técnica', 'mmpadel-core' ),
				'competir'   => __( 'Prepararme para competir', 'mmpadel-core' ),
				'socializar' => __( 'Jugar y conocer gente', 'mmpadel-core' ),
			),
			'dias'        => array(
				'lunes'     => __( 'Lunes', 'mmpadel-core' ),
				'martes'    => __( 'Martes', 'mmpadel-core' ),
				'miercoles' => __( 'Miércoles', 'mmpadel-core' ),
				'jueves'    => __( 'Jueves', 'mmpadel-core' ),
				'viernes'   => __( 'Viernes', 'mmpadel-core' ),
				'sabado'    => __( 'Sábado', 'mmpadel-core' ),
				'domingo'   => __( 'Domingo', 'mmpadel-core' ),
			),
			'franjas'     => array(
				'manana'   => __( 'Mañana (9–14 h)', 'mmpadel-core' ),
				'mediodia' => __( 'Mediodía (14–17 h)', 'mmpadel-core' ),
				'tarde'    => __( 'Tarde (17–20 h)', 'mmpadel-core' ),
				'noche'    => __( 'Noche (20–23 h)', 'mmpadel-core' ),
			),
			'personas'    => array(
				'1' => __( 'Solo yo', 'mmpadel-core' ),
				'2' => __( '2 personas', 'mmpadel-core' ),
				'3' => __( '3 personas', 'mmpadel-core' ),
				'4' => __( '4 personas', 'mmpadel-core' ),
			),
			'tipo_clase'  => $tipos,
		);

		/**
		 * Filtra las opciones del formulario «Encuentra tu grupo».
		 *
		 * @param array $options Campo => (valor => etiqueta).
		 */
		return apply_filters( 'mmpadel_form_options', $options );
	}

	/**
	 * Esquema de campos, en el orden del formulario.
	 *
	 * Claves de cada campo: label, short (etiqueta corta para emails/CSV),
	 * type (radio|checkbox|text|tel|email|textarea), options, required,
	 * section, help, autocomplete.
	 *
	 * @return array<string, array>
	 */
	public static function schema() {
		$o = self::options();

		$fields = array(
			'experiencia' => array(
				'label'   => __( '¿Cuánto tiempo llevas jugando?', 'mmpadel-core' ),
				'short'   => __( 'Experiencia', 'mmpadel-core' ),
				'type'    => 'radio',
				'options' => $o['experiencia'],
				'section' => 'juego',
			),
			'nivel'       => array(
				'label'   => __( '¿Cuál crees que es tu nivel?', 'mmpadel-core' ),
				'short'   => __( 'Nivel', 'mmpadel-core' ),
				'type'    => 'radio',
				'options' => $o['nivel'],
				'section' => 'juego',
				'help'    => __( 'Si dudas, marca «No lo sé»: el entrenador lo confirma en la primera clase.', 'mmpadel-core' ),
			),
			'objetivo'    => array(
				'label'   => __( '¿Qué buscas?', 'mmpadel-core' ),
				'short'   => __( 'Objetivo', 'mmpadel-core' ),
				'type'    => 'radio',
				'options' => $o['objetivo'],
				'section' => 'juego',
			),
			'dias'        => array(
				'label'   => __( '¿Qué días puedes?', 'mmpadel-core' ),
				'short'   => __( 'Días', 'mmpadel-core' ),
				'type'    => 'checkbox',
				'options' => $o['dias'],
				'section' => 'disponibilidad',
				'help'    => __( 'Marca todos los que te vengan bien.', 'mmpadel-core' ),
			),
			'franjas'     => array(
				'label'   => __( '¿En qué franjas horarias?', 'mmpadel-core' ),
				'short'   => __( 'Franjas', 'mmpadel-core' ),
				'type'    => 'checkbox',
				'options' => $o['franjas'],
				'section' => 'disponibilidad',
			),
			'personas'    => array(
				'label'   => __( '¿Cuántas personas os apuntáis?', 'mmpadel-core' ),
				'short'   => __( 'Personas', 'mmpadel-core' ),
				'type'    => 'radio',
				'options' => $o['personas'],
				'section' => 'clase',
				'help'    => __( 'Si venís varios, os buscamos plaza en el mismo grupo.', 'mmpadel-core' ),
			),
			'tipo_clase'  => array(
				'label'   => __( '¿Qué tipo de clase te interesa?', 'mmpadel-core' ),
				'short'   => __( 'Tipo de clase', 'mmpadel-core' ),
				'type'    => 'radio',
				'options' => $o['tipo_clase'],
				'section' => 'clase',
			),
			'nombre'      => array(
				'label'        => __( 'Nombre y apellidos', 'mmpadel-core' ),
				'short'        => __( 'Nombre', 'mmpadel-core' ),
				'type'         => 'text',
				'section'      => 'contacto',
				'autocomplete' => 'name',
			),
			'telefono'    => array(
				'label'        => __( 'Teléfono', 'mmpadel-core' ),
				'short'        => __( 'Teléfono', 'mmpadel-core' ),
				'type'         => 'tel',
				'section'      => 'contacto',
				'autocomplete' => 'tel',
				'help'         => __( 'Te escribiremos por WhatsApp a este número.', 'mmpadel-core' ),
			),
			'email'       => array(
				'label'        => __( 'Email', 'mmpadel-core' ),
				'short'        => __( 'Email', 'mmpadel-core' ),
				'type'         => 'email',
				'section'      => 'contacto',
				'autocomplete' => 'email',
			),
			'mensaje'     => array(
				'label'    => __( '¿Algo más que debamos saber? (opcional)', 'mmpadel-core' ),
				'short'    => __( 'Comentarios', 'mmpadel-core' ),
				'type'     => 'textarea',
				'section'  => 'contacto',
				'required' => false,
			),
		);

		foreach ( $fields as $key => $field ) {
			$fields[ $key ] = wp_parse_args(
				$field,
				array(
					'options'      => array(),
					'required'     => true,
					'help'         => '',
					'autocomplete' => '',
				)
			);
		}

		return $fields;
	}

	/**
	 * Títulos de las secciones del formulario.
	 *
	 * @return array<string, string>
	 */
	public static function sections() {
		return array(
			'juego'          => __( 'Tu juego', 'mmpadel-core' ),
			'disponibilidad' => __( 'Tu disponibilidad', 'mmpadel-core' ),
			'clase'          => __( 'La clase', 'mmpadel-core' ),
			'contacto'       => __( 'Tus datos', 'mmpadel-core' ),
		);
	}

	/**
	 * Texto legible de un valor guardado («lunes,martes» → «Lunes, Martes»).
	 *
	 * @param string $key   Campo.
	 * @param mixed  $value Valor (cadena o lista).
	 * @param array  $schema Esquema (para no recalcularlo en bucles).
	 * @return string
	 */
	public static function display_value( $key, $value, array $schema = array() ) {
		$schema = $schema ? $schema : self::schema();
		$field  = $schema[ $key ] ?? null;
		$values = is_array( $value ) ? $value : array_filter( explode( ',', (string) $value ), 'strlen' );

		if ( ! $field || ! $field['options'] ) {
			return is_array( $value ) ? implode( ', ', $value ) : (string) $value;
		}

		$labels = array();
		foreach ( $values as $v ) {
			// Si la opción ya no existe (p. ej. se borró un tipo de clase), se muestra el valor guardado.
			$labels[] = $field['options'][ $v ] ?? $v;
		}
		return implode( ', ', $labels );
	}

	/**
	 * Shortcode [mmpadel_formulario].
	 *
	 * @return string
	 */
	public static function shortcode() {
		// Solo se leen claves de un solo uso para recuperar el estado guardado en servidor.
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$ok_key    = isset( $_GET['mmpadel_ok'] ) ? sanitize_key( wp_unslash( $_GET['mmpadel_ok'] ) ) : '';
		$error_key = isset( $_GET['mmpadel_error'] ) ? sanitize_key( wp_unslash( $_GET['mmpadel_error'] ) ) : '';
		// phpcs:enable

		if ( $ok_key ) {
			$ok = get_transient( 'mmpadel_ok_' . $ok_key );
			if ( is_array( $ok ) ) {
				delete_transient( 'mmpadel_ok_' . $ok_key );
				return mmpadel_render_template(
					'form-ok',
					array(
						'nombre'       => $ok['nombre'] ?? '',
						'tipo_clase'   => $ok['tipo_clase'] ?? '',
						'canal_plazas' => mmpadel_get_option( 'canal_plazas_url' ),
					)
				);
			}
		}

		$errors = array();
		$values = array();
		if ( $error_key ) {
			$stored = get_transient( 'mmpadel_error_' . $error_key );
			if ( is_array( $stored ) ) {
				delete_transient( 'mmpadel_error_' . $error_key );
				$errors = $stored['errors'] ?? array();
				$values = $stored['values'] ?? array();
			}
		}

		// Desde una tarifa (?tipo=slug): ese tipo de clase llega ya marcado.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- solo preselecciona una opción.
		$tipo = isset( $_GET['tipo'] ) ? sanitize_title( wp_unslash( $_GET['tipo'] ) ) : '';
		if ( ! $values && $tipo && isset( self::options()['tipo_clase'][ $tipo ] ) ) {
			$values['tipo_clase'] = $tipo;
		}

		return mmpadel_render_template(
			'form',
			array(
				'schema'      => self::schema(),
				'sections'    => self::sections(),
				'values'      => $values,
				'errors'      => $errors,
				'action'      => admin_url( 'admin-post.php' ),
				'volver'      => self::current_url(),
				'timestamp'   => self::sign_time( time() ),
				'privacidad'  => mmpadel_get_page_url( 'privacidad' ),
				'origin_keys' => self::ORIGIN_KEYS,
			)
		);
	}

	/**
	 * Procesa el envío.
	 */
	public static function handle() {
		$volver = self::return_url();

		// 1. Nonce. Si caduca (página en caché mucho tiempo), se avisa sin perder los datos.
		$nonce = isset( $_POST['_mmpadel_nonce'] ) ? sanitize_key( wp_unslash( $_POST['_mmpadel_nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, self::NONCE ) ) {
			self::redirect_with_errors(
				$volver,
				array( '_form' => __( 'El formulario ha caducado. Revisa los datos y vuelve a enviarlo.', 'mmpadel-core' ) ),
				self::collect_values()
			);
		}

		// 2. Antispam silencioso: al bot se le dice que ha ido bien, pero no se guarda nada.
		if ( self::is_spam() ) {
			self::redirect_ok( $volver, array() );
		}

		// 3. Límite de envíos por IP (solo se guarda un hash, nunca la IP).
		if ( self::too_many_requests() ) {
			self::redirect_with_errors(
				$volver,
				array( '_form' => __( 'Hemos recibido varias solicitudes seguidas desde tu conexión. Espera unos minutos o escríbenos por WhatsApp.', 'mmpadel-core' ) ),
				self::collect_values()
			);
		}

		// 4. Validación en servidor.
		$values = self::collect_values();
		$errors = self::validate( $values );
		if ( $errors ) {
			self::redirect_with_errors( $volver, $errors, $values );
		}

		// 5. Guardado y emails.
		$origin = self::collect_origin();
		$id     = self::save( $values, $origin );
		if ( ! $id ) {
			self::redirect_with_errors(
				$volver,
				array( '_form' => __( 'No hemos podido guardar tu solicitud. Inténtalo de nuevo o escríbenos por WhatsApp.', 'mmpadel-core' ) ),
				$values
			);
		}

		MMPadel_Mailer::notify_school( $id, $values, $origin );
		MMPadel_Mailer::autoreply( $values );

		/**
		 * Se ha guardado una solicitud nueva. Punto de enganche para la
		 * integración futura con el software de gestión.
		 *
		 * @param int   $id     ID de la solicitud.
		 * @param array $values Datos del formulario.
		 * @param array $origin Origen (UTM, referrer, página de entrada).
		 */
		do_action( 'mmpadel_solicitud_creada', $id, $values, $origin );

		self::redirect_ok(
			$volver,
			array(
				'nombre'     => $values['nombre'],
				'tipo_clase' => $values['tipo_clase'],
			)
		);
	}

	/**
	 * Lee y sanitiza los valores enviados (sin validar).
	 *
	 * @return array
	 */
	private static function collect_values() {
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- el nonce se comprueba en handle().
		$values = array();

		foreach ( self::schema() as $key => $field ) {
			$raw = isset( $_POST['mmpadel'][ $key ] ) ? wp_unslash( $_POST['mmpadel'][ $key ] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- se sanitiza abajo según el tipo.

			switch ( $field['type'] ) {
				case 'checkbox':
					$raw            = is_array( $raw ) ? array_map( 'sanitize_key', $raw ) : array();
					$values[ $key ] = array_values( array_intersect( array_keys( $field['options'] ), $raw ) );
					break;
				case 'radio':
					$raw            = is_string( $raw ) ? sanitize_key( $raw ) : '';
					$values[ $key ] = array_key_exists( $raw, $field['options'] ) ? (string) $raw : '';
					break;
				case 'email':
					// Si no es un email válido se conserva el texto (sanitizado) para mostrar
					// «Revisa el email» con lo que escribió; validate() no lo dejará pasar.
					$clean          = is_string( $raw ) ? sanitize_email( $raw ) : '';
					$values[ $key ] = '' !== $clean ? $clean : ( is_string( $raw ) ? mb_substr( sanitize_text_field( $raw ), 0, 120 ) : '' );
					break;
				case 'textarea':
					$values[ $key ] = is_string( $raw ) ? mb_substr( sanitize_textarea_field( $raw ), 0, 2000 ) : '';
					break;
				default:
					$values[ $key ] = is_string( $raw ) ? mb_substr( sanitize_text_field( $raw ), 0, 120 ) : '';
			}
		}

		$values['privacidad'] = ! empty( $_POST['mmpadel']['privacidad'] );
		// phpcs:enable

		return $values;
	}

	/**
	 * Valida los valores. Devuelve campo => mensaje.
	 *
	 * @param array $values Valores sanitizados.
	 * @return array<string, string>
	 */
	public static function validate( array $values ) {
		$errors = array();

		foreach ( self::schema() as $key => $field ) {
			$value = $values[ $key ] ?? '';
			if ( $field['required'] && ( '' === $value || array() === $value ) ) {
				$errors[ $key ] = in_array( $field['type'], array( 'radio', 'checkbox' ), true )
					/* translators: %s: nombre del campo. */
					? sprintf( __( 'Elige una opción en «%s».', 'mmpadel-core' ), $field['short'] )
					/* translators: %s: nombre del campo. */
					: sprintf( __( 'Falta tu %s.', 'mmpadel-core' ), mb_strtolower( $field['short'] ) );
			}
		}

		if ( ! isset( $errors['nombre'] ) && mb_strlen( $values['nombre'] ) < 2 ) {
			$errors['nombre'] = __( 'Escribe tu nombre.', 'mmpadel-core' );
		}

		if ( ! isset( $errors['telefono'] ) && ! self::is_valid_phone( $values['telefono'] ) ) {
			$errors['telefono'] = __( 'Revisa el teléfono: debe tener al menos 9 cifras.', 'mmpadel-core' );
		}

		if ( ! isset( $errors['email'] ) && ! is_email( $values['email'] ) ) {
			$errors['email'] = __( 'Revisa el email: no parece válido.', 'mmpadel-core' );
		}

		if ( empty( $values['privacidad'] ) ) {
			$errors['privacidad'] = __( 'Para enviar la solicitud tienes que aceptar la política de privacidad.', 'mmpadel-core' );
		}

		return $errors;
	}

	/**
	 * Teléfono español o internacional: 9 a 15 cifras, «+» opcional.
	 *
	 * @param string $phone Teléfono.
	 * @return bool
	 */
	private static function is_valid_phone( $phone ) {
		$clean = preg_replace( '/[\s.\-()]/', '', $phone );
		return (bool) preg_match( '/^\+?\d{9,15}$/', $clean );
	}

	/**
	 * Honeypot relleno o envío demasiado rápido / sin marca de tiempo válida.
	 *
	 * @return bool
	 */
	private static function is_spam() {
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- el nonce se comprueba antes en handle().
		if ( ! empty( $_POST['mmpadel_web'] ) ) {
			return true;
		}

		$stamp = isset( $_POST['mmpadel_t'] ) ? sanitize_text_field( wp_unslash( $_POST['mmpadel_t'] ) ) : '';
		// phpcs:enable

		$time = self::verify_time( $stamp );
		return ! $time || ( time() - $time ) < self::MIN_SECONDS;
	}

	/**
	 * Marca de tiempo firmada, para que un bot no pueda falsearla.
	 *
	 * @param int $time Marca de tiempo.
	 * @return string
	 */
	private static function sign_time( $time ) {
		return $time . '.' . substr( wp_hash( 'mmpadel_t' . $time, 'nonce' ), 0, 16 );
	}

	/**
	 * Comprueba la marca de tiempo firmada.
	 *
	 * @param string $stamp Marca recibida.
	 * @return int|false Marca de tiempo o false si no es válida.
	 */
	private static function verify_time( $stamp ) {
		$parts = explode( '.', $stamp );
		if ( 2 !== count( $parts ) || ! ctype_digit( $parts[0] ) ) {
			return false;
		}
		return hash_equals( self::sign_time( (int) $parts[0] ), $stamp ) ? (int) $parts[0] : false;
	}

	/**
	 * Cuenta envíos por IP en una ventana de 10 minutos.
	 *
	 * @return bool Si se ha superado el límite.
	 */
	private static function too_many_requests() {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		if ( '' === $ip ) {
			return false;
		}

		$key   = 'mmpadel_rl_' . substr( wp_hash( $ip, 'nonce' ), 0, 20 );
		$count = (int) get_transient( $key );
		if ( $count >= self::MAX_PER_IP ) {
			return true;
		}
		set_transient( $key, $count + 1, 10 * MINUTE_IN_SECONDS );
		return false;
	}

	/**
	 * Origen del interesado (UTM, referrer y página de entrada).
	 *
	 * @return array<string, string>
	 */
	private static function collect_origin() {
		$origin = array();
		foreach ( self::ORIGIN_KEYS as $key ) {
			$raw = isset( $_POST['origen'][ $key ] ) ? wp_unslash( $_POST['origen'][ $key ] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- nonce comprobado en handle(); se sanitiza abajo.
			$raw = is_string( $raw ) ? $raw : '';

			$origin[ $key ] = in_array( $key, array( 'referrer', 'landing' ), true )
				? esc_url_raw( mb_substr( $raw, 0, 500 ), array( 'http', 'https' ) )
				: mb_substr( sanitize_text_field( $raw ), 0, 150 );
		}
		return $origin;
	}

	/**
	 * Guarda la solicitud.
	 *
	 * @param array $values Valores validados.
	 * @param array $origin Origen.
	 * @return int ID o 0 si falla.
	 */
	private static function save( array $values, array $origin ) {
		$id = wp_insert_post(
			array(
				'post_type'   => MMPadel_Post_Types::SOLICITUD,
				'post_status' => 'publish',
				'post_title'  => $values['nombre'],
			),
			true
		);
		if ( is_wp_error( $id ) ) {
			return 0;
		}

		foreach ( self::schema() as $key => $field ) {
			$value = is_array( $values[ $key ] ) ? implode( ',', $values[ $key ] ) : $values[ $key ];
			update_post_meta( $id, self::META_PREFIX . $key, $value );
		}
		foreach ( $origin as $key => $value ) {
			update_post_meta( $id, self::META_PREFIX . 'origen_' . $key, $value );
		}

		// Prueba del consentimiento: cuándo y con qué política.
		update_post_meta( $id, self::META_PREFIX . 'privacidad', current_time( 'mysql' ) );
		update_post_meta( $id, self::META_PREFIX . 'privacidad_url', mmpadel_get_page_url( 'privacidad' ) );
		update_post_meta( $id, self::META_PREFIX . 'estado', 'nueva' );

		return (int) $id;
	}

	/**
	 * URL a la que volver, validada para que sea de este sitio.
	 *
	 * @return string
	 */
	private static function return_url() {
		$url = isset( $_POST['mmpadel_volver'] ) ? esc_url_raw( wp_unslash( $_POST['mmpadel_volver'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- solo se usa para redirigir a una URL del propio sitio.
		$url = wp_validate_redirect( $url, mmpadel_get_page_url( 'encuentra-tu-grupo' ) );
		return remove_query_arg( array( 'mmpadel_ok', 'mmpadel_error' ), $url );
	}

	/**
	 * URL actual de la página del formulario.
	 *
	 * @return string
	 */
	private static function current_url() {
		$url = is_singular() ? get_permalink() : home_url( add_query_arg( array() ) );
		return remove_query_arg( array( 'mmpadel_ok', 'mmpadel_error' ), (string) $url );
	}

	/**
	 * Vuelve al formulario con errores.
	 *
	 * @param string $url    URL del formulario.
	 * @param array  $errors Errores.
	 * @param array  $values Valores para rellenar de nuevo.
	 */
	private static function redirect_with_errors( $url, array $errors, array $values ) {
		$key = strtolower( wp_generate_password( 20, false ) );
		set_transient(
			'mmpadel_error_' . $key,
			array(
				'errors' => $errors,
				'values' => $values,
			),
			15 * MINUTE_IN_SECONDS
		);
		wp_safe_redirect( add_query_arg( 'mmpadel_error', $key, $url ) . '#mmpadel-formulario' );
		exit;
	}

	/**
	 * Vuelve a la página con el aviso de solicitud enviada.
	 *
	 * @param string $url  URL del formulario.
	 * @param array  $data Datos para el aviso.
	 */
	private static function redirect_ok( $url, array $data ) {
		$key = strtolower( wp_generate_password( 20, false ) );
		set_transient( 'mmpadel_ok_' . $key, $data, 30 * MINUTE_IN_SECONDS );
		wp_safe_redirect( add_query_arg( 'mmpadel_ok', $key, $url ) . '#mmpadel-formulario' );
		exit;
	}
}
