<?php
/**
 * Solicitudes en el panel: listado, ficha, estado y exportación CSV.
 *
 * @package MMPadel_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Administración de solicitudes.
 */
final class MMPadel_Requests_Admin {

	const EXPORT_ACTION = 'mmpadel_exportar_solicitudes';

	/**
	 * Engancha las acciones.
	 */
	public static function init() {
		$type = MMPadel_Post_Types::SOLICITUD;

		add_filter( "manage_{$type}_posts_columns", array( __CLASS__, 'columns' ) );
		add_action( "manage_{$type}_posts_custom_column", array( __CLASS__, 'render_column' ), 10, 2 );
		add_filter( "manage_edit-{$type}_sortable_columns", array( __CLASS__, 'sortable_columns' ) );
		add_filter( 'post_row_actions', array( __CLASS__, 'row_actions' ), 10, 2 );
		add_filter( "bulk_actions-edit-{$type}", array( __CLASS__, 'bulk_actions' ) );
		add_action( 'restrict_manage_posts', array( __CLASS__, 'status_filter' ) );
		add_action( 'manage_posts_extra_tablenav', array( __CLASS__, 'export_button' ) );
		add_action( 'pre_get_posts', array( __CLASS__, 'filter_query' ) );

		add_action( "add_meta_boxes_{$type}", array( __CLASS__, 'meta_boxes' ) );
		add_action( "save_post_{$type}", array( __CLASS__, 'save' ) );
		add_action( 'admin_head', array( __CLASS__, 'admin_css' ) );
		add_action( 'admin_menu', array( __CLASS__, 'menu_badge' ), 99 );

		add_action( 'admin_post_' . self::EXPORT_ACTION, array( __CLASS__, 'export_csv' ) );
	}

	/**
	 * Estados de una solicitud.
	 *
	 * @return array<string, string>
	 */
	public static function statuses() {
		return array(
			'nueva'      => __( 'Nueva', 'mmpadel-core' ),
			'contactada' => __( 'Contactada', 'mmpadel-core' ),
			'asignada'   => __( 'Asignada a grupo', 'mmpadel-core' ),
			'descartada' => __( 'Descartada', 'mmpadel-core' ),
		);
	}

	/**
	 * Lee un dato de la solicitud.
	 *
	 * @param int    $post_id ID.
	 * @param string $key     Clave sin prefijo.
	 * @return string
	 */
	public static function get( $post_id, $key ) {
		return (string) get_post_meta( $post_id, MMPadel_Form::META_PREFIX . $key, true );
	}

	/**
	 * Origen en pocas palabras: «instagram / bio», «google.com» o «Directo».
	 *
	 * @param int $post_id ID.
	 * @return string
	 */
	public static function origin_label( $post_id ) {
		$source = self::get( $post_id, 'origen_utm_source' );
		if ( $source ) {
			$medium = self::get( $post_id, 'origen_utm_medium' );
			return $medium ? $source . ' / ' . $medium : $source;
		}
		$referrer = self::get( $post_id, 'origen_referrer' );
		if ( $referrer ) {
			return (string) wp_parse_url( $referrer, PHP_URL_HOST );
		}
		return __( 'Directo', 'mmpadel-core' );
	}

	/**
	 * Columnas del listado.
	 *
	 * @param array $columns Columnas por defecto.
	 * @return array
	 */
	public static function columns( $columns ) {
		return array(
			'cb'                     => $columns['cb'],
			'title'                  => __( 'Nombre', 'mmpadel-core' ),
			'mmpadel_estado'         => __( 'Estado', 'mmpadel-core' ),
			'mmpadel_contacto'       => __( 'Contacto', 'mmpadel-core' ),
			'mmpadel_nivel'          => __( 'Nivel', 'mmpadel-core' ),
			'mmpadel_tipo'           => __( 'Tipo de clase', 'mmpadel-core' ),
			'mmpadel_disponibilidad' => __( 'Disponibilidad', 'mmpadel-core' ),
			'mmpadel_origen'         => __( 'Origen', 'mmpadel-core' ),
			'date'                   => __( 'Recibida', 'mmpadel-core' ),
		);
	}

	/**
	 * Pinta una columna propia.
	 *
	 * @param string $column  Columna.
	 * @param int    $post_id ID.
	 */
	public static function render_column( $column, $post_id ) {
		switch ( $column ) {
			case 'mmpadel_estado':
				$estado   = self::get( $post_id, 'estado' );
				$statuses = self::statuses();
				printf(
					'<span class="mmpadel-estado mmpadel-estado--%1$s">%2$s</span>',
					esc_attr( $estado ),
					esc_html( $statuses[ $estado ] ?? $estado )
				);
				break;

			case 'mmpadel_contacto':
				$tel   = self::get( $post_id, 'telefono' );
				$email = self::get( $post_id, 'email' );
				printf(
					'<a href="%1$s">%2$s</a><br><a href="%3$s">%4$s</a>',
					esc_url( mmpadel_get_tel_url( $tel ), array( 'tel' ) ),
					esc_html( $tel ),
					esc_url( 'mailto:' . $email ),
					esc_html( $email )
				);
				break;

			case 'mmpadel_nivel':
				echo esc_html( MMPadel_Form::display_value( 'nivel', self::get( $post_id, 'nivel' ) ) );
				break;

			case 'mmpadel_tipo':
				echo esc_html(
					MMPadel_Form::display_value( 'tipo_clase', self::get( $post_id, 'tipo_clase' ) )
					. ' · '
					/* translators: %s: número de personas. */
					. sprintf( _n( '%s persona', '%s personas', (int) self::get( $post_id, 'personas' ), 'mmpadel-core' ), self::get( $post_id, 'personas' ) )
				);
				break;

			case 'mmpadel_disponibilidad':
				echo esc_html( MMPadel_Form::display_value( 'dias', self::get( $post_id, 'dias' ) ) );
				echo '<br><span class="description">' . esc_html( MMPadel_Form::display_value( 'franjas', self::get( $post_id, 'franjas' ) ) ) . '</span>';
				break;

			case 'mmpadel_origen':
				echo esc_html( self::origin_label( $post_id ) );
				break;
		}
	}

	/**
	 * Columnas ordenables.
	 *
	 * @param array $columns Columnas.
	 * @return array
	 */
	public static function sortable_columns( $columns ) {
		$columns['mmpadel_estado'] = 'mmpadel_estado';
		return $columns;
	}

	/**
	 * Acciones de fila: sin edición rápida (los datos del formulario no se editan).
	 *
	 * @param array   $actions Acciones.
	 * @param WP_Post $post    Contenido.
	 * @return array
	 */
	public static function row_actions( $actions, $post ) {
		if ( MMPadel_Post_Types::SOLICITUD !== $post->post_type ) {
			return $actions;
		}
		unset( $actions['inline hide-if-no-js'], $actions['view'] );
		if ( isset( $actions['edit'] ) ) {
			$actions['edit'] = sprintf( '<a href="%1$s">%2$s</a>', esc_url( get_edit_post_link( $post->ID ) ), esc_html__( 'Ver solicitud', 'mmpadel-core' ) );
		}
		return $actions;
	}

	/**
	 * Acciones en lote: solo mover a la papelera.
	 *
	 * @param array $actions Acciones.
	 * @return array
	 */
	public static function bulk_actions( $actions ) {
		unset( $actions['edit'] );
		return $actions;
	}

	/**
	 * Estado elegido en el filtro del listado.
	 *
	 * @return string
	 */
	private static function current_status_filter() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- filtro de listado, solo lectura.
		$estado = isset( $_GET['mmpadel_estado'] ) ? sanitize_key( wp_unslash( $_GET['mmpadel_estado'] ) ) : '';
		return array_key_exists( $estado, self::statuses() ) ? $estado : '';
	}

	/**
	 * Desplegable para filtrar por estado.
	 *
	 * @param string $post_type Tipo de contenido del listado.
	 */
	public static function status_filter( $post_type ) {
		if ( MMPadel_Post_Types::SOLICITUD !== $post_type ) {
			return;
		}
		$current = self::current_status_filter();
		echo '<label class="screen-reader-text" for="mmpadel-filtro-estado">' . esc_html__( 'Filtrar por estado', 'mmpadel-core' ) . '</label>';
		echo '<select name="mmpadel_estado" id="mmpadel-filtro-estado">';
		echo '<option value="">' . esc_html__( 'Todos los estados', 'mmpadel-core' ) . '</option>';
		foreach ( self::statuses() as $value => $label ) {
			printf( '<option value="%1$s"%2$s>%3$s</option>', esc_attr( $value ), selected( $current, $value, false ), esc_html( $label ) );
		}
		echo '</select>';
	}

	/**
	 * Aplica el filtro y la ordenación por estado.
	 *
	 * @param WP_Query $query Consulta.
	 */
	public static function filter_query( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() || MMPadel_Post_Types::SOLICITUD !== $query->get( 'post_type' ) ) {
			return;
		}

		$estado = self::current_status_filter();
		if ( $estado ) {
			$query->set(
				'meta_query',
				array(
					array(
						'key'   => MMPadel_Form::META_PREFIX . 'estado',
						'value' => $estado,
					),
				)
			);
		}

		if ( 'mmpadel_estado' === $query->get( 'orderby' ) ) {
			$query->set( 'meta_key', MMPadel_Form::META_PREFIX . 'estado' );
			$query->set( 'orderby', 'meta_value' );
		}
	}

	/**
	 * Botón «Exportar CSV» encima del listado. Respeta el filtro de estado.
	 *
	 * @param string $which top|bottom.
	 */
	public static function export_button( $which ) {
		$screen = get_current_screen();
		if ( 'top' !== $which || ! $screen || 'edit-' . MMPadel_Post_Types::SOLICITUD !== $screen->id ) {
			return;
		}
		if ( ! current_user_can( 'read_private_mmpadel_solicitudes' ) ) {
			return;
		}

		$url = wp_nonce_url(
			add_query_arg(
				array(
					'action'         => self::EXPORT_ACTION,
					'mmpadel_estado' => self::current_status_filter(),
				),
				admin_url( 'admin-post.php' )
			),
			self::EXPORT_ACTION
		);

		printf(
			'<div class="alignleft actions"><a class="button" href="%1$s">%2$s</a></div>',
			esc_url( $url ),
			esc_html__( 'Exportar CSV', 'mmpadel-core' )
		);
	}

	/**
	 * Cajas de la ficha de una solicitud.
	 */
	public static function meta_boxes() {
		add_meta_box( 'mmpadel-solicitud', __( 'Datos de la solicitud', 'mmpadel-core' ), array( __CLASS__, 'render_details' ), null, 'normal', 'high' );
		add_meta_box( 'mmpadel-gestion', __( 'Gestión', 'mmpadel-core' ), array( __CLASS__, 'render_management' ), null, 'side', 'high' );
		remove_meta_box( 'slugdiv', MMPadel_Post_Types::SOLICITUD, 'normal' );
	}

	/**
	 * Ficha de solo lectura con los datos del formulario.
	 *
	 * @param WP_Post $post Solicitud.
	 */
	public static function render_details( $post ) {
		$schema   = MMPadel_Form::schema();
		$telefono = self::get( $post->ID, 'telefono' );
		$email    = self::get( $post->ID, 'email' );
		$whatsapp = mmpadel_get_whatsapp_url( $telefono );
		?>
		<p class="mmpadel-acciones">
			<a class="button button-primary" href="<?php echo esc_url( mmpadel_get_tel_url( $telefono ), array( 'tel' ) ); ?>"><?php esc_html_e( 'Llamar', 'mmpadel-core' ); ?></a>
			<?php if ( $whatsapp ) : ?>
				<a class="button" href="<?php echo esc_url( $whatsapp ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'WhatsApp', 'mmpadel-core' ); ?></a>
			<?php endif; ?>
			<a class="button" href="<?php echo esc_url( 'mailto:' . $email ); ?>"><?php esc_html_e( 'Email', 'mmpadel-core' ); ?></a>
		</p>

		<?php foreach ( MMPadel_Form::sections() as $section_key => $section_title ) : ?>
			<h3><?php echo esc_html( $section_title ); ?></h3>
			<table class="widefat striped mmpadel-tabla">
				<tbody>
				<?php foreach ( $schema as $key => $field ) : ?>
					<?php
					if ( $field['section'] !== $section_key ) {
						continue;
					}
					$value = MMPadel_Form::display_value( $key, self::get( $post->ID, $key ), $schema );
					?>
					<tr>
						<th scope="row"><?php echo esc_html( $field['short'] ); ?></th>
						<td><?php echo '' !== $value ? nl2br( esc_html( $value ) ) : '—'; ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php endforeach; ?>

		<h3><?php esc_html_e( 'Origen', 'mmpadel-core' ); ?></h3>
		<table class="widefat striped mmpadel-tabla">
			<tbody>
			<?php
			$origin = array();
			foreach ( MMPadel_Form::ORIGIN_KEYS as $key ) {
				$origin[ $key ] = self::get( $post->ID, 'origen_' . $key );
			}
			foreach ( explode( "\n", MMPadel_Mailer::origin_summary( $origin ) ) as $line ) :
				$parts = explode( ': ', $line, 2 );
				?>
				<tr>
					<th scope="row"><?php echo esc_html( $parts[0] ); ?></th>
					<td><?php echo esc_html( $parts[1] ?? '' ); ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>

		<h3><?php esc_html_e( 'Consentimiento', 'mmpadel-core' ); ?></h3>
		<p>
			<?php
			$fecha = self::get( $post->ID, 'privacidad' );
			printf(
				/* translators: 1: fecha y hora, 2: URL de la política. */
				esc_html__( 'Aceptó la política de privacidad el %1$s (%2$s).', 'mmpadel-core' ),
				esc_html( $fecha ? mysql2date( get_option( 'date_format' ) . ' H:i', $fecha ) : '—' ),
				esc_html( self::get( $post->ID, 'privacidad_url' ) )
			);
			?>
		</p>
		<?php
	}

	/**
	 * Caja lateral: estado y notas internas.
	 *
	 * @param WP_Post $post Solicitud.
	 */
	public static function render_management( $post ) {
		wp_nonce_field( 'mmpadel_gestion', '_mmpadel_gestion_nonce' );
		$estado = self::get( $post->ID, 'estado' );
		?>
		<p>
			<label for="mmpadel-estado"><strong><?php esc_html_e( 'Estado', 'mmpadel-core' ); ?></strong></label><br>
			<select name="mmpadel_estado" id="mmpadel-estado" class="widefat">
				<?php foreach ( self::statuses() as $value => $label ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $estado, $value ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<label for="mmpadel-notas"><strong><?php esc_html_e( 'Notas internas', 'mmpadel-core' ); ?></strong></label><br>
			<textarea name="mmpadel_notas" id="mmpadel-notas" rows="5" class="widefat"><?php echo esc_textarea( self::get( $post->ID, 'notas' ) ); ?></textarea>
			<span class="description"><?php esc_html_e( 'Solo se ven en el panel. P. ej.: grupo asignado o cuándo volver a llamar.', 'mmpadel-core' ); ?></span>
		</p>
		<?php
	}

	/**
	 * Guarda estado y notas. Los datos del formulario no se modifican.
	 *
	 * @param int $post_id ID.
	 */
	public static function save( $post_id ) {
		if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
			return;
		}
		if ( ! isset( $_POST['_mmpadel_gestion_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_mmpadel_gestion_nonce'] ) ), 'mmpadel_gestion' ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$estado = isset( $_POST['mmpadel_estado'] ) ? sanitize_key( wp_unslash( $_POST['mmpadel_estado'] ) ) : '';
		if ( array_key_exists( $estado, self::statuses() ) ) {
			update_post_meta( $post_id, MMPadel_Form::META_PREFIX . 'estado', $estado );
		}

		if ( isset( $_POST['mmpadel_notas'] ) ) {
			update_post_meta( $post_id, MMPadel_Form::META_PREFIX . 'notas', sanitize_textarea_field( wp_unslash( $_POST['mmpadel_notas'] ) ) );
		}
	}

	/**
	 * Estilos del listado y la ficha. En la ficha se ocultan las opciones de
	 * publicación que no tienen sentido (visibilidad, fecha…).
	 */
	public static function admin_css() {
		$screen = get_current_screen();
		if ( ! $screen || MMPadel_Post_Types::SOLICITUD !== $screen->post_type ) {
			return;
		}
		?>
		<style>
			.mmpadel-estado { display: inline-block; padding: 2px 8px; border-radius: 999px; background: #f0f0f1; font-weight: 600; }
			.mmpadel-estado--nueva { background: #fcf0c3; color: #614a00; }
			.mmpadel-estado--contactada { background: #dbeafe; color: #1e3a8a; }
			.mmpadel-estado--asignada { background: #d1fae5; color: #065f46; }
			.mmpadel-estado--descartada { background: #f0f0f1; color: #50575e; }
			.mmpadel-tabla th { width: 30%; font-weight: 600; }
			.mmpadel-acciones .button { margin-right: 4px; }
			.post-type-mmpadel_solicitud #minor-publishing,
			.post-type-mmpadel_solicitud #misc-publishing-actions,
			.post-type-mmpadel_solicitud .page-title-action { display: none; }
		</style>
		<?php
	}

	/**
	 * Contador de solicitudes nuevas junto al menú.
	 */
	public static function menu_badge() {
		global $menu;

		if ( ! current_user_can( 'edit_mmpadel_solicitudes' ) ) {
			return;
		}

		$count = self::count_new();
		if ( ! $count ) {
			return;
		}

		foreach ( $menu as $index => $item ) {
			if ( 'edit.php?post_type=' . MMPadel_Post_Types::SOLICITUD === $item[2] ) {
				// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- forma estándar de añadir un contador al menú.
				$menu[ $index ][0] .= sprintf( ' <span class="awaiting-mod count-%1$d"><span class="pending-count">%1$d</span></span>', $count );
				break;
			}
		}
	}

	/**
	 * Número de solicitudes en estado «nueva».
	 *
	 * @return int
	 */
	private static function count_new() {
		$query = new WP_Query(
			array(
				'post_type'      => MMPadel_Post_Types::SOLICITUD,
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_key'       => MMPadel_Form::META_PREFIX . 'estado', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- tabla pequeña.
				'meta_value'     => 'nueva', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- tabla pequeña.
			)
		);
		return (int) $query->found_posts;
	}

	/**
	 * Descarga las solicitudes en CSV (separador «;» y BOM para Excel en español).
	 */
	public static function export_csv() {
		if ( ! current_user_can( 'read_private_mmpadel_solicitudes' ) ) {
			wp_die( esc_html__( 'No tienes permisos para exportar las solicitudes.', 'mmpadel-core' ), '', array( 'response' => 403 ) );
		}
		check_admin_referer( self::EXPORT_ACTION );

		$args   = array(
			'post_type'      => MMPadel_Post_Types::SOLICITUD,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'DESC',
		);
		$estado = self::current_status_filter();
		if ( $estado ) {
			$args['meta_key']   = MMPadel_Form::META_PREFIX . 'estado'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- exportación puntual.
			$args['meta_value'] = $estado; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- exportación puntual.
		}

		$schema   = MMPadel_Form::schema();
		$statuses = self::statuses();

		$header = array( 'ID', __( 'Recibida', 'mmpadel-core' ), __( 'Estado', 'mmpadel-core' ) );
		foreach ( $schema as $field ) {
			$header[] = $field['short'];
		}
		$header = array_merge( $header, MMPadel_Form::ORIGIN_KEYS, array( __( 'Consentimiento', 'mmpadel-core' ), __( 'Notas internas', 'mmpadel-core' ) ) );

		$filename = 'mmpadel-solicitudes' . ( $estado ? '-' . $estado : '' ) . '-' . wp_date( 'Y-m-d' ) . '.csv';
		nocache_headers();
		header( 'Content-Type: text/csv; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );

		$out = fopen( 'php://output', 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- salida directa de la descarga.
		fwrite( $out, "\xEF\xBB\xBF" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite -- BOM UTF-8 para Excel.
		fputcsv( $out, $header, ';', '"', '' );

		foreach ( get_posts( $args ) as $post ) {
			$estado_post = self::get( $post->ID, 'estado' );
			$row         = array( $post->ID, get_the_date( 'Y-m-d H:i', $post ), $statuses[ $estado_post ] ?? $estado_post );
			foreach ( array_keys( $schema ) as $key ) {
				$row[] = MMPadel_Form::display_value( $key, self::get( $post->ID, $key ), $schema );
			}
			foreach ( MMPadel_Form::ORIGIN_KEYS as $key ) {
				$row[] = self::get( $post->ID, 'origen_' . $key );
			}
			$row[] = self::get( $post->ID, 'privacidad' );
			$row[] = self::get( $post->ID, 'notas' );

			fputcsv( $out, array_map( array( __CLASS__, 'csv_safe' ), $row ), ';', '"', '' );
		}

		fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- salida directa de la descarga.
		exit;
	}

	/**
	 * Neutraliza la inyección de fórmulas: una celda que empieza por = + - @
	 * (o tabulador / retorno) se prefija con un apóstrofo para que Excel la
	 * trate como texto.
	 *
	 * @param mixed $value Valor.
	 * @return string
	 */
	public static function csv_safe( $value ) {
		$value = (string) $value;
		return preg_match( '/^[=+\-@\t\r]/', $value ) ? "'" . $value : $value;
	}
}
