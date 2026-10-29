<?php
/**
 * Plantilla: bloque de contacto con los datos de Ajustes MMPadel.
 *
 * @package MMPadel_Core
 *
 * @var array $args Claves: direccion, mapa_url, horario, telefono, whatsapp, email_publico.
 */

defined( 'ABSPATH' ) || exit;

$whatsapp_url = mmpadel_get_whatsapp_url( $args['whatsapp'] );
$tel_url      = mmpadel_get_tel_url( $args['telefono'] );
?>
<div class="mmpadel-contacto">
	<dl class="mmpadel-ficha">
		<?php if ( $args['direccion'] ) : ?>
			<div>
				<dt><?php esc_html_e( 'Dónde estamos', 'mmpadel-core' ); ?></dt>
				<dd>
					<?php echo esc_html( $args['direccion'] ); ?>
					<?php if ( $args['mapa_url'] ) : ?>
						<br><a href="<?php echo esc_url( $args['mapa_url'] ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Cómo llegar (Google Maps)', 'mmpadel-core' ); ?></a>
					<?php endif; ?>
				</dd>
			</div>
		<?php endif; ?>
		<?php if ( $args['horario'] ) : ?>
			<div>
				<dt><?php esc_html_e( 'Horario', 'mmpadel-core' ); ?></dt>
				<dd><?php echo nl2br( esc_html( $args['horario'] ) ); ?></dd>
			</div>
		<?php endif; ?>
		<?php if ( $args['telefono'] ) : ?>
			<div>
				<dt><?php esc_html_e( 'Teléfono', 'mmpadel-core' ); ?></dt>
				<dd><a href="<?php echo esc_url( $tel_url, array( 'tel' ) ); ?>"><?php echo esc_html( $args['telefono'] ); ?></a></dd>
			</div>
		<?php endif; ?>
		<?php if ( $args['email_publico'] ) : ?>
			<div>
				<dt><?php esc_html_e( 'Email', 'mmpadel-core' ); ?></dt>
				<dd><a href="<?php echo esc_url( 'mailto:' . antispambot( $args['email_publico'] ) ); ?>"><?php echo esc_html( antispambot( $args['email_publico'] ) ); ?></a></dd>
			</div>
		<?php endif; ?>
	</dl>

	<?php if ( $whatsapp_url || $tel_url ) : ?>
		<p class="button-row">
			<?php if ( $whatsapp_url ) : ?>
				<a class="button button--cta" href="<?php echo esc_url( $whatsapp_url ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Escríbenos por WhatsApp', 'mmpadel-core' ); ?></a>
			<?php endif; ?>
			<?php if ( $tel_url ) : ?>
				<a class="button button--outline" href="<?php echo esc_url( $tel_url, array( 'tel' ) ); ?>"><?php esc_html_e( 'Llamar', 'mmpadel-core' ); ?></a>
			<?php endif; ?>
		</p>
	<?php endif; ?>
</div>
