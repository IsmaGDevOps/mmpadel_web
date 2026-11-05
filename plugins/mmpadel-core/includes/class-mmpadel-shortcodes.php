<?php
/**
 * Shortcodes para mostrar los contenidos propios dentro de las páginas.
 *
 *   [mmpadel_tarifas tipo="slug"]
 *   [mmpadel_equipo]
 *   [mmpadel_eventos mostrar="proximos|pasados|todos" limite="10"]
 *   [mmpadel_faq categoria="slug"]
 *   [mmpadel_contacto]
 *   [mmpadel_boton destino="grupo|whatsapp|plazas|software|telefono|resenas|instagram|facebook" texto="…" estilo="primario|cta|borde"]
 *   [mmpadel_dato clave="titular"]
 *   [mmpadel_valoracion formato="marcador|linea"]
 *   [mmpadel_opiniones limite="12"]
 *
 * El HTML sale de plugins/mmpadel-core/templates/; el tema puede sustituir
 * cualquier plantilla copiándola a {tema}/mmpadel/.
 *
 * @package MMPadel_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registro y render de shortcodes.
 */
final class MMPadel_Shortcodes {

	/**
	 * Engancha las acciones.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
		add_filter( 'the_content', array( __CLASS__, 'evento_content' ), 20 );
	}

	/**
	 * Registra los shortcodes.
	 */
	public static function register() {
		add_shortcode( 'mmpadel_tarifas', array( __CLASS__, 'tarifas' ) );
		add_shortcode( 'mmpadel_equipo', array( __CLASS__, 'equipo' ) );
		add_shortcode( 'mmpadel_eventos', array( __CLASS__, 'eventos' ) );
		add_shortcode( 'mmpadel_faq', array( __CLASS__, 'faq' ) );
		add_shortcode( 'mmpadel_contacto', array( __CLASS__, 'contacto' ) );
		add_shortcode( 'mmpadel_boton', array( __CLASS__, 'boton' ) );
		add_shortcode( 'mmpadel_dato', array( __CLASS__, 'dato' ) );
		add_shortcode( 'mmpadel_valoracion', array( __CLASS__, 'valoracion' ) );
		add_shortcode( 'mmpadel_opiniones', array( __CLASS__, 'opiniones' ) );
	}

	/**
	 * Opiniones (reseñas de Google copiadas en el panel), en el orden de su
	 * campo «Orden» y, a igualdad, las más recientes primero.
	 *
	 * @param array|string $atts Atributos.
	 * @return string Vacío si no hay opiniones.
	 */
	public static function opiniones( $atts ) {
		$atts = shortcode_atts( array( 'limite' => 12 ), $atts, 'mmpadel_opiniones' );

		$opiniones = array();
		foreach (
			get_posts(
				array(
					'post_type'      => MMPadel_Post_Types::OPINION,
					'post_status'    => 'publish',
					'posts_per_page' => max( 1, min( 50, (int) $atts['limite'] ) ),
					'orderby'        => array(
						'menu_order' => 'ASC',
						'date'       => 'DESC',
					),
					'no_found_rows'  => true,
				)
			) as $post
		) {
			$texto = mmpadel_get_field( 'texto', $post->ID );
			if ( '' === trim( $texto ) ) {
				continue;
			}
			$nota        = (int) mmpadel_get_field( 'nota', $post->ID );
			$opiniones[] = array(
				'autor'     => get_the_title( $post ),
				'iniciales' => self::initials( get_the_title( $post ) ),
				'nota'      => $nota >= 1 && $nota <= 5 ? $nota : 5,
				'texto'     => $texto,
				'detalle'   => mmpadel_get_field( 'detalle', $post->ID ),
				'cuando'    => mmpadel_get_field( 'cuando', $post->ID ),
			);
		}

		if ( ! $opiniones ) {
			return '';
		}

		// Flechas y barra de progreso del carrusel: solo donde hay opiniones.
		wp_enqueue_script(
			'mmpadel-carrusel',
			MMPADEL_CORE_URL . 'assets/js/mmpadel-carrusel.js',
			array(),
			self::asset_version( 'assets/js/mmpadel-carrusel.js' ),
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);

		return mmpadel_render_template( 'opiniones', array( 'opiniones' => $opiniones ) );
	}

	/**
	 * Estilos de los componentes del plugin (listados y formulario).
	 */
	public static function enqueue() {
		wp_enqueue_style(
			'mmpadel-core',
			MMPADEL_CORE_URL . 'assets/css/mmpadel.css',
			array(),
			self::asset_version( 'assets/css/mmpadel.css' )
		);

		// Origen y eventos de dataLayer: en todas las páginas, porque los clics a
		// WhatsApp y teléfono pueden estar en cualquiera.
		wp_enqueue_script(
			'mmpadel-tracking',
			MMPADEL_CORE_URL . 'assets/js/mmpadel-tracking.js',
			array(),
			self::asset_version( 'assets/js/mmpadel-tracking.js' ),
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
	}

	/**
	 * Versión de un recurso: en local, la fecha del archivo (evita la caché).
	 *
	 * @param string $file Ruta relativa al plugin.
	 * @return string
	 */
	public static function asset_version( $file ) {
		return 'local' === wp_get_environment_type() ? (string) filemtime( MMPADEL_CORE_DIR . $file ) : MMPADEL_CORE_VERSION;
	}

	/**
	 * Tarifas agrupadas por tipo de clase.
	 *
	 * @param array|string $atts Atributos.
	 * @return string
	 */
	public static function tarifas( $atts ) {
		$atts = shortcode_atts( array( 'tipo' => '' ), $atts, 'mmpadel_tarifas' );

		$args = array(
			'post_type'      => MMPadel_Post_Types::TARIFA,
			'post_status'    => 'publish',
			'posts_per_page' => 100,
			'orderby'        => array(
				'menu_order' => 'ASC',
				'title'      => 'ASC',
			),
			'no_found_rows'  => true,
		);
		if ( $atts['tipo'] ) {
			$args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- pocas tarifas.
				array(
					'taxonomy' => MMPadel_Post_Types::TAX_TIPO_CLASE,
					'field'    => 'slug',
					'terms'    => sanitize_title( $atts['tipo'] ),
				),
			);
		}

		$formulario = mmpadel_get_page_url( 'encuentra-tu-grupo' );

		$grupos = array();
		foreach ( get_posts( $args ) as $post ) {
			$terms = get_the_terms( $post, MMPadel_Post_Types::TAX_TIPO_CLASE );
			$term  = is_array( $terms ) ? $terms[0] : null;
			$id    = $term ? $term->term_id : 0;

			if ( ! isset( $grupos[ $id ] ) ) {
				$grupos[ $id ] = array(
					'titulo'      => $term ? $term->name : __( 'Otras tarifas', 'mmpadel-core' ),
					'descripcion' => $term ? $term->description : '',
					'ancla'       => 'tarifas-' . ( $term ? $term->slug : 'otras' ),
					// El formulario llega con este tipo de clase ya marcado (ver MMPadel_Form::shortcode()).
					'url'         => $formulario ? add_query_arg( 'tipo', $term ? $term->slug : false, $formulario ) . '#mmpadel-formulario' : '',
					'tarifas'     => array(),
				);
			}

			$precio = mmpadel_get_field( 'precio', $post->ID );
			$tamano = (int) mmpadel_get_field( 'tamano_grupo', $post->ID );

			$grupos[ $id ]['tarifas'][] = array(
				'titulo'    => get_the_title( $post ),
				'tamano'    => self::group_size_label( $tamano ),
				'plazas'    => min( max( $tamano, 0 ), 8 ),
				'precio'    => mmpadel_format_price( $precio ),
				'partes'    => mmpadel_price_parts( $precio ),
				'periodo'   => mmpadel_get_field( 'periodo', $post->ID ),
				'notas'     => mmpadel_get_field( 'notas', $post->ID ),
				'destacada' => 'si' === mmpadel_get_field( 'destacada', $post->ID ),
			);
		}

		// Los grupos salen en el orden de sus fichas (campo «Orden»); los que no tienen categoría, al final.
		$grupos = self::ungrouped_last( $grupos );

		return mmpadel_render_template( 'tarifas', array( 'grupos' => $grupos ) );
	}

	/**
	 * Mueve al final el grupo «sin categoría» (clave 0).
	 *
	 * @param array $grupos Grupos por ID de término.
	 * @return array
	 */
	private static function ungrouped_last( array $grupos ) {
		if ( isset( $grupos[0] ) ) {
			$sin_grupo = $grupos[0];
			unset( $grupos[0] );
			$grupos[0] = $sin_grupo;
		}
		return $grupos;
	}

	/**
	 * Texto del tamaño de grupo.
	 *
	 * @param string $size Alumnos por grupo.
	 * @return string
	 */
	private static function group_size_label( $size ) {
		$size = (int) $size;
		if ( $size < 1 ) {
			return '';
		}
		if ( 1 === $size ) {
			return __( 'Clase individual', 'mmpadel-core' );
		}
		/* translators: %d: alumnos por grupo. */
		return sprintf( __( 'Grupo de %d', 'mmpadel-core' ), $size );
	}

	/**
	 * Fichas de entrenadores.
	 *
	 * @return string
	 */
	public static function equipo() {
		$entrenadores = array();

		$posts = get_posts(
			array(
				'post_type'      => MMPadel_Post_Types::ENTRENADOR,
				'post_status'    => 'publish',
				'posts_per_page' => 50,
				'orderby'        => array(
					'menu_order' => 'ASC',
					'title'      => 'ASC',
				),
				'no_found_rows'  => true,
			)
		);

		foreach ( $posts as $post ) {
			$name           = get_the_title( $post );
			$entrenadores[] = array(
				'nombre'       => $name,
				'foto'         => get_the_post_thumbnail(
					$post,
					'medium_large',
					array(
						'alt'     => $name,
						'loading' => 'lazy',
					)
				),
				'iniciales'    => self::initials( $name ),
				'especialidad' => mmpadel_get_field( 'especialidad', $post->ID ),
				'experiencia'  => mmpadel_get_field( 'experiencia', $post->ID ),
				'enfoque'      => mmpadel_get_field( 'enfoque', $post->ID ),
			);
		}

		return mmpadel_render_template( 'equipo', array( 'entrenadores' => $entrenadores ) );
	}

	/**
	 * Iniciales para la ficha sin foto.
	 *
	 * @param string $name Nombre.
	 * @return string
	 */
	private static function initials( $name ) {
		$words    = preg_split( '/\s+/u', trim( $name ) );
		$initials = '';
		foreach ( array_slice( (array) $words, 0, 2 ) as $word ) {
			$initials .= mb_strtoupper( mb_substr( $word, 0, 1 ) );
		}
		return $initials;
	}

	/**
	 * Listado de pools y eventos.
	 *
	 * @param array|string $atts Atributos.
	 * @return string
	 */
	public static function eventos( $atts ) {
		$atts = shortcode_atts(
			array(
				'mostrar' => 'proximos',
				'limite'  => 10,
			),
			$atts,
			'mmpadel_eventos'
		);

		$mostrar = in_array( $atts['mostrar'], array( 'proximos', 'pasados', 'todos' ), true ) ? $atts['mostrar'] : 'proximos';
		$today   = wp_date( 'Y-m-d' );

		$args = array(
			'post_type'      => MMPadel_Post_Types::EVENTO,
			'post_status'    => 'publish',
			'posts_per_page' => max( 1, min( 50, (int) $atts['limite'] ) ),
			'no_found_rows'  => true,
			'meta_key'       => MMPadel_Fields::meta_key( 'fecha' ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- pocos eventos.
			'orderby'        => 'meta_value',
			'order'          => 'proximos' === $mostrar ? 'ASC' : 'DESC',
		);
		if ( 'todos' !== $mostrar ) {
			$args['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- pocos eventos.
				array(
					'key'     => MMPadel_Fields::meta_key( 'fecha' ),
					'value'   => $today,
					'compare' => 'proximos' === $mostrar ? '>=' : '<',
					'type'    => 'DATE',
				),
			);
		}

		$eventos = array();
		foreach ( get_posts( $args ) as $post ) {
			$eventos[] = self::evento_data( $post, $today );
		}

		return mmpadel_render_template(
			'eventos',
			array(
				'eventos' => $eventos,
				'mostrar' => $mostrar,
			)
		);
	}

	/**
	 * Datos de un evento para las plantillas.
	 *
	 * @param WP_Post $post  Evento.
	 * @param string  $today Fecha de hoy (Y-m-d).
	 * @return array
	 */
	private static function evento_data( $post, $today ) {
		$fecha = mmpadel_get_field( 'fecha', $post->ID );
		$date  = $fecha ? date_create_immutable( $fecha, wp_timezone() ) : false;

		// Días que faltan (negativo si ya pasó), para «Hoy», «Mañana» o «Dentro de N días».
		$dias = null;
		if ( $date ) {
			$diff = date_create_immutable( $today, wp_timezone() )->diff( $date );
			$dias = $diff->invert ? -$diff->days : $diff->days;
		}

		$resultados = mmpadel_get_field( 'resultados', $post->ID );

		return array(
			'titulo'      => get_the_title( $post ),
			'url'         => get_permalink( $post ),
			'resumen'     => has_excerpt( $post ) ? get_the_excerpt( $post ) : '',
			'fecha'       => $fecha,
			'fecha_texto' => $date ? wp_date( get_option( 'date_format' ), $date->getTimestamp() ) : '',
			'dia'         => $date ? wp_date( 'j', $date->getTimestamp() ) : '',
			'mes'         => $date ? wp_date( 'M', $date->getTimestamp() ) : '',
			'semana'      => $date ? wp_date( 'l', $date->getTimestamp() ) : '',
			'dias'        => $dias,
			'hora'        => mmpadel_get_field( 'hora', $post->ID ),
			'nivel'       => mmpadel_get_field( 'nivel', $post->ID ),
			'reglas'      => mmpadel_get_field( 'reglas', $post->ID ),
			'resultados'  => $resultados,
			// Primera línea de los resultados («Campeones: …»), para el listado.
			'destacado'   => trim( explode( "\n", $resultados )[0] ),
			'inscripcion' => mmpadel_get_field( 'inscripcion_url', $post->ID ),
			'terminado'   => $fecha && $fecha < $today,
		);
	}

	/**
	 * Ficha pública del evento: datos antes del contenido; reglas y resultados después.
	 *
	 * @param string $content Contenido ya filtrado.
	 * @return string
	 */
	public static function evento_content( $content ) {
		if ( ! is_singular( MMPadel_Post_Types::EVENTO ) || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}

		return mmpadel_render_template(
			'evento-ficha',
			array(
				'evento'    => self::evento_data( get_post(), wp_date( 'Y-m-d' ) ),
				'contenido' => $content,
			)
		);
	}

	/**
	 * Preguntas frecuentes agrupadas por categoría.
	 *
	 * @param array|string $atts Atributos.
	 * @return string
	 */
	public static function faq( $atts ) {
		$atts = shortcode_atts( array( 'categoria' => '' ), $atts, 'mmpadel_faq' );

		$args = array(
			'post_type'      => MMPadel_Post_Types::FAQ,
			'post_status'    => 'publish',
			'posts_per_page' => 100,
			'orderby'        => array(
				'menu_order' => 'ASC',
				'title'      => 'ASC',
			),
			'no_found_rows'  => true,
		);
		if ( $atts['categoria'] ) {
			$args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- pocas preguntas.
				array(
					'taxonomy' => MMPadel_Post_Types::TAX_FAQ,
					'field'    => 'slug',
					'terms'    => sanitize_title( $atts['categoria'] ),
				),
			);
		}

		$grupos = array();
		foreach ( get_posts( $args ) as $post ) {
			$terms = get_the_terms( $post, MMPadel_Post_Types::TAX_FAQ );
			$term  = is_array( $terms ) ? $terms[0] : null;
			$id    = $term ? $term->term_id : 0;

			if ( ! isset( $grupos[ $id ] ) ) {
				$grupos[ $id ] = array(
					'titulo'    => $term ? $term->name : __( 'Otras preguntas', 'mmpadel-core' ),
					'preguntas' => array(),
				);
			}

			$grupos[ $id ]['preguntas'][] = array(
				'pregunta'  => get_the_title( $post ),
				'respuesta' => wpautop( $post->post_content ),
			);
		}

		// Los grupos salen en el orden de sus fichas (campo «Orden»); los que no tienen categoría, al final.
		$grupos = self::ungrouped_last( $grupos );

		return mmpadel_render_template(
			'faq',
			array(
				'grupos'         => $grupos,
				'con_categorias' => ! $atts['categoria'] && count( $grupos ) > 1,
			)
		);
	}

	/**
	 * Bloque de contacto con los datos de Ajustes MMPadel.
	 *
	 * @return string
	 */
	public static function contacto() {
		$keys = array( 'direccion', 'mapa_url', 'horario', 'telefono', 'whatsapp', 'email_publico' );
		$data = array();
		foreach ( $keys as $key ) {
			$data[ $key ] = mmpadel_get_option( $key );
		}
		return mmpadel_render_template( 'contacto', $data );
	}

	/**
	 * Botón que enlaza a un destino configurado en Ajustes MMPadel.
	 *
	 * Si falta el enlace no se muestra nada; a quien puede editar le aparece
	 * un aviso para que sepa qué rellenar.
	 *
	 * @param array|string $atts Atributos.
	 * @return string
	 */
	public static function boton( $atts ) {
		$atts = shortcode_atts(
			array(
				'destino' => 'grupo',
				'texto'   => '',
				'estilo'  => 'primario',
			),
			$atts,
			'mmpadel_boton'
		);

		$destinos = array(
			'grupo'     => array( mmpadel_get_page_url( 'encuentra-tu-grupo' ), __( 'Encuentra tu grupo', 'mmpadel-core' ), '' ),
			'whatsapp'  => array( mmpadel_get_whatsapp_url(), __( 'Escríbenos por WhatsApp', 'mmpadel-core' ), 'WhatsApp' ),
			'plazas'    => array( mmpadel_get_option( 'canal_plazas_url' ), __( 'Ver plazas libres', 'mmpadel-core' ), __( 'Canal de WhatsApp de plazas libres', 'mmpadel-core' ) ),
			'software'  => array( mmpadel_get_option( 'software_url' ), __( 'Acceso alumnos', 'mmpadel-core' ), __( 'Software de gestión', 'mmpadel-core' ) ),
			'telefono'  => array( mmpadel_get_tel_url( mmpadel_get_option( 'telefono' ) ), __( 'Llámanos', 'mmpadel-core' ), __( 'Teléfono', 'mmpadel-core' ) ),
			'resenas'   => array( mmpadel_get_option( 'google_resenas_url' ), __( 'Ver opiniones en Google', 'mmpadel-core' ), __( 'Ficha de Google (reseñas)', 'mmpadel-core' ) ),
			'instagram' => array( mmpadel_get_option( 'instagram_url' ), __( 'Síguenos en Instagram', 'mmpadel-core' ), 'Instagram' ),
			'facebook'  => array( mmpadel_get_option( 'facebook_url' ), __( 'Síguenos en Facebook', 'mmpadel-core' ), 'Facebook' ),
		);

		if ( ! isset( $destinos[ $atts['destino'] ] ) ) {
			return '';
		}

		list( $url, $default_text, $setting_label ) = $destinos[ $atts['destino'] ];

		if ( '' === $url ) {
			if ( current_user_can( 'edit_posts' ) ) {
				return sprintf(
					'<p class="mmpadel-aviso-editor">%s</p>',
					/* translators: %s: nombre del ajuste. */
					esc_html( sprintf( __( 'Botón oculto: falta «%s» en Ajustes → MMPadel. Este aviso solo lo ven los editores.', 'mmpadel-core' ), $setting_label ) )
				);
			}
			return '';
		}

		$estilos  = array(
			'primario' => 'button',
			'cta'      => 'button button--cta',
			'borde'    => 'button button--outline',
		);
		$class    = $estilos[ $atts['estilo'] ] ?? 'button';
		$external = str_starts_with( $url, 'http' ) && ! str_starts_with( $url, home_url() );

		// Envuelto en un bloque para que respete el ancho del contenido.
		return sprintf(
			'<div class="mmpadel-boton"><a class="%1$s" href="%2$s"%3$s>%4$s</a></div>',
			esc_attr( $class ),
			esc_url( $url, array( 'http', 'https', 'tel' ) ),
			$external ? ' target="_blank" rel="noopener"' : '',
			esc_html( '' !== $atts['texto'] ? $atts['texto'] : $default_text )
		);
	}

	/**
	 * Un dato de Ajustes MMPadel (para textos legales). Si falta, se marca
	 * como pendiente para que se vea al revisar la página.
	 *
	 * @param array|string $atts Atributos.
	 * @return string
	 */
	public static function dato( $atts ) {
		$atts   = shortcode_atts( array( 'clave' => '' ), $atts, 'mmpadel_dato' );
		$fields = MMPadel_Settings::fields();

		// Los ajustes internos del formulario no se pueden mostrar.
		unset( $fields['email_destino'], $fields['autorespuesta_texto'] );

		if ( ! isset( $fields[ $atts['clave'] ] ) ) {
			return '';
		}

		$value = mmpadel_get_option( $atts['clave'] );
		if ( '' === $value ) {
			/* translators: %s: nombre del dato. */
			return '<mark class="mmpadel-pendiente">' . esc_html( sprintf( __( '[Pendiente: %s]', 'mmpadel-core' ), $fields[ $atts['clave'] ]['label'] ) ) . '</mark>';
		}

		return nl2br( esc_html( $value ) );
	}

	/**
	 * Nota y número de reseñas de Google (Ajustes MMPadel): como marcador o,
	 * con formato="linea", en una línea discreta (p. ej. en la portada).
	 * Sin nota no se muestra; a quien puede editar le aparece un aviso.
	 *
	 * @param array|string $atts Atributos.
	 * @return string
	 */
	public static function valoracion( $atts ) {
		$atts = shortcode_atts( array( 'formato' => 'marcador' ), $atts, 'mmpadel_valoracion' );
		$nota = mmpadel_get_option( 'google_nota' );
		if ( '' === $nota ) {
			if ( current_user_can( 'edit_posts' ) ) {
				return '<p class="mmpadel-aviso-editor">' . esc_html__( 'Marcador oculto: falta «Nota media en Google» en Ajustes → MMPadel. Este aviso solo lo ven los editores.', 'mmpadel-core' ) . '</p>';
			}
			return '';
		}

		return mmpadel_render_template(
			'valoracion',
			array(
				'nota'    => $nota,
				'resenas' => mmpadel_get_option( 'google_num_resenas' ),
				'formato' => $atts['formato'],
			)
		);
	}
}
