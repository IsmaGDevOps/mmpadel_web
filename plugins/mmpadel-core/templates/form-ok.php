<?php
/**
 * Plantilla: solicitud enviada.
 *
 * El atributo data-mmpadel-event lo lee mmpadel-tracking.js para enviar el
 * evento form_submit a dataLayer. Esta vista solo aparece una vez por envío.
 *
 * @package MMPadel_Core
 *
 * @var array $args {
 *     @type string $nombre       Nombre del interesado.
 *     @type string $tipo_clase   Tipo de clase elegido (slug).
 *     @type string $canal_plazas URL del canal de plazas libres.
 * }
 */

defined( 'ABSPATH' ) || exit;

$nombre = $args['nombre'] ? preg_split( '/\s+/u', trim( $args['nombre'] ) )[0] : '';
?>
<div id="mmpadel-formulario" class="mmpadel-form-ok" role="status" tabindex="-1"
	data-mmpadel-event="form_submit"
	data-mmpadel-form="encuentra_tu_grupo"
	data-mmpadel-tipo-clase="<?php echo esc_attr( $args['tipo_clase'] ); ?>">
	<h2>
		<?php
		echo esc_html(
			$nombre
				/* translators: %s: nombre del interesado. */
				? sprintf( __( '¡Gracias, %s! Hemos recibido tu solicitud', 'mmpadel-core' ), $nombre )
				: __( '¡Gracias! Hemos recibido tu solicitud', 'mmpadel-core' )
		);
		?>
	</h2>
	<p><?php esc_html_e( 'La revisamos y te escribimos en 1-2 días laborables con una propuesta de grupo y horario. También te hemos enviado un email de confirmación.', 'mmpadel-core' ); ?></p>

	<?php if ( $args['canal_plazas'] ) : ?>
		<p><?php esc_html_e( 'Mientras tanto, únete al canal de plazas libres para enterarte antes que nadie de los huecos que quedan:', 'mmpadel-core' ); ?></p>
		<p><a class="button button--cta" href="<?php echo esc_url( $args['canal_plazas'] ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Unirme al canal de plazas libres', 'mmpadel-core' ); ?></a></p>
	<?php endif; ?>

	<p><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Volver al inicio', 'mmpadel-core' ); ?></a></p>
</div>
