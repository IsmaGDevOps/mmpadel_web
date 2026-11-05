<?php
/**
 * Plantilla: tarifas agrupadas por tipo de clase.
 *
 * Con más de un tipo, arriba van accesos directos a cada grupo. Cada tarjeta
 * muestra las plazas del grupo como puntos, el precio grande y un botón al
 * formulario con el tipo de clase ya marcado. La tarifa con «Destacar» sale
 * resaltada como «Recomendada».
 *
 * @package MMPadel_Core
 *
 * @var array $args {
 *     @type array $grupos Lista de grupos: titulo, descripcion, ancla, url, tarifas[]
 *                         (titulo, tamano, plazas, precio, partes, periodo, notas, destacada).
 * }
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $args['grupos'] ) ) {
	echo '<p class="mmpadel-vacio">' . esc_html__( 'Estamos actualizando las tarifas. Escríbenos y te informamos.', 'mmpadel-core' ) . '</p>';
	return;
}
?>
<div class="mmpadel-tarifas">
	<?php if ( count( $args['grupos'] ) > 1 ) : ?>
		<nav class="mmpadel-tarifas__nav" aria-label="<?php esc_attr_e( 'Tipos de clase', 'mmpadel-core' ); ?>">
			<ul role="list">
				<?php foreach ( $args['grupos'] as $grupo ) : ?>
					<li>
						<a href="#<?php echo esc_attr( $grupo['ancla'] ); ?>">
							<?php echo esc_html( $grupo['titulo'] ); ?>
							<span class="mmpadel-tarifas__cuenta"><?php echo esc_html( number_format_i18n( count( $grupo['tarifas'] ) ) ); ?></span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</nav>
	<?php endif; ?>

	<?php foreach ( $args['grupos'] as $grupo ) : ?>
		<section class="mmpadel-tarifas__grupo" id="<?php echo esc_attr( $grupo['ancla'] ); ?>" aria-labelledby="<?php echo esc_attr( $grupo['ancla'] ); ?>-titulo">
			<header class="mmpadel-tarifas__cabecera">
				<h3 class="mmpadel-tarifas__tipo" id="<?php echo esc_attr( $grupo['ancla'] ); ?>-titulo"><?php echo esc_html( $grupo['titulo'] ); ?></h3>
				<?php if ( $grupo['descripcion'] ) : ?>
					<p class="mmpadel-tarifas__desc"><?php echo esc_html( $grupo['descripcion'] ); ?></p>
				<?php endif; ?>
			</header>

			<ul class="mmpadel-grid mmpadel-tarifas__lista" role="list">
				<?php foreach ( $grupo['tarifas'] as $tarifa ) : ?>
					<li class="mmpadel-tarifa<?php echo $tarifa['destacada'] ? ' mmpadel-tarifa--destacada' : ''; ?>">
						<?php if ( $tarifa['destacada'] ) : ?>
							<p class="mmpadel-tarifa__sello"><?php esc_html_e( 'Recomendada', 'mmpadel-core' ); ?></p>
						<?php endif; ?>

						<?php if ( $tarifa['tamano'] ) : ?>
							<p class="mmpadel-tarifa__tamano">
								<?php if ( $tarifa['plazas'] ) : ?>
									<span class="mmpadel-tarifa__plazas" aria-hidden="true"><?php echo str_repeat( '<span></span>', $tarifa['plazas'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML fijo. ?></span>
								<?php endif; ?>
								<?php echo esc_html( $tarifa['tamano'] ); ?>
							</p>
						<?php endif; ?>

						<h4 class="mmpadel-tarifa__titulo"><?php echo esc_html( $tarifa['titulo'] ); ?></h4>

						<?php if ( $tarifa['partes'] ) : ?>
							<p class="mmpadel-tarifa__precio">
								<strong class="mmpadel-tarifa__importe"><?php echo esc_html( $tarifa['partes']['entero'] ); ?><?php if ( $tarifa['partes']['decimales'] ) : ?><span class="mmpadel-tarifa__decimales"><?php echo esc_html( $tarifa['partes']['decimales'] ); ?></span><?php endif; ?><span class="mmpadel-tarifa__moneda"> <?php echo esc_html( $tarifa['partes']['moneda'] ); ?></span></strong>
								<?php if ( $tarifa['periodo'] ) : ?>
									<span class="mmpadel-tarifa__periodo"><?php echo esc_html( $tarifa['periodo'] ); ?></span>
								<?php endif; ?>
							</p>
						<?php endif; ?>

						<?php if ( $tarifa['notas'] ) : ?>
							<p class="mmpadel-tarifa__notas"><?php echo nl2br( esc_html( $tarifa['notas'] ) ); ?></p>
						<?php endif; ?>

						<?php if ( $grupo['url'] ) : ?>
							<p class="mmpadel-tarifa__accion">
								<a class="button <?php echo $tarifa['destacada'] ? 'button--cta' : 'button--outline'; ?>" href="<?php echo esc_url( $grupo['url'] ); ?>">
									<?php esc_html_e( 'Quiero esta clase', 'mmpadel-core' ); ?><span class="screen-reader-text">: <?php echo esc_html( $tarifa['titulo'] ); ?></span>
								</a>
							</p>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>
	<?php endforeach; ?>
</div>
