<?php
/**
 * Integración de las solicitudes con las herramientas de privacidad de
 * WordPress (Herramientas → Exportar / Borrar datos personales), para
 * atender los derechos de acceso y supresión del RGPD por email.
 *
 * @package MMPadel_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Exportador y borrador de datos personales.
 */
final class MMPadel_Privacy {

	/**
	 * Engancha los filtros.
	 */
	public static function init() {
		add_filter( 'wp_privacy_personal_data_exporters', array( __CLASS__, 'register_exporter' ) );
		add_filter( 'wp_privacy_personal_data_erasers', array( __CLASS__, 'register_eraser' ) );
	}

	/**
	 * Registra el exportador.
	 *
	 * @param array $exporters Exportadores.
	 * @return array
	 */
	public static function register_exporter( $exporters ) {
		$exporters['mmpadel-solicitudes'] = array(
			'exporter_friendly_name' => __( 'Solicitudes «Encuentra tu grupo»', 'mmpadel-core' ),
			'callback'               => array( __CLASS__, 'export' ),
		);
		return $exporters;
	}

	/**
	 * Registra el borrador.
	 *
	 * @param array $erasers Borradores.
	 * @return array
	 */
	public static function register_eraser( $erasers ) {
		$erasers['mmpadel-solicitudes'] = array(
			'eraser_friendly_name' => __( 'Solicitudes «Encuentra tu grupo»', 'mmpadel-core' ),
			'callback'             => array( __CLASS__, 'erase' ),
		);
		return $erasers;
	}

	/**
	 * Solicitudes de un email.
	 *
	 * @param string $email Email.
	 * @return int[]
	 */
	private static function find( $email ) {
		return get_posts(
			array(
				'post_type'      => MMPadel_Post_Types::SOLICITUD,
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'meta_key'       => MMPadel_Form::META_PREFIX . 'email', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- uso puntual.
				'meta_value'     => $email, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- uso puntual.
			)
		);
	}

	/**
	 * Exporta las solicitudes de un email.
	 *
	 * @param string $email Email.
	 * @return array
	 */
	public static function export( $email ) {
		$schema = MMPadel_Form::schema();
		$items  = array();

		foreach ( self::find( $email ) as $id ) {
			$data = array(
				array(
					'name'  => __( 'Recibida', 'mmpadel-core' ),
					'value' => get_the_date( 'Y-m-d H:i', $id ),
				),
			);
			foreach ( $schema as $key => $field ) {
				$data[] = array(
					'name'  => $field['short'],
					'value' => MMPadel_Form::display_value( $key, MMPadel_Requests_Admin::get( $id, $key ), $schema ),
				);
			}
			$data[] = array(
				'name'  => __( 'Consentimiento', 'mmpadel-core' ),
				'value' => MMPadel_Requests_Admin::get( $id, 'privacidad' ),
			);

			$items[] = array(
				'group_id'    => 'mmpadel-solicitudes',
				'group_label' => __( 'Solicitudes «Encuentra tu grupo»', 'mmpadel-core' ),
				'item_id'     => 'mmpadel-solicitud-' . $id,
				'data'        => $data,
			);
		}

		return array(
			'data' => $items,
			'done' => true,
		);
	}

	/**
	 * Borra las solicitudes de un email.
	 *
	 * @param string $email Email.
	 * @return array
	 */
	public static function erase( $email ) {
		$removed = false;
		foreach ( self::find( $email ) as $id ) {
			$removed = (bool) wp_delete_post( $id, true ) || $removed;
		}

		return array(
			'items_removed'  => $removed,
			'items_retained' => false,
			'messages'       => array(),
			'done'           => true,
		);
	}
}
