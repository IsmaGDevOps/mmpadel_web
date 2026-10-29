<?php
/**
 * Tipos de contenido, taxonomías y sus campos.
 *
 * @package MMPadel_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registro de tarifas, entrenadores, eventos, FAQ y solicitudes.
 */
final class MMPadel_Post_Types {

	const TARIFA     = 'mmpadel_tarifa';
	const ENTRENADOR = 'mmpadel_entrenador';
	const EVENTO     = 'mmpadel_evento';
	const FAQ        = 'mmpadel_faq';
	const SOLICITUD  = 'mmpadel_solicitud';

	const TAX_TIPO_CLASE = 'mmpadel_tipo_clase';
	const TAX_FAQ        = 'mmpadel_faq_categoria';

	/**
	 * Versión de los permisos. Si cambia, se vuelven a asignar al administrador.
	 */
	const CAPS_VERSION = '1';

	/**
	 * Engancha las acciones.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
		add_action( 'admin_init', array( __CLASS__, 'maybe_add_caps' ) );
		add_filter( 'use_block_editor_for_post_type', array( __CLASS__, 'use_block_editor' ), 10, 2 );
		add_filter( 'enter_title_here', array( __CLASS__, 'title_placeholder' ), 10, 2 );
		add_filter( 'admin_post_thumbnail_html', array( __CLASS__, 'thumbnail_help' ), 10, 2 );
	}

	/**
	 * Activación del plugin: registra todo, da permisos y regenera los enlaces.
	 */
	public static function activate() {
		self::register();
		self::add_caps();
		flush_rewrite_rules();
	}

	/**
	 * Registra tipos de contenido, taxonomías y campos.
	 */
	public static function register() {
		self::register_tarifas();
		self::register_entrenadores();
		self::register_eventos();
		self::register_faq();
		self::register_solicitudes();
	}

	/**
	 * Tarifas: tipo de clase, tamaño de grupo, precio final y notas.
	 */
	private static function register_tarifas() {
		register_post_type(
			self::TARIFA,
			array(
				'labels'        => self::labels( __( 'Tarifas', 'mmpadel-core' ), __( 'Tarifa', 'mmpadel-core' ), true ),
				'public'        => false,
				'show_ui'       => true,
				'show_in_rest'  => true,
				'menu_position' => 21,
				'menu_icon'     => 'dashicons-tickets-alt',
				'supports'      => array( 'title', 'page-attributes' ),
				'rewrite'       => false,
			)
		);

		register_taxonomy(
			self::TAX_TIPO_CLASE,
			self::TARIFA,
			array(
				'labels'            => array(
					'name'          => __( 'Tipos de clase', 'mmpadel-core' ),
					'singular_name' => __( 'Tipo de clase', 'mmpadel-core' ),
					'menu_name'     => __( 'Tipos de clase', 'mmpadel-core' ),
					'all_items'     => __( 'Todos los tipos', 'mmpadel-core' ),
					'edit_item'     => __( 'Editar tipo de clase', 'mmpadel-core' ),
					'add_new_item'  => __( 'Añadir tipo de clase', 'mmpadel-core' ),
					'not_found'     => __( 'No hay tipos de clase.', 'mmpadel-core' ),
				),
				'description'       => __( 'Se usan para agrupar las tarifas y como opciones del formulario «Encuentra tu grupo».', 'mmpadel-core' ),
				'public'            => false,
				'show_ui'           => true,
				'show_in_rest'      => true,
				'show_admin_column' => true,
				'hierarchical'      => true,
				'rewrite'           => false,
			)
		);

		MMPadel_Fields::register(
			self::TARIFA,
			__( 'Datos de la tarifa', 'mmpadel-core' ),
			array(
				'tamano_grupo' => array(
					'label'       => __( 'Tamaño de grupo', 'mmpadel-core' ),
					'type'        => 'number',
					'description' => __( 'Alumnos por grupo. 1 = clase individual.', 'mmpadel-core' ),
					'column'      => true,
				),
				'precio'       => array(
					'label'       => __( 'Precio final (€)', 'mmpadel-core' ),
					'type'        => 'price',
					'description' => __( 'Precio final con todo incluido. Ej.: 45 o 45,50.', 'mmpadel-core' ),
					'column'      => true,
				),
				'periodo'      => array(
					'label'       => __( 'Periodo', 'mmpadel-core' ),
					'description' => __( 'A qué corresponde el precio.', 'mmpadel-core' ),
					'default'     => __( 'por ciclo de 4 semanas', 'mmpadel-core' ),
					'column'      => true,
				),
				'notas'        => array(
					'label'       => __( 'Notas', 'mmpadel-core' ),
					'type'        => 'textarea',
					'description' => __( 'Aclaraciones: matrícula gratuita, pista incluida, horario, etc.', 'mmpadel-core' ),
				),
			)
		);
	}

	/**
	 * Entrenadores: foto (imagen destacada), experiencia, especialidad y enfoque.
	 */
	private static function register_entrenadores() {
		register_post_type(
			self::ENTRENADOR,
			array(
				'labels'        => array_merge(
					self::labels( __( 'Entrenadores', 'mmpadel-core' ), __( 'Entrenador', 'mmpadel-core' ), false ),
					array(
						'featured_image'        => __( 'Foto', 'mmpadel-core' ),
						'set_featured_image'    => __( 'Elegir foto', 'mmpadel-core' ),
						'remove_featured_image' => __( 'Quitar foto', 'mmpadel-core' ),
						'use_featured_image'    => __( 'Usar como foto', 'mmpadel-core' ),
					)
				),
				'public'        => false,
				'show_ui'       => true,
				'show_in_rest'  => true,
				'menu_position' => 22,
				'menu_icon'     => 'dashicons-groups',
				'supports'      => array( 'title', 'thumbnail', 'page-attributes' ),
				'rewrite'       => false,
			)
		);

		MMPadel_Fields::register(
			self::ENTRENADOR,
			__( 'Ficha del entrenador', 'mmpadel-core' ),
			array(
				'especialidad' => array(
					'label'       => __( 'Especialidad', 'mmpadel-core' ),
					'placeholder' => __( 'Ej.: iniciación y menores', 'mmpadel-core' ),
					'column'      => true,
				),
				'experiencia'  => array(
					'label'       => __( 'Experiencia', 'mmpadel-core' ),
					'type'        => 'textarea',
					'description' => __( 'Años entrenando, titulaciones, competición…', 'mmpadel-core' ),
				),
				'enfoque'      => array(
					'label'       => __( 'Enfoque', 'mmpadel-core' ),
					'type'        => 'textarea',
					'description' => __( 'Cómo enseña: en qué se fija, cómo trabaja con el grupo.', 'mmpadel-core' ),
				),
			)
		);
	}

	/**
	 * Pools y eventos: fecha, nivel, reglas, enlace de inscripción y resultados.
	 * Tienen ficha pública en /eventos/{slug}/.
	 */
	private static function register_eventos() {
		register_post_type(
			self::EVENTO,
			array(
				'labels'        => self::labels( __( 'Pools y eventos', 'mmpadel-core' ), __( 'Evento', 'mmpadel-core' ), false ),
				'public'        => true,
				'show_in_rest'  => true,
				'has_archive'   => false,
				'menu_position' => 23,
				'menu_icon'     => 'dashicons-awards',
				'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
				'rewrite'       => array(
					'slug'       => 'eventos',
					'with_front' => false,
				),
			)
		);

		MMPadel_Fields::register(
			self::EVENTO,
			__( 'Datos del evento', 'mmpadel-core' ),
			array(
				'fecha'           => array(
					'label'  => __( 'Fecha', 'mmpadel-core' ),
					'type'   => 'date',
					'column' => true,
				),
				'hora'            => array(
					'label' => __( 'Hora de inicio', 'mmpadel-core' ),
					'type'  => 'time',
				),
				'nivel'           => array(
					'label'       => __( 'Nivel', 'mmpadel-core' ),
					'placeholder' => __( 'Ej.: intermedio (3–4)', 'mmpadel-core' ),
					'column'      => true,
				),
				'reglas'          => array(
					'label'       => __( 'Reglas', 'mmpadel-core' ),
					'type'        => 'textarea',
					'description' => __( 'Formato, duración, cómo se forman las parejas… Una regla por línea.', 'mmpadel-core' ),
				),
				'inscripcion_url' => array(
					'label'       => __( 'Enlace de inscripción', 'mmpadel-core' ),
					'type'        => 'url',
					'description' => __( 'Formulario, software de gestión o WhatsApp. Vacío = sin botón de inscripción.', 'mmpadel-core' ),
				),
				'resultados'      => array(
					'label'       => __( 'Resultados', 'mmpadel-core' ),
					'type'        => 'textarea',
					'description' => __( 'Se muestran en la ficha cuando el evento ha terminado.', 'mmpadel-core' ),
				),
			)
		);
	}

	/**
	 * Preguntas frecuentes: pregunta (título), respuesta (contenido) y categoría.
	 */
	private static function register_faq() {
		register_post_type(
			self::FAQ,
			array(
				'labels'        => self::labels( __( 'Preguntas frecuentes', 'mmpadel-core' ), __( 'Pregunta', 'mmpadel-core' ), true ),
				'public'        => false,
				'show_ui'       => true,
				'show_in_rest'  => true,
				'menu_position' => 24,
				'menu_icon'     => 'dashicons-editor-help',
				'supports'      => array( 'title', 'editor', 'page-attributes' ),
				'rewrite'       => false,
			)
		);

		register_taxonomy(
			self::TAX_FAQ,
			self::FAQ,
			array(
				'labels'            => array(
					'name'          => __( 'Categorías de FAQ', 'mmpadel-core' ),
					'singular_name' => __( 'Categoría', 'mmpadel-core' ),
					'menu_name'     => __( 'Categorías', 'mmpadel-core' ),
					'all_items'     => __( 'Todas las categorías', 'mmpadel-core' ),
					'edit_item'     => __( 'Editar categoría', 'mmpadel-core' ),
					'add_new_item'  => __( 'Añadir categoría', 'mmpadel-core' ),
					'not_found'     => __( 'No hay categorías.', 'mmpadel-core' ),
				),
				'public'            => false,
				'show_ui'           => true,
				'show_in_rest'      => true,
				'show_admin_column' => true,
				'hierarchical'      => true,
				'rewrite'           => false,
			)
		);
	}

	/**
	 * Solicitudes del formulario. Privadas: no tienen URL pública, no salen en
	 * la API REST y solo las ve quien tenga los permisos propios (por defecto,
	 * el administrador). No se pueden crear desde el panel.
	 */
	private static function register_solicitudes() {
		register_post_type(
			self::SOLICITUD,
			array(
				'labels'              => self::labels( __( 'Solicitudes', 'mmpadel-core' ), __( 'Solicitud', 'mmpadel-core' ), true ),
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_rest'        => false,
				'show_in_nav_menus'   => false,
				'exclude_from_search' => true,
				'publicly_queryable'  => false,
				'query_var'           => false,
				'rewrite'             => false,
				'menu_position'       => 3,
				'menu_icon'           => 'dashicons-email-alt',
				'supports'            => false, // Sin título ni editor: los datos del formulario no se editan.
				'capability_type'     => array( 'mmpadel_solicitud', 'mmpadel_solicitudes' ),
				'map_meta_cap'        => true,
				'capabilities'        => array(
					'create_posts' => 'do_not_allow',
				),
			)
		);
	}

	/**
	 * Permisos primitivos de las solicitudes.
	 *
	 * @return string[]
	 */
	public static function solicitud_caps() {
		$object = get_post_type_object( self::SOLICITUD );
		if ( ! $object ) {
			return array();
		}

		// Los permisos «meta» (edit_post, read_post, delete_post) se resuelven con map_meta_cap.
		$caps = (array) $object->cap;
		unset( $caps['edit_post'], $caps['read_post'], $caps['delete_post'], $caps['create_posts'], $caps['read'] );

		return array_values( array_unique( $caps ) );
	}

	/**
	 * Da al administrador los permisos de las solicitudes.
	 */
	public static function add_caps() {
		$role = get_role( 'administrator' );
		if ( ! $role ) {
			return;
		}
		foreach ( self::solicitud_caps() as $cap ) {
			$role->add_cap( $cap );
		}
		update_option( 'mmpadel_caps_version', self::CAPS_VERSION, false );
	}

	/**
	 * Vuelve a asignar permisos si han cambiado (o si el plugin se activó sin
	 * pasar por el hook de activación, p. ej. al copiarlo por FTP).
	 */
	public static function maybe_add_caps() {
		if ( get_option( 'mmpadel_caps_version' ) !== self::CAPS_VERSION ) {
			self::add_caps();
		}
	}

	/**
	 * FAQ con el editor clásico: una respuesta corta no necesita bloques.
	 *
	 * @param bool   $use_block_editor Usar el editor de bloques.
	 * @param string $post_type        Tipo de contenido.
	 * @return bool
	 */
	public static function use_block_editor( $use_block_editor, $post_type ) {
		return self::FAQ === $post_type ? false : $use_block_editor;
	}

	/**
	 * Texto de ayuda del título según el tipo de contenido.
	 *
	 * @param string  $text Texto por defecto.
	 * @param WP_Post $post Contenido.
	 * @return string
	 */
	public static function title_placeholder( $text, $post ) {
		$placeholders = array(
			self::TARIFA     => __( 'Nombre de la tarifa (p. ej. «Grupo de 4 – 1 día por semana»)', 'mmpadel-core' ),
			self::ENTRENADOR => __( 'Nombre del entrenador', 'mmpadel-core' ),
			self::EVENTO     => __( 'Nombre del pool o evento', 'mmpadel-core' ),
			self::FAQ        => __( 'Escribe la pregunta', 'mmpadel-core' ),
		);
		return $placeholders[ $post->post_type ] ?? $text;
	}

	/**
	 * Aclara que la imagen destacada del entrenador es su foto.
	 *
	 * @param string $html    HTML de la caja.
	 * @param int    $post_id ID.
	 * @return string
	 */
	public static function thumbnail_help( $html, $post_id ) {
		if ( self::ENTRENADOR === get_post_type( $post_id ) ) {
			$html .= '<p class="description">' . esc_html__( 'Foto del entrenador. Mejor vertical o cuadrada, mínimo 600 px de ancho.', 'mmpadel-core' ) . '</p>';
		}
		return $html;
	}

	/**
	 * Etiquetas de un tipo de contenido.
	 *
	 * @param string $plural   Nombre en plural.
	 * @param string $singular Nombre en singular.
	 * @param bool   $feminine Si el nombre es femenino («nueva» en vez de «nuevo»).
	 * @return array
	 */
	private static function labels( $plural, $singular, $feminine ) {
		$lower = mb_strtolower( $singular );

		return array(
			'name'               => $plural,
			'singular_name'      => $singular,
			'menu_name'          => $plural,
			'all_items'          => $plural,
			/* translators: %s: nombre en singular, en minúsculas. */
			'add_new_item'       => sprintf( $feminine ? __( 'Añadir nueva %s', 'mmpadel-core' ) : __( 'Añadir nuevo %s', 'mmpadel-core' ), $lower ),
			'add_new'            => $feminine ? __( 'Añadir nueva', 'mmpadel-core' ) : __( 'Añadir nuevo', 'mmpadel-core' ),
			/* translators: %s: nombre en singular, en minúsculas. */
			'edit_item'          => sprintf( __( 'Editar %s', 'mmpadel-core' ), $lower ),
			/* translators: %s: nombre en singular, en minúsculas. */
			'view_item'          => sprintf( __( 'Ver %s', 'mmpadel-core' ), $lower ),
			/* translators: %s: nombre en plural, en minúsculas. */
			'search_items'       => sprintf( __( 'Buscar %s', 'mmpadel-core' ), mb_strtolower( $plural ) ),
			/* translators: %s: nombre en plural, en minúsculas. */
			'not_found'          => sprintf( __( 'No hay %s.', 'mmpadel-core' ), mb_strtolower( $plural ) ),
			/* translators: %s: nombre en plural, en minúsculas. */
			'not_found_in_trash' => sprintf( __( 'No hay %s en la papelera.', 'mmpadel-core' ), mb_strtolower( $plural ) ),
		);
	}
}
