<?php
/**
 * Comandos WP-CLI: wp mmpadel paginas | demo.
 *
 * @package MMPadel_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Prepara la estructura de la web y el contenido de ejemplo.
 */
final class MMPadel_CLI {

	/**
	 * Marca del contenido de ejemplo, para poder borrarlo después.
	 */
	const DEMO_META = '_mmpadel_demo';

	/**
	 * Crea las páginas, los menús, la portada y la página del blog.
	 *
	 * Se puede repetir sin duplicar nada: las páginas que ya existen (por su
	 * slug) se dejan como están, salvo que se use --forzar. Sirve también en
	 * producción.
	 *
	 * ## OPTIONS
	 *
	 * [--forzar]
	 * : Sustituye el contenido de las páginas existentes por el de las plantillas.
	 *
	 * ## EXAMPLES
	 *
	 *     wp mmpadel paginas
	 *     wp mmpadel paginas --forzar
	 *
	 * @param array $args       Argumentos posicionales.
	 * @param array $assoc_args Opciones.
	 */
	public function paginas( $args, $assoc_args ) {
		$forzar = (bool) \WP_CLI\Utils\get_flag_value( $assoc_args, 'forzar', false );
		$ids    = array();
		$order  = 0;

		// El contenido sale de archivos del propio plugin: no hace falta pasarlo por kses.
		kses_remove_filters();

		foreach ( self::pages() as $slug => $title ) {
			$content  = self::page_content( $slug );
			$existing = get_page_by_path( $slug, OBJECT, 'page' );
			++$order;

			if ( $existing ) {
				$ids[ $slug ] = $existing->ID;
				if ( $forzar ) {
					wp_update_post(
						array(
							'ID'           => $existing->ID,
							'post_content' => $content,
						)
					);
					WP_CLI::log( "Actualizada: {$title}" );
				} else {
					WP_CLI::log( "Ya existe:   {$title}" );
				}
				continue;
			}

			$id = wp_insert_post(
				array(
					'post_type'    => 'page',
					'post_status'  => 'publish',
					'post_title'   => $title,
					'post_name'    => $slug,
					'post_content' => $content,
					'menu_order'   => $order,
				),
				true
			);
			if ( is_wp_error( $id ) ) {
				WP_CLI::error( "No se pudo crear «{$title}»: " . $id->get_error_message() );
			}
			$ids[ $slug ] = $id;
			WP_CLI::log( "Creada:      {$title}" );
		}

		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $ids['inicio'] );
		update_option( 'page_for_posts', $ids['pildoras'] );
		update_option( 'wp_page_for_privacy_policy', $ids['privacidad'] );

		// Formatos de fecha y hora en español si siguen los de WordPress en inglés.
		if ( 'F j, Y' === get_option( 'date_format' ) ) {
			update_option( 'date_format', 'j \d\e F \d\e Y' );
		}
		if ( 'g:i a' === get_option( 'time_format' ) ) {
			update_option( 'time_format', 'H:i' );
		}
		update_option( 'start_of_week', 1 );

		self::menu(
			__( 'Menú principal', 'mmpadel-core' ),
			'principal',
			array(
				array( 'metodologia', __( 'Metodología', 'mmpadel-core' ) ),
				array( 'clases-y-tarifas', __( 'Clases y tarifas', 'mmpadel-core' ) ),
				array( 'plazas-libres', __( 'Plazas libres', 'mmpadel-core' ) ),
				array( 'pools-y-eventos', __( 'Pools y eventos', 'mmpadel-core' ) ),
				array( 'equipo', __( 'Equipo', 'mmpadel-core' ) ),
				array( 'pildoras', __( 'Píldoras', 'mmpadel-core' ) ),
				array( 'opiniones', __( 'Opiniones', 'mmpadel-core' ) ),
				array( 'contacto', __( 'Contacto', 'mmpadel-core' ) ),
				array( 'encuentra-tu-grupo', __( 'Encuentra tu grupo', 'mmpadel-core' ), 'menu-cta' ),
			),
			$ids
		);

		self::menu(
			__( 'Menú legal', 'mmpadel-core' ),
			'legal',
			array(
				array( 'aviso-legal', __( 'Aviso legal', 'mmpadel-core' ) ),
				array( 'privacidad', __( 'Privacidad', 'mmpadel-core' ) ),
				array( 'cookies', __( 'Cookies', 'mmpadel-core' ) ),
			),
			$ids
		);

		WP_CLI::success( 'Páginas y menús listos.' );
	}

	/**
	 * Crea contenido de ejemplo: ajustes, tarifas, entrenadores, eventos,
	 * preguntas frecuentes y píldoras. Solo para desarrollo.
	 *
	 * Todo lo que crea queda marcado y se puede borrar con --borrar. Los
	 * ajustes solo se rellenan si están vacíos.
	 *
	 * ## OPTIONS
	 *
	 * [--borrar]
	 * : Borra el contenido de ejemplo creado antes.
	 *
	 * [--yes]
	 * : No pedir confirmación fuera del entorno local.
	 *
	 * ## EXAMPLES
	 *
	 *     wp mmpadel demo
	 *     wp mmpadel demo --borrar
	 *
	 * @param array $args       Argumentos posicionales.
	 * @param array $assoc_args Opciones.
	 */
	public function demo( $args, $assoc_args ) {
		if ( 'local' !== wp_get_environment_type() ) {
			WP_CLI::confirm( 'Este no es el entorno local. ¿Seguro que quieres tocar el contenido de ejemplo aquí?', $assoc_args );
		}

		if ( \WP_CLI\Utils\get_flag_value( $assoc_args, 'borrar', false ) ) {
			self::delete_demo();
			return;
		}

		self::demo_settings();
		self::delete_wordpress_samples();

		$tipos = array(
			'grupo'      => self::term( MMPadel_Post_Types::TAX_TIPO_CLASE, 'Grupo adultos', 'Grupos reducidos por nivel, con el mismo entrenador todo el ciclo.' ),
			'particular' => self::term( MMPadel_Post_Types::TAX_TIPO_CLASE, 'Clase particular', 'Sesiones individuales o en pareja para trabajar algo concreto.' ),
			'menores'    => self::term( MMPadel_Post_Types::TAX_TIPO_CLASE, 'Menores', 'Grupos para niños y adolescentes, por edad y nivel.' ),
		);

		$tarifas = array(
			array( 'Grupo de 4 · 1 día por semana', 'grupo', 4, '55', 'por ciclo de 4 semanas', 'Precio de ejemplo. Sesiones de 1 hora.' ),
			array( 'Grupo de 4 · 2 días por semana', 'grupo', 4, '99', 'por ciclo de 4 semanas', 'Precio de ejemplo. Sesiones de 1 hora.' ),
			array( 'Grupo de 3 · 1 día por semana', 'grupo', 3, '69', 'por ciclo de 4 semanas', 'Precio de ejemplo.' ),
			array( 'Clase individual', 'particular', 1, '35', 'por sesión de 1 hora', 'Precio de ejemplo.' ),
			array( 'Clase en pareja', 'particular', 2, '22,50', 'por persona y sesión', 'Precio de ejemplo.' ),
			array( 'Menores · grupo de 4', 'menores', 4, '45', 'por ciclo de 4 semanas', 'Precio de ejemplo. De 8 a 16 años.' ),
		);
		foreach ( $tarifas as $i => $t ) {
			self::post(
				MMPadel_Post_Types::TARIFA,
				$t[0],
				'',
				array(
					'tamano_grupo' => $t[2],
					'precio'       => $t[3],
					'periodo'      => $t[4],
					'notas'        => $t[5],
				),
				array( MMPadel_Post_Types::TAX_TIPO_CLASE => array( $tipos[ $t[1] ] ) ),
				$i
			);
		}

		$entrenadores = array(
			array( 'Marta Ruiz', 'Iniciación y técnica', "8 años entrenando.\nMonitora nacional de pádel.", 'Corrige poco y bien: un detalle por sesión hasta que sale solo.' ),
			array( 'Carlos Medina', 'Táctica y competición', "Jugador de categoría absoluta.\n10 años entrenando a grupos de nivel medio y alto.", 'Mucho juego dirigido: aprendes la táctica jugando puntos, no escuchando.' ),
			array( 'Laura Gómez', 'Menores', 'Graduada en Ciencias del Deporte. 5 años con grupos infantiles.', 'Juegos y retos para que los peques aprendan sin darse cuenta.' ),
		);
		foreach ( $entrenadores as $i => $e ) {
			self::post(
				MMPadel_Post_Types::ENTRENADOR,
				$e[0],
				'',
				array(
					'especialidad' => $e[1],
					'experiencia'  => $e[2],
					'enfoque'      => $e[3],
				),
				array(),
				$i
			);
		}

		$hoy     = current_datetime();
		$eventos = array(
			array( 'Pool de iniciación', '+12 days', '18:00', 'Iniciación (1–2)', '' ),
			array( 'Pool intermedio de otoño', '+33 days', '10:00', 'Intermedio (3–4)', '' ),
			array( 'Pool de verano', '-40 days', '19:00', 'Todos los niveles', "Campeones: Ana y Pedro.\nSubcampeones: Luis y Marta.\n24 parejas, 3 horas de juego." ),
		);
		foreach ( $eventos as $e ) {
			self::post(
				MMPadel_Post_Types::EVENTO,
				$e[0],
				"<!-- wp:paragraph -->\n<p>Una tarde de partidos por nivel para conocer a gente nueva y poner en práctica lo trabajado en clase. Evento de ejemplo.</p>\n<!-- /wp:paragraph -->",
				array(
					'fecha'           => $hoy->modify( $e[1] )->format( 'Y-m-d' ),
					'hora'            => $e[2],
					'nivel'           => $e[3],
					'reglas'          => "Partidos de 20 minutos.\nParejas rotativas.\nPelotas incluidas.",
					'inscripcion_url' => 'https://example.com/inscripcion',
					'resultados'      => $e[4],
				),
				array(),
				0,
				'Partidos por nivel, parejas rotativas y buen ambiente.'
			);
		}

		$faq_cats = array(
			'pagos'     => self::term( MMPadel_Post_Types::TAX_FAQ, 'Pagos' ),
			'ciclos'    => self::term( MMPadel_Post_Types::TAX_FAQ, 'Ciclos' ),
			'ausencias' => self::term( MMPadel_Post_Types::TAX_FAQ, 'Ausencias' ),
			'nivel'     => self::term( MMPadel_Post_Types::TAX_FAQ, 'Nivel' ),
		);
		$faqs     = array(
			array( 'pagos', '¿Cuándo se paga cada ciclo?', 'Al empezar cada ciclo de cuatro semanas. Te avisamos antes con el importe y la forma de pago.' ),
			array( 'pagos', '¿La pista está incluida en el precio?', 'Sí. El precio de las tarifas es final: incluye la pista y no hay matrícula.' ),
			array( 'ciclos', '¿Puedo empezar a mitad de ciclo?', 'Si queda una plaza libre en un grupo de tu nivel, sí. Las publicamos en el canal de plazas libres.' ),
			array( 'ciclos', '¿Cómo renuevo para el siguiente ciclo?', 'Antes de que termine el ciclo te pedimos que confirmes si continúas. Si no confirmas a tiempo, la plaza puede ofrecerse a otra persona.' ),
			array( 'ausencias', '¿Qué pasa si falto a una clase?', 'Avísanos cuanto antes. Te explicamos la política de ausencias de la escuela al darte de alta.' ),
			array( 'nivel', 'No sé cuál es mi nivel, ¿qué marco?', 'Marca «No lo sé» en el formulario. Con tu experiencia y lo que nos cuentes te proponemos grupo, y el entrenador lo confirma en la primera clase.' ),
			array( 'nivel', '¿Puedo cambiar de grupo si subo de nivel?', 'Sí. Al cerrar cada ciclo revisamos los grupos y, si has subido de nivel, te proponemos el cambio.' ),
		);
		foreach ( $faqs as $i => $f ) {
			self::post( MMPadel_Post_Types::FAQ, $f[1], $f[2], array(), array( MMPadel_Post_Types::TAX_FAQ => array( $faq_cats[ $f[0] ] ) ), $i );
		}

		$tecnica   = self::term( 'category', 'Técnica' );
		$tactica   = self::term( 'category', 'Táctica' );
		$pildoras  = array(
			array( 'Tres errores típicos en la salida de pared', $tecnica ),
			array( 'Cuándo subir a la red (y cuándo no)', $tactica ),
			array( 'Cómo elegir tu primera pala', $tecnica ),
		);
		$parrafo   = "<!-- wp:paragraph -->\n<p>%s</p>\n<!-- /wp:paragraph -->";
		$contenido = sprintf( $parrafo, 'Píldora de ejemplo. Aquí irá un consejo breve, normalmente acompañado del vídeo de Instagram en el que lo explicamos.' )
			. "\n\n" . sprintf( $parrafo, 'Para insertar un vídeo de Instagram, pega el enlace de la publicación en un bloque «Insertar».' );
		foreach ( $pildoras as $p ) {
			self::post( 'post', $p[0], $contenido, array(), array( 'category' => array( $p[1] ) ) );
		}

		WP_CLI::success( 'Contenido de ejemplo listo. Para borrarlo: wp mmpadel demo --borrar' );
	}

	/**
	 * Páginas de la web (slug => título), en el orden del menú.
	 *
	 * @return array<string, string>
	 */
	private static function pages() {
		return array(
			'inicio'             => __( 'Inicio', 'mmpadel-core' ),
			'metodologia'        => __( 'Metodología', 'mmpadel-core' ),
			'clases-y-tarifas'   => __( 'Clases y tarifas', 'mmpadel-core' ),
			'encuentra-tu-grupo' => __( 'Encuentra tu grupo', 'mmpadel-core' ),
			'plazas-libres'      => __( 'Plazas libres', 'mmpadel-core' ),
			'pools-y-eventos'    => __( 'Pools y eventos', 'mmpadel-core' ),
			'equipo'             => __( 'Equipo', 'mmpadel-core' ),
			'pildoras'           => __( 'Píldoras MMPadel', 'mmpadel-core' ),
			'opiniones'          => __( 'Opiniones', 'mmpadel-core' ),
			'contacto'           => __( 'Contacto y preguntas frecuentes', 'mmpadel-core' ),
			'aviso-legal'        => __( 'Aviso legal', 'mmpadel-core' ),
			'privacidad'         => __( 'Política de privacidad', 'mmpadel-core' ),
			'cookies'            => __( 'Política de cookies', 'mmpadel-core' ),
		);
	}

	/**
	 * Contenido inicial de una página (content/paginas/{slug}.html).
	 *
	 * @param string $slug Slug.
	 * @return string
	 */
	private static function page_content( $slug ) {
		$file = MMPADEL_CORE_DIR . 'content/paginas/' . $slug . '.html';
		return file_exists( $file ) ? trim( (string) file_get_contents( $file ) ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- archivo local del plugin.
	}

	/**
	 * Crea un menú (si no existe) y lo asigna a su ubicación.
	 *
	 * @param string $name     Nombre del menú.
	 * @param string $location Ubicación del tema.
	 * @param array  $items    Elementos: array( slug, etiqueta, clases ).
	 * @param array  $ids      IDs de página por slug.
	 */
	private static function menu( $name, $location, array $items, array $ids ) {
		$menu = wp_get_nav_menu_object( $name );

		if ( $menu ) {
			$menu_id = $menu->term_id;
			WP_CLI::log( "Ya existe:   menú «{$name}» (no se modifican sus elementos)" );
		} else {
			$menu_id = wp_create_nav_menu( $name );
			if ( is_wp_error( $menu_id ) ) {
				WP_CLI::error( $menu_id->get_error_message() );
			}
			foreach ( $items as $item ) {
				wp_update_nav_menu_item(
					$menu_id,
					0,
					array(
						'menu-item-object-id' => $ids[ $item[0] ],
						'menu-item-object'    => 'page',
						'menu-item-type'      => 'post_type',
						'menu-item-title'     => $item[1],
						'menu-item-classes'   => $item[2] ?? '',
						'menu-item-status'    => 'publish',
					)
				);
			}
			WP_CLI::log( "Creado:      menú «{$name}»" );
		}

		$locations              = (array) get_theme_mod( 'nav_menu_locations', array() );
		$locations[ $location ] = $menu_id;
		set_theme_mod( 'nav_menu_locations', $locations );
	}

	/**
	 * Rellena los ajustes vacíos con datos de ejemplo. Teléfono, WhatsApp,
	 * Instagram y Facebook no: ya tienen sus valores reales
	 * (MMPadel_Settings::defaults()).
	 */
	private static function demo_settings() {
		$options = (array) get_option( MMPadel_Settings::OPTION, array() );
		$demo    = array(
			'email_publico'      => 'hola@mmpadel.test',
			'direccion'          => 'Calle de Ejemplo 1, 29000 Málaga',
			'mapa_url'           => 'https://www.google.com/maps/search/?api=1&query=M%C3%A1laga',
			'horario'            => "Lunes a viernes: 9:00–22:00\nSábados: 9:00–14:00",
			'canal_plazas_url'   => 'https://whatsapp.com/channel/EJEMPLO',
			'google_resenas_url' => 'https://www.google.com/maps',
			'titular'            => 'MMPadel (dato de ejemplo)',
		);

		foreach ( $demo as $key => $value ) {
			if ( empty( $options[ $key ] ) ) {
				$options[ $key ] = $value;
			}
		}
		update_option( MMPadel_Settings::OPTION, $options );

		if ( in_array( get_option( 'blogname' ), array( 'mmpadel', 'My WordPress Website', 'Mi sitio web' ), true ) ) {
			update_option( 'blogname', 'MMPadel' );
		}
		if ( in_array( get_option( 'blogdescription' ), array( '', 'Just another WordPress site', 'Otro sitio realizado con WordPress' ), true ) ) {
			update_option( 'blogdescription', 'Escuela de pádel en Málaga. Clases por niveles en ciclos de cuatro semanas.' );
		}

		WP_CLI::log( 'Ajustes:     rellenados los que estaban vacíos' );
	}

	/**
	 * Borra la entrada, la página y el borrador de privacidad que trae WordPress.
	 */
	private static function delete_wordpress_samples() {
		foreach ( array( 'hello-world', 'hola-mundo' ) as $slug ) {
			$post = get_page_by_path( $slug, OBJECT, 'post' );
			if ( $post ) {
				wp_delete_post( $post->ID, true );
			}
		}
		foreach ( array( 'sample-page', 'pagina-ejemplo', 'privacy-policy', 'politica-de-privacidad' ) as $slug ) {
			$page = get_page_by_path( $slug, OBJECT, 'page' );
			if ( $page && (int) get_option( 'wp_page_for_privacy_policy' ) !== $page->ID ) {
				wp_delete_post( $page->ID, true );
			}
		}
	}

	/**
	 * Crea un término si no existe y lo marca como de ejemplo.
	 *
	 * @param string $taxonomy    Taxonomía.
	 * @param string $name        Nombre.
	 * @param string $description Descripción.
	 * @return int ID del término.
	 */
	private static function term( $taxonomy, $name, $description = '' ) {
		$term = term_exists( $name, $taxonomy );
		if ( $term ) {
			return (int) $term['term_id'];
		}

		$term = wp_insert_term( $name, $taxonomy, array( 'description' => $description ) );
		if ( is_wp_error( $term ) ) {
			WP_CLI::error( $term->get_error_message() );
		}
		update_term_meta( $term['term_id'], self::DEMO_META, 1 );
		return (int) $term['term_id'];
	}

	/**
	 * Crea un contenido de ejemplo si no hay otro con el mismo título.
	 *
	 * @param string $post_type Tipo de contenido.
	 * @param string $title     Título.
	 * @param string $content   Contenido.
	 * @param array  $fields    Campos propios (clave => valor).
	 * @param array  $terms     Términos (taxonomía => IDs).
	 * @param int    $order     Orden.
	 * @param string $excerpt   Extracto.
	 */
	private static function post( $post_type, $title, $content, array $fields = array(), array $terms = array(), $order = 0, $excerpt = '' ) {
		$existing = get_posts(
			array(
				'post_type'      => $post_type,
				'title'          => $title,
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);
		if ( $existing ) {
			return;
		}

		$id = wp_insert_post(
			array(
				'post_type'    => $post_type,
				'post_status'  => 'publish',
				'post_title'   => $title,
				'post_content' => $content,
				'post_excerpt' => $excerpt,
				'menu_order'   => $order,
			),
			true
		);
		if ( is_wp_error( $id ) ) {
			WP_CLI::error( $id->get_error_message() );
		}

		update_post_meta( $id, self::DEMO_META, 1 );

		$declared = MMPadel_Fields::get_fields( $post_type );
		foreach ( $fields as $key => $value ) {
			if ( isset( $declared[ $key ] ) && '' !== (string) $value ) {
				update_post_meta( $id, MMPadel_Fields::meta_key( $key ), MMPadel_Fields::sanitize( $value, $declared[ $key ] ) );
			}
		}
		foreach ( $terms as $taxonomy => $term_ids ) {
			wp_set_object_terms( $id, $term_ids, $taxonomy );
		}

		WP_CLI::log( sprintf( 'Ejemplo:     %s «%s»', get_post_type_object( $post_type )->labels->singular_name, $title ) );
	}

	/**
	 * Borra todo lo marcado como contenido de ejemplo.
	 */
	private static function delete_demo() {
		$posts = get_posts(
			array(
				// 'any' excluiría los tipos no públicos (tarifas, entrenadores, FAQ).
				'post_type'      => array(
					'post',
					'page',
					MMPadel_Post_Types::TARIFA,
					MMPadel_Post_Types::ENTRENADOR,
					MMPadel_Post_Types::EVENTO,
					MMPadel_Post_Types::FAQ,
				),
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'meta_key'       => self::DEMO_META, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- comando puntual.
			)
		);
		foreach ( $posts as $id ) {
			wp_delete_post( $id, true );
		}

		$terms = get_terms(
			array(
				'hide_empty' => false,
				'meta_key'   => self::DEMO_META, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- comando puntual.
			)
		);
		foreach ( is_array( $terms ) ? $terms : array() as $term ) {
			wp_delete_term( $term->term_id, $term->taxonomy );
		}

		WP_CLI::success( sprintf( 'Borrados %d contenidos y %d términos de ejemplo. Los ajustes no se tocan.', count( $posts ), is_array( $terms ) ? count( $terms ) : 0 ) );
	}
}
