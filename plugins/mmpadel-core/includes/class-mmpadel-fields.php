<?php
/**
 * Mini-framework de campos propios (sustituye a ACF).
 *
 * Cada tipo de contenido declara sus campos en un array. A partir de esa
 * declaración se registran los metadatos (register_post_meta, con su
 * sanitizado y permisos), se pinta la caja de campos en el editor, se guarda
 * con nonce y comprobación de permisos y se añaden columnas al listado.
 *
 * Declaración de un campo:
 *   'precio' => array(
 *       'label'       => 'Precio final',
 *       'type'        => 'price',  // text|textarea|url|email|tel|number|price|date|time|select
 *       'description' => 'Texto de ayuda',
 *       'options'     => array(),  // solo select: valor => etiqueta
 *       'placeholder' => '',
 *       'default'     => '',       // valor inicial en contenidos nuevos
 *       'column'      => false,    // mostrar en el listado del panel
 *   )
 *
 * Los valores se guardan en metadatos protegidos con el prefijo _mmpadel_.
 *
 * @package MMPadel_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registro, edición y guardado de campos.
 */
final class MMPadel_Fields {

	/**
	 * Cajas de campos por tipo de contenido.
	 *
	 * @var array<string, array{title: string, fields: array<string, array>}>
	 */
	private static $boxes = array();

	/**
	 * Engancha las acciones.
	 */
	public static function init() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ), 10, 1 );
		add_action( 'save_post', array( __CLASS__, 'save' ), 10, 2 );
	}

	/**
	 * Clave del metadato para un campo.
	 *
	 * @param string $key Clave del campo.
	 * @return string
	 */
	public static function meta_key( $key ) {
		return '_mmpadel_' . $key;
	}

	/**
	 * Declara los campos de un tipo de contenido. Se llama en init.
	 *
	 * @param string $post_type Tipo de contenido.
	 * @param string $title     Título de la caja.
	 * @param array  $fields    Campos (clave => declaración).
	 */
	public static function register( $post_type, $title, array $fields ) {
		foreach ( $fields as $key => $field ) {
			$fields[ $key ] = wp_parse_args(
				$field,
				array(
					'label'       => $key,
					'type'        => 'text',
					'description' => '',
					'options'     => array(),
					'placeholder' => '',
					'default'     => '',
					'column'      => false,
				)
			);

			$field = $fields[ $key ];

			register_post_meta(
				$post_type,
				self::meta_key( $key ),
				array(
					'type'              => 'string',
					'single'            => true,
					'show_in_rest'      => true,
					'sanitize_callback' => static function ( $value ) use ( $field ) {
						return self::sanitize( $value, $field );
					},
					'auth_callback'     => static function ( $allowed, $meta_key, $post_id ) {
						return current_user_can( 'edit_post', $post_id );
					},
				)
			);
		}

		self::$boxes[ $post_type ] = array(
			'title'  => $title,
			'fields' => $fields,
		);

		if ( wp_list_filter( $fields, array( 'column' => true ) ) ) {
			add_filter( "manage_{$post_type}_posts_columns", array( __CLASS__, 'add_columns' ) );
			add_action( "manage_{$post_type}_posts_custom_column", array( __CLASS__, 'render_column' ), 10, 2 );
		}
	}

	/**
	 * Campos declarados de un tipo de contenido.
	 *
	 * @param string $post_type Tipo de contenido.
	 * @return array
	 */
	public static function get_fields( $post_type ) {
		return isset( self::$boxes[ $post_type ] ) ? self::$boxes[ $post_type ]['fields'] : array();
	}

	/**
	 * Sanitiza un valor según el tipo de campo.
	 *
	 * @param mixed $value Valor recibido.
	 * @param array $field Declaración del campo.
	 * @return string
	 */
	public static function sanitize( $value, array $field ) {
		if ( is_array( $value ) || is_object( $value ) ) {
			return '';
		}
		$value = trim( (string) $value );

		switch ( $field['type'] ) {
			case 'textarea':
				return sanitize_textarea_field( $value );

			case 'url':
				return esc_url_raw( $value, array( 'http', 'https' ) );

			case 'email':
				return sanitize_email( $value );

			case 'number':
				// Acepta «4» y también «4 alumnos».
				return preg_match( '/\d+/', $value, $m ) ? (string) (int) $m[0] : '';

			case 'price':
				// Fuera símbolos, espacios y texto («45 €», «45 EUR»).
				$value = preg_replace( '/[^0-9.,]/', '', $value );
				// Acepta «1.234,50», «1234,50» y «1234.50».
				if ( str_contains( $value, ',' ) ) {
					$value = str_replace( array( '.', ',' ), array( '', '.' ), $value );
				}
				return is_numeric( $value ) && (float) $value >= 0 ? (string) round( (float) $value, 2 ) : '';

			case 'date':
				if ( preg_match( '/^(\d{4})-(\d{2})-(\d{2})$/', $value, $m ) && checkdate( (int) $m[2], (int) $m[3], (int) $m[1] ) ) {
					return $value;
				}
				return '';

			case 'time':
				return preg_match( '/^([01]\d|2[0-3]):[0-5]\d$/', $value ) ? $value : '';

			case 'select':
				return array_key_exists( $value, $field['options'] ) ? $value : '';

			default:
				return sanitize_text_field( $value );
		}
	}

	/**
	 * Valor listo para mostrar (sin escapar).
	 *
	 * @param string $value Valor guardado.
	 * @param array  $field Declaración del campo.
	 * @return string
	 */
	public static function format( $value, array $field ) {
		if ( '' === $value ) {
			return '';
		}

		switch ( $field['type'] ) {
			case 'price':
				return mmpadel_format_price( $value );

			case 'date':
				$date = date_create_immutable( $value, wp_timezone() );
				return $date ? wp_date( get_option( 'date_format' ), $date->getTimestamp() ) : $value;

			case 'select':
				return $field['options'][ $value ] ?? $value;

			default:
				return $value;
		}
	}

	/**
	 * Añade la caja de campos al editor.
	 *
	 * @param string $post_type Tipo de contenido que se edita.
	 */
	public static function add_meta_boxes( $post_type ) {
		if ( ! isset( self::$boxes[ $post_type ] ) ) {
			return;
		}

		add_meta_box(
			'mmpadel-fields',
			self::$boxes[ $post_type ]['title'],
			array( __CLASS__, 'render_box' ),
			$post_type,
			'normal',
			'high'
		);
	}

	/**
	 * Pinta la caja de campos.
	 *
	 * @param WP_Post $post Contenido.
	 */
	public static function render_box( $post ) {
		wp_nonce_field( 'mmpadel_fields_' . $post->post_type, '_mmpadel_fields_nonce' );

		$is_new = 'auto-draft' === $post->post_status;

		echo '<div class="mmpadel-fields">';
		foreach ( self::get_fields( $post->post_type ) as $key => $field ) {
			$value = $is_new ? $field['default'] : (string) get_post_meta( $post->ID, self::meta_key( $key ), true );
			self::render_field( $key, $field, $value );
		}
		echo '</div>';
	}

	/**
	 * Pinta un campo.
	 *
	 * @param string $key   Clave.
	 * @param array  $field Declaración.
	 * @param string $value Valor actual.
	 */
	private static function render_field( $key, array $field, $value ) {
		$id   = 'mmpadel-field-' . $key;
		$name = 'mmpadel_fields[' . $key . ']';
		$desc = $field['description'] ? ' aria-describedby="' . esc_attr( $id . '-desc' ) . '"' : '';

		echo '<p class="mmpadel-field">';
		printf( '<label for="%1$s"><strong>%2$s</strong></label><br>', esc_attr( $id ), esc_html( $field['label'] ) );

		switch ( $field['type'] ) {
			case 'textarea':
				printf(
					'<textarea id="%1$s" name="%2$s" class="widefat" rows="4" placeholder="%3$s"%4$s>%5$s</textarea>',
					esc_attr( $id ),
					esc_attr( $name ),
					esc_attr( $field['placeholder'] ),
					$desc, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escapado al construirlo.
					esc_textarea( $value )
				);
				break;

			case 'select':
				printf( '<select id="%1$s" name="%2$s"%3$s>', esc_attr( $id ), esc_attr( $name ), $desc ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escapado al construirlo.
				echo '<option value="">' . esc_html__( '— Elegir —', 'mmpadel-core' ) . '</option>';
				foreach ( $field['options'] as $option_value => $option_label ) {
					printf( '<option value="%1$s"%2$s>%3$s</option>', esc_attr( $option_value ), selected( $value, (string) $option_value, false ), esc_html( $option_label ) );
				}
				echo '</select>';
				break;

			default:
				$types = array(
					'url'    => 'url',
					'email'  => 'email',
					'tel'    => 'tel',
					'number' => 'number',
					'date'   => 'date',
					'time'   => 'time',
					'price'  => 'text',
				);
				$type  = $types[ $field['type'] ] ?? 'text';
				$extra = 'price' === $field['type'] ? ' inputmode="decimal"' : '';
				$class = in_array( $field['type'], array( 'number', 'price', 'date', 'time' ), true ) ? 'regular-text' : 'widefat';

				printf(
					'<input type="%1$s" id="%2$s" name="%3$s" value="%4$s" class="%5$s" placeholder="%6$s"%7$s%8$s>',
					esc_attr( $type ),
					esc_attr( $id ),
					esc_attr( $name ),
					esc_attr( $value ),
					esc_attr( $class ),
					esc_attr( $field['placeholder'] ),
					$extra, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- literal.
					$desc // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escapado al construirlo.
				);
		}

		if ( $field['description'] ) {
			printf( '<br><span class="description" id="%1$s">%2$s</span>', esc_attr( $id . '-desc' ), esc_html( $field['description'] ) );
		}
		echo '</p>';
	}

	/**
	 * Guarda los campos al guardar el contenido.
	 *
	 * @param int     $post_id ID.
	 * @param WP_Post $post    Contenido.
	 */
	public static function save( $post_id, $post ) {
		if ( ! isset( self::$boxes[ $post->post_type ] ) ) {
			return;
		}
		if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
			return;
		}
		if ( ! isset( $_POST['_mmpadel_fields_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_mmpadel_fields_nonce'] ) ), 'mmpadel_fields_' . $post->post_type ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Cada valor se sanitiza abajo según su tipo de campo.
		$input = isset( $_POST['mmpadel_fields'] ) && is_array( $_POST['mmpadel_fields'] ) ? wp_unslash( $_POST['mmpadel_fields'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		foreach ( self::get_fields( $post->post_type ) as $key => $field ) {
			$value = self::sanitize( $input[ $key ] ?? '', $field );

			if ( '' === $value ) {
				delete_post_meta( $post_id, self::meta_key( $key ) );
			} else {
				update_post_meta( $post_id, self::meta_key( $key ), $value );
			}
		}
	}

	/**
	 * Añade las columnas marcadas con 'column' tras el título.
	 *
	 * @param array $columns Columnas.
	 * @return array
	 */
	public static function add_columns( $columns ) {
		$post_type = get_current_screen() ? get_current_screen()->post_type : '';
		$extra     = array();

		foreach ( self::get_fields( $post_type ) as $key => $field ) {
			if ( $field['column'] ) {
				$extra[ 'mmpadel_' . $key ] = $field['label'];
			}
		}

		$position = array_search( 'title', array_keys( $columns ), true );
		$position = false === $position ? count( $columns ) : $position + 1;

		return array_slice( $columns, 0, $position, true ) + $extra + array_slice( $columns, $position, null, true );
	}

	/**
	 * Pinta el valor de una columna propia.
	 *
	 * @param string $column  Columna.
	 * @param int    $post_id ID.
	 */
	public static function render_column( $column, $post_id ) {
		if ( ! str_starts_with( $column, 'mmpadel_' ) ) {
			return;
		}
		$key    = substr( $column, strlen( 'mmpadel_' ) );
		$fields = self::get_fields( get_post_type( $post_id ) );

		if ( isset( $fields[ $key ] ) ) {
			$value = self::format( mmpadel_get_field( $key, $post_id ), $fields[ $key ] );
			echo '' !== $value ? esc_html( $value ) : '—';
		}
	}
}
