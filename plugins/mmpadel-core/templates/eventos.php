<?php
/**
 * Plantilla: listado de pools y eventos.
 *
 * @package MMPadel_Core
 *
 * @var array $args {
 *     @type array  $eventos Lista de eventos (ver MMPadel_Shortcodes::evento_data()).
 *     @type string $mostrar proximos|pasados|todos.
 * }
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $args['eventos'] ) ) {
	$mensaje = 'pasados' === $args['mostrar']
		? __( 'Todavía no hay resultados publicados.', 'mmpadel-core' )
		: __( 'Ahora mismo no hay pools ni eventos programados. Los anunciamos en el canal de plazas libres y en Instagram.', 'mmpadel-core' );
	echo '<p class="mmpadel-vacio">' . esc_html( $mensaje ) . '</p>';
	return;
}
?>
<ul class="mmpadel-eventos" role="list">
	<?php foreach ( $args['eventos'] as $evento ) : ?>
		<li class="mmpadel-evento<?php echo $evento['terminado'] ? ' mmpadel-evento--terminado' : ''; ?>">
			<?php if ( $evento['fecha'] ) : ?>
				<time class="mmpadel-evento__fecha" datetime="<?php echo esc_attr( $evento['fecha'] ); ?>">
					<span class="mmpadel-evento__dia"><?php echo esc_html( $evento['dia'] ); ?></span>
					<span class="mmpadel-evento__mes"><?php echo esc_html( $evento['mes'] ); ?></span>
				</time>
			<?php endif; ?>
			<div class="mmpadel-evento__texto">
				<h3 class="mmpadel-evento__titulo"><a href="<?php echo esc_url( $evento['url'] ); ?>"><?php echo esc_html( $evento['titulo'] ); ?></a></h3>
				<p class="mmpadel-evento__meta">
					<?php
					$meta = array_filter(
						array(
							$evento['fecha_texto'],
							$evento['hora'],
							/* translators: %s: nivel del evento. */
							$evento['nivel'] ? sprintf( __( 'Nivel: %s', 'mmpadel-core' ), $evento['nivel'] ) : '',
						)
					);
					echo esc_html( implode( ' · ', $meta ) );
					?>
				</p>
				<?php if ( $evento['resumen'] ) : ?>
					<p class="mmpadel-evento__resumen"><?php echo esc_html( $evento['resumen'] ); ?></p>
				<?php endif; ?>
				<p class="mmpadel-evento__acciones">
					<?php if ( $evento['inscripcion'] && ! $evento['terminado'] ) : ?>
						<a class="button button--cta" href="<?php echo esc_url( $evento['inscripcion'] ); ?>" target="_blank" rel="noopener" data-mmpadel-track="click_inscripcion_pool" data-mmpadel-evento="<?php echo esc_attr( $evento['titulo'] ); ?>"><?php esc_html_e( 'Inscribirme', 'mmpadel-core' ); ?></a>
					<?php endif; ?>
					<a class="button button--outline" href="<?php echo esc_url( $evento['url'] ); ?>">
						<?php echo esc_html( $evento['terminado'] && $evento['resultados'] ? __( 'Ver resultados', 'mmpadel-core' ) : __( 'Ver detalles', 'mmpadel-core' ) ); ?>
					</a>
				</p>
			</div>
		</li>
	<?php endforeach; ?>
</ul>
