<?php
/**
 * Plantilla: bloque de contacto con los datos de Ajustes MMPadel.
 *
 * Tarjetas en mosaico: WhatsApp (grande, en azul), teléfono, email, horario
 * (una fila por línea del ajuste; «Lunes a viernes: 9:00–22:00» se parte en
 * días y horas) y dónde estamos, con un plano dibujado que lleva a Google
 * Maps. Cada tarjeta con enlace es toda ella el enlace. Las que no tienen
 * dato no salen.
 *
 * @package MMPadel_Core
 *
 * @var array $args Claves: direccion, mapa_url, horario, telefono, whatsapp, email_publico.
 */

defined( 'ABSPATH' ) || exit;

$whatsapp_url = mmpadel_get_whatsapp_url( $args['whatsapp'] );
$tel_url      = mmpadel_get_tel_url( $args['telefono'] );
$email        = $args['email_publico'] ? antispambot( $args['email_publico'] ) : '';

// Horario: «Días: horas» por línea. Se parte por el primer «: » (con espacio), para no cortar «9:00».
$horario = array();
foreach ( array_filter( array_map( 'trim', explode( "\n", (string) $args['horario'] ) ) ) as $linea ) {
	$partes    = explode( ': ', $linea, 2 );
	$horario[] = 2 === count( $partes ) ? $partes : array( $linea, '' );
}

/**
 * Icono de trazo de 24×24 (decorativo).
 *
 * @param string $nombre chat, telefono, email, reloj o pin.
 */
$icono = static function ( $nombre ) {
	$iconos = array(
		'chat'     => '<path d="M21 11.5a8.4 8.4 0 0 1-12.3 7.5L3 21l2-5.7A8.4 8.4 0 1 1 21 11.5z"/>',
		'telefono' => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/>',
		'email'    => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
		'reloj'    => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
		'pin'      => '<path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/>',
	);
	printf(
		'<span class="mmpadel-canal__icono" aria-hidden="true"><svg viewBox="0 0 24 24" focusable="false">%s</svg></span>',
		$iconos[ $nombre ] // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG fijo.
	);
};
?>
<div class="mmpadel-contacto">
	<?php if ( $whatsapp_url ) : ?>
		<a class="mmpadel-canal mmpadel-canal--whatsapp" href="<?php echo esc_url( $whatsapp_url ); ?>" target="_blank" rel="noopener">
			<?php $icono( 'chat' ); ?>
			<span class="mmpadel-canal__etiqueta"><?php esc_html_e( 'WhatsApp', 'mmpadel-core' ); ?></span>
			<strong class="mmpadel-canal__valor"><?php echo esc_html( $args['whatsapp'] ); ?></strong>
			<span class="mmpadel-canal__texto"><?php esc_html_e( 'Cuéntanos tu nivel y cuándo puedes jugar, y te proponemos grupo.', 'mmpadel-core' ); ?></span>
			<span class="mmpadel-canal__accion"><?php esc_html_e( 'Escríbenos', 'mmpadel-core' ); ?></span>
		</a>
	<?php endif; ?>

	<?php if ( $tel_url ) : ?>
		<a class="mmpadel-canal mmpadel-canal--telefono" href="<?php echo esc_url( $tel_url, array( 'tel' ) ); ?>">
			<?php $icono( 'telefono' ); ?>
			<span class="mmpadel-canal__etiqueta"><?php esc_html_e( 'Teléfono', 'mmpadel-core' ); ?></span>
			<strong class="mmpadel-canal__valor"><?php echo esc_html( $args['telefono'] ); ?></strong>
			<span class="mmpadel-canal__accion"><?php esc_html_e( 'Llámanos', 'mmpadel-core' ); ?></span>
		</a>
	<?php endif; ?>

	<?php if ( $email ) : ?>
		<a class="mmpadel-canal mmpadel-canal--email" href="<?php echo esc_url( 'mailto:' . $email ); ?>">
			<?php $icono( 'email' ); ?>
			<span class="mmpadel-canal__etiqueta"><?php esc_html_e( 'Email', 'mmpadel-core' ); ?></span>
			<strong class="mmpadel-canal__valor"><?php echo esc_html( $email ); ?></strong>
			<span class="mmpadel-canal__accion"><?php esc_html_e( 'Escríbenos un email', 'mmpadel-core' ); ?></span>
		</a>
	<?php endif; ?>

	<?php if ( $horario ) : ?>
		<div class="mmpadel-canal mmpadel-canal--horario">
			<?php $icono( 'reloj' ); ?>
			<span class="mmpadel-canal__etiqueta"><?php esc_html_e( 'Horario', 'mmpadel-core' ); ?></span>
			<dl class="mmpadel-horario">
				<?php foreach ( $horario as $fila ) : ?>
					<div>
						<dt><?php echo esc_html( $fila[0] ); ?></dt>
						<dd><?php echo esc_html( $fila[1] ); ?></dd>
					</div>
				<?php endforeach; ?>
			</dl>
		</div>
	<?php endif; ?>

	<?php if ( $args['direccion'] ) : ?>
		<?php
		$etiqueta = $args['mapa_url'] ? 'a' : 'div';
		$enlace   = $args['mapa_url'] ? ' href="' . esc_url( $args['mapa_url'] ) . '" target="_blank" rel="noopener"' : '';
		?>
		<<?php echo $etiqueta; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- «a» o «div». ?> class="mmpadel-canal mmpadel-canal--mapa"<?php echo $enlace; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escapado arriba. ?>>
			<span class="mmpadel-canal__datos">
				<?php $icono( 'pin' ); ?>
				<span class="mmpadel-canal__etiqueta"><?php esc_html_e( 'Dónde estamos', 'mmpadel-core' ); ?></span>
				<strong class="mmpadel-canal__valor"><?php echo esc_html( $args['direccion'] ); ?></strong>
				<?php if ( $args['mapa_url'] ) : ?>
					<span class="mmpadel-canal__accion"><?php esc_html_e( 'Cómo llegar', 'mmpadel-core' ); ?></span>
				<?php endif; ?>
			</span>
			<span class="mmpadel-plano" aria-hidden="true"><span class="mmpadel-plano__pin"></span></span>
		</<?php echo $etiqueta; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- «a» o «div». ?>>
	<?php endif; ?>
</div>
