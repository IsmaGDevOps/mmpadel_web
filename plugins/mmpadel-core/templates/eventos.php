<?php
/**
 * Plantilla: listado de pools y eventos.
 *
 * Cada evento es una entrada: a la izquierda, la matriz con el día; a la
 * derecha, nivel, título, datos y botones, separados por un corte troquelado.
 * En los próximos, el primero va destacado y cada uno dice cuánto falta; en
 * los pasados, la primera línea de los resultados («Campeones: …»).
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

/**
 * Cuánto falta para un evento: «Hoy», «Mañana» o «Dentro de N días».
 *
 * @param int|null $dias Días que faltan.
 * @return string
 */
$mmpadel_cuenta = static function ( $dias ) {
	if ( null === $dias || $dias < 0 ) {
		return '';
	}
	if ( 0 === $dias ) {
		return __( 'Hoy', 'mmpadel-core' );
	}
	if ( 1 === $dias ) {
		return __( 'Mañana', 'mmpadel-core' );
	}
	/* translators: %s: número de días. */
	return sprintf( _n( 'Dentro de %s día', 'Dentro de %s días', $dias, 'mmpadel-core' ), number_format_i18n( $dias ) );
};
?>
<ul class="mmpadel-eventos mmpadel-eventos--<?php echo esc_attr( $args['mostrar'] ); ?>" role="list">
	<?php foreach ( $args['eventos'] as $evento ) : ?>
		<?php $cuenta = $evento['terminado'] ? '' : $mmpadel_cuenta( $evento['dias'] ); ?>
		<li class="mmpadel-evento<?php echo $evento['terminado'] ? ' mmpadel-evento--terminado' : ''; ?>">
			<?php if ( $evento['fecha'] ) : ?>
				<time class="mmpadel-evento__fecha" datetime="<?php echo esc_attr( $evento['fecha'] ); ?>">
					<span class="mmpadel-evento__semana"><?php echo esc_html( $evento['semana'] ); ?></span>
					<span class="mmpadel-evento__dia"><?php echo esc_html( $evento['dia'] ); ?></span>
					<span class="mmpadel-evento__mes"><?php echo esc_html( $evento['mes'] ); ?></span>
				</time>
			<?php endif; ?>
			<div class="mmpadel-evento__texto">
				<?php if ( $evento['nivel'] || $cuenta ) : ?>
					<p class="mmpadel-evento__etiquetas">
						<?php if ( $evento['nivel'] ) : ?>
							<span class="mmpadel-evento__nivel"><?php echo esc_html( $evento['nivel'] ); ?></span>
						<?php endif; ?>
						<?php if ( $cuenta ) : ?>
							<span class="mmpadel-evento__cuenta"><?php echo esc_html( $cuenta ); ?></span>
						<?php endif; ?>
					</p>
				<?php endif; ?>
				<h3 class="mmpadel-evento__titulo"><a href="<?php echo esc_url( $evento['url'] ); ?>"><?php echo esc_html( $evento['titulo'] ); ?></a></h3>
				<p class="mmpadel-evento__meta">
					<?php echo esc_html( implode( ' · ', array_filter( array( $evento['fecha_texto'], $evento['hora'] ) ) ) ); ?>
				</p>
				<?php if ( $evento['terminado'] && $evento['destacado'] ) : ?>
					<p class="mmpadel-evento__destacado"><?php echo esc_html( $evento['destacado'] ); ?></p>
				<?php elseif ( $evento['resumen'] ) : ?>
					<p class="mmpadel-evento__resumen"><?php echo esc_html( $evento['resumen'] ); ?></p>
				<?php endif; ?>
				<p class="mmpadel-evento__acciones">
					<?php if ( $evento['inscripcion'] && ! $evento['terminado'] ) : ?>
						<a class="button button--cta" href="<?php echo esc_url( $evento['inscripcion'] ); ?>" target="_blank" rel="noopener" data-mmpadel-track="click_inscripcion_pool" data-mmpadel-evento="<?php echo esc_attr( $evento['titulo'] ); ?>"><?php esc_html_e( 'Inscribirme', 'mmpadel-core' ); ?><span class="screen-reader-text">: <?php echo esc_html( $evento['titulo'] ); ?></span></a>
					<?php endif; ?>
					<a class="button button--outline" href="<?php echo esc_url( $evento['url'] ); ?>">
						<?php echo esc_html( $evento['terminado'] && $evento['resultados'] ? __( 'Ver resultados', 'mmpadel-core' ) : __( 'Ver detalles', 'mmpadel-core' ) ); ?><span class="screen-reader-text">: <?php echo esc_html( $evento['titulo'] ); ?></span>
					</a>
				</p>
			</div>
		</li>
	<?php endforeach; ?>
</ul>
