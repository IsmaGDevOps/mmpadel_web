<?php
/**
 * Plantilla: fichas de entrenadores.
 *
 * Cada ficha es un cromo: foto (o iniciales sobre las líneas de una pista)
 * con el nombre y la especialidad encima, la experiencia en una lista con
 * vistos (una línea del campo, un punto) y el enfoque como cita.
 *
 * @package MMPadel_Core
 *
 * @var array $args {
 *     @type array $entrenadores Lista: nombre, foto (HTML de la imagen), iniciales, especialidad, experiencia, enfoque.
 * }
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $args['entrenadores'] ) ) {
	return;
}
?>
<ul class="mmpadel-grid mmpadel-equipo" role="list">
	<?php foreach ( $args['entrenadores'] as $entrenador ) : ?>
		<?php $experiencia = array_filter( array_map( 'trim', explode( "\n", $entrenador['experiencia'] ) ) ); ?>
		<li class="mmpadel-entrenador">
			<div class="mmpadel-entrenador__foto<?php echo $entrenador['foto'] ? '' : ' mmpadel-entrenador__foto--sin-foto'; ?>">
				<?php if ( $entrenador['foto'] ) : ?>
					<?php echo wp_kses_post( $entrenador['foto'] ); ?>
				<?php else : ?>
					<span class="mmpadel-entrenador__iniciales" aria-hidden="true"><?php echo esc_html( $entrenador['iniciales'] ); ?></span>
				<?php endif; ?>
				<div class="mmpadel-entrenador__cabecera">
					<h3 class="mmpadel-entrenador__nombre"><?php echo esc_html( $entrenador['nombre'] ); ?></h3>
					<?php if ( $entrenador['especialidad'] ) : ?>
						<p class="mmpadel-entrenador__especialidad"><?php echo esc_html( $entrenador['especialidad'] ); ?></p>
					<?php endif; ?>
				</div>
			</div>
			<?php if ( $experiencia || $entrenador['enfoque'] ) : ?>
				<div class="mmpadel-entrenador__texto">
					<?php if ( $experiencia ) : ?>
						<h4 class="mmpadel-entrenador__etiqueta"><?php esc_html_e( 'Experiencia', 'mmpadel-core' ); ?></h4>
						<ul class="mmpadel-entrenador__experiencia" role="list">
							<?php foreach ( $experiencia as $linea ) : ?>
								<li><?php echo esc_html( $linea ); ?></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
					<?php if ( $entrenador['enfoque'] ) : ?>
						<figure class="mmpadel-entrenador__enfoque">
							<figcaption class="mmpadel-entrenador__etiqueta"><?php esc_html_e( 'Cómo enseña', 'mmpadel-core' ); ?></figcaption>
							<blockquote><p><?php echo nl2br( esc_html( $entrenador['enfoque'] ) ); ?></p></blockquote>
						</figure>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</li>
	<?php endforeach; ?>
</ul>
