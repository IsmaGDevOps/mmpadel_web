<?php
/**
 * Página «Ajustes → MMPadel»: contacto, enlaces, formulario y datos legales.
 *
 * Todo se guarda en una sola opción (mmpadel_ajustes). El tema y el plugin la
 * leen con mmpadel_get_option().
 *
 * @package MMPadel_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Ajustes generales de la web.
 */
final class MMPadel_Settings {

	const OPTION = 'mmpadel_ajustes';
	const PAGE   = 'mmpadel-ajustes';

	/**
	 * Engancha las acciones.
	 */
	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
		add_action( 'admin_menu', array( __CLASS__, 'add_page' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( MMPADEL_CORE_FILE ), array( __CLASS__, 'action_links' ) );
	}

	/**
	 * Secciones y campos de la página.
	 *
	 * @return array
	 */
	public static function sections() {
		return array(
			'contacto'   => array(
				'title'  => __( 'Contacto', 'mmpadel-core' ),
				'intro'  => __( 'Se muestran en el pie de todas las páginas y en Contacto.', 'mmpadel-core' ),
				'fields' => array(
					'telefono'      => array(
						'label'       => __( 'Teléfono', 'mmpadel-core' ),
						'type'        => 'tel',
						'placeholder' => '600 000 000',
					),
					'whatsapp'      => array(
						'label'       => __( 'WhatsApp', 'mmpadel-core' ),
						'type'        => 'tel',
						'placeholder' => '600 000 000',
						'description' => __( 'Número para consultas. Si no es español, con prefijo internacional (+351…).', 'mmpadel-core' ),
					),
					'email_publico' => array(
						'label' => __( 'Email de contacto', 'mmpadel-core' ),
						'type'  => 'email',
					),
					'direccion'     => array(
						'label'       => __( 'Dirección', 'mmpadel-core' ),
						'placeholder' => __( 'Club, calle y número, Málaga', 'mmpadel-core' ),
					),
					'mapa_url'      => array(
						'label'       => __( 'Enlace al mapa', 'mmpadel-core' ),
						'type'        => 'url',
						'description' => __( 'Enlace de Google Maps a la ficha del club.', 'mmpadel-core' ),
					),
					'horario'       => array(
						'label'       => __( 'Horario', 'mmpadel-core' ),
						'type'        => 'textarea',
						'placeholder' => __( "Lunes a viernes: 9:00–22:00\nSábados: 9:00–14:00", 'mmpadel-core' ),
					),
				),
			),
			'enlaces'    => array(
				'title'  => __( 'Enlaces', 'mmpadel-core' ),
				'intro'  => __( 'Si un enlace está vacío, el botón correspondiente no se muestra.', 'mmpadel-core' ),
				'fields' => array(
					'canal_plazas_url'   => array(
						'label'       => __( 'Canal de WhatsApp de plazas libres', 'mmpadel-core' ),
						'type'        => 'url',
						'placeholder' => 'https://whatsapp.com/channel/…',
					),
					'software_url'       => array(
						'label'       => __( 'Software de gestión (acceso alumnos)', 'mmpadel-core' ),
						'type'        => 'url',
						'description' => __( 'Pendiente de elegir el software. Vacío = no se muestra.', 'mmpadel-core' ),
					),
					'instagram_url'      => array(
						'label' => __( 'Instagram', 'mmpadel-core' ),
						'type'  => 'url',
					),
					'google_resenas_url' => array(
						'label'       => __( 'Ficha de Google (reseñas)', 'mmpadel-core' ),
						'type'        => 'url',
						'description' => __( 'Enlace a las reseñas del Perfil de Empresa de Google.', 'mmpadel-core' ),
					),
				),
			),
			'formulario' => array(
				'title'  => __( 'Formulario «Encuentra tu grupo»', 'mmpadel-core' ),
				'intro'  => '',
				'fields' => array(
					'email_destino'       => array(
						'label'       => __( 'Email que recibe las solicitudes', 'mmpadel-core' ),
						'type'        => 'email',
						/* translators: %s: email del administrador. */
						'description' => sprintf( __( 'Si se deja vacío: %s', 'mmpadel-core' ), get_option( 'admin_email' ) ),
					),
					'autorespuesta_texto' => array(
						'label'       => __( 'Texto de la respuesta automática', 'mmpadel-core' ),
						'type'        => 'textarea',
						'description' => __( 'Lo que recibe el interesado al enviar el formulario, tras el saludo. Vacío = texto por defecto. El enlace al canal de plazas libres se añade solo.', 'mmpadel-core' ),
					),
				),
			),
			'legal'      => array(
				'title'  => __( 'Datos legales', 'mmpadel-core' ),
				'intro'  => __( 'Se usan en Aviso legal y Privacidad mediante los shortcodes [mmpadel_dato clave="titular"], etc.', 'mmpadel-core' ),
				'fields' => array(
					'titular'          => array(
						'label'       => __( 'Titular (razón social o nombre)', 'mmpadel-core' ),
						'placeholder' => __( 'MMPadel S.L.', 'mmpadel-core' ),
					),
					'nif'              => array(
						'label' => __( 'NIF / CIF', 'mmpadel-core' ),
					),
					'domicilio_fiscal' => array(
						'label' => __( 'Domicilio social', 'mmpadel-core' ),
					),
				),
			),
		);
	}

	/**
	 * Todos los campos en una lista plana (clave => declaración).
	 *
	 * @return array
	 */
	public static function fields() {
		$fields = array();
		foreach ( self::sections() as $section ) {
			$fields += $section['fields'];
		}
		return $fields;
	}

	/**
	 * Registra la opción, las secciones y los campos.
	 */
	public static function register() {
		register_setting(
			'mmpadel_ajustes_group',
			self::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => array(),
			)
		);

		foreach ( self::sections() as $section_id => $section ) {
			add_settings_section(
				'mmpadel_' . $section_id,
				$section['title'],
				static function () use ( $section ) {
					if ( $section['intro'] ) {
						echo '<p>' . esc_html( $section['intro'] ) . '</p>';
					}
				},
				self::PAGE
			);

			foreach ( $section['fields'] as $key => $field ) {
				add_settings_field(
					'mmpadel_' . $key,
					$field['label'],
					array( __CLASS__, 'render_field' ),
					self::PAGE,
					'mmpadel_' . $section_id,
					array(
						'key'       => $key,
						'field'     => $field,
						'label_for' => 'mmpadel-' . $key,
					)
				);
			}
		}
	}

	/**
	 * Sanitiza la opción completa. Solo se guardan claves conocidas.
	 *
	 * @param mixed $input Valores enviados.
	 * @return array
	 */
	public static function sanitize( $input ) {
		$input  = is_array( $input ) ? $input : array();
		$output = array();

		foreach ( self::fields() as $key => $field ) {
			$value = isset( $input[ $key ] ) ? (string) $input[ $key ] : '';

			switch ( $field['type'] ?? 'text' ) {
				case 'email':
					$clean = sanitize_email( $value );
					if ( '' !== trim( $value ) && '' === $clean ) {
						add_settings_error(
							self::OPTION,
							'mmpadel_' . $key,
							/* translators: %s: nombre del campo. */
							sprintf( __( '«%s» no es un email válido.', 'mmpadel-core' ), $field['label'] )
						);
					}
					$output[ $key ] = $clean;
					break;
				case 'url':
					$output[ $key ] = esc_url_raw( trim( $value ), array( 'http', 'https' ) );
					break;
				case 'textarea':
					$output[ $key ] = sanitize_textarea_field( $value );
					break;
				default:
					$output[ $key ] = sanitize_text_field( $value );
			}
		}

		return $output;
	}

	/**
	 * Pinta un campo.
	 *
	 * @param array $args Argumentos de add_settings_field.
	 */
	public static function render_field( $args ) {
		$key   = $args['key'];
		$field = $args['field'];
		$id    = 'mmpadel-' . $key;
		$name  = self::OPTION . '[' . $key . ']';
		$value = mmpadel_get_option( $key );
		$type  = $field['type'] ?? 'text';
		$ph    = $field['placeholder'] ?? '';

		// El destino de solicitudes muestra vacío si no se ha cambiado (usa el del administrador).
		if ( 'email_destino' === $key ) {
			$options = get_option( self::OPTION, array() );
			$value   = $options['email_destino'] ?? '';
		}

		if ( 'textarea' === $type ) {
			printf(
				'<textarea id="%1$s" name="%2$s" rows="4" class="large-text" placeholder="%3$s">%4$s</textarea>',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( $ph ),
				esc_textarea( $value )
			);
		} else {
			printf(
				'<input type="%1$s" id="%2$s" name="%3$s" value="%4$s" class="regular-text" placeholder="%5$s">',
				esc_attr( $type ),
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( $value ),
				esc_attr( $ph )
			);
		}

		if ( ! empty( $field['description'] ) ) {
			echo '<p class="description">' . esc_html( $field['description'] ) . '</p>';
		}
	}

	/**
	 * Añade la página en Ajustes.
	 */
	public static function add_page() {
		add_options_page(
			__( 'Ajustes MMPadel', 'mmpadel-core' ),
			__( 'MMPadel', 'mmpadel-core' ),
			'manage_options',
			self::PAGE,
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Pinta la página.
	 */
	public static function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'mmpadel_ajustes_group' );
				do_settings_sections( self::PAGE );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Enlace «Ajustes» en la lista de plugins.
	 *
	 * @param array $links Enlaces.
	 * @return array
	 */
	public static function action_links( $links ) {
		array_unshift(
			$links,
			sprintf( '<a href="%1$s">%2$s</a>', esc_url( admin_url( 'options-general.php?page=' . self::PAGE ) ), esc_html__( 'Ajustes', 'mmpadel-core' ) )
		);
		return $links;
	}
}
