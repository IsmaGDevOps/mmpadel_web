<?php
/**
 * Plantilla: tarifas agrupadas por tipo de clase.
 *
 * @package MMPadel_Core
 *
 * @var array $args {
 *     @type array $grupos Lista de grupos: titulo, descripcion, tarifas[] (titulo, tamano, precio, periodo, notas).
 * }
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $args['grupos'] ) ) {
	echo '<p class="mmpadel-vacio">' . esc_html__( 'Estamos actualizando las tarifas. Escríbenos y te informamos.', 'mmpadel-core' ) . '</p>';
	return;
}
?>
<div class="mmpadel-tarifas">
	<?php foreach ( $args['grupos'] as $grupo ) : ?>
		<section class="mmpadel-tarifas__grupo">
			<h3 class="mmpadel-tarifas__tipo"><?php echo esc_html( $grupo['titulo'] ); ?></h3>
			<?php if ( $grupo['descripcion'] ) : ?>
				<p class="mmpadel-tarifas__desc"><?php echo esc_html( $grupo['descripcion'] ); ?></p>
			<?php endif; ?>

			<ul class="mmpadel-grid mmpadel-tarifas__lista" role="list">
				<?php foreach ( $grupo['tarifas'] as $tarifa ) : ?>
					<li class="mmpadel-tarifa">
						<h4 class="mmpadel-tarifa__titulo"><?php echo esc_html( $tarifa['titulo'] ); ?></h4>
						<?php if ( $tarifa['tamano'] ) : ?>
							<p class="mmpadel-tarifa__tamano"><?php echo esc_html( $tarifa['tamano'] ); ?></p>
						<?php endif; ?>
						<?php if ( $tarifa['precio'] ) : ?>
							<p class="mmpadel-tarifa__precio">
								<strong><?php echo esc_html( $tarifa['precio'] ); ?></strong>
								<?php if ( $tarifa['periodo'] ) : ?>
									<span><?php echo esc_html( $tarifa['periodo'] ); ?></span>
								<?php endif; ?>
							</p>
						<?php endif; ?>
						<?php if ( $tarifa['notas'] ) : ?>
							<p class="mmpadel-tarifa__notas"><?php echo nl2br( esc_html( $tarifa['notas'] ) ); ?></p>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>
	<?php endforeach; ?>
</div>
