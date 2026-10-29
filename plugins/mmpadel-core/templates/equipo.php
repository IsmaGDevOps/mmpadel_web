<?php
/**
 * Plantilla: fichas de entrenadores.
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
		<li class="mmpadel-entrenador">
			<div class="mmpadel-entrenador__foto">
				<?php if ( $entrenador['foto'] ) : ?>
					<?php echo wp_kses_post( $entrenador['foto'] ); ?>
				<?php else : ?>
					<span class="mmpadel-entrenador__iniciales" aria-hidden="true"><?php echo esc_html( $entrenador['iniciales'] ); ?></span>
				<?php endif; ?>
			</div>
			<div class="mmpadel-entrenador__texto">
				<h3 class="mmpadel-entrenador__nombre"><?php echo esc_html( $entrenador['nombre'] ); ?></h3>
				<?php if ( $entrenador['especialidad'] ) : ?>
					<p class="mmpadel-entrenador__especialidad"><?php echo esc_html( $entrenador['especialidad'] ); ?></p>
				<?php endif; ?>
				<?php if ( $entrenador['experiencia'] ) : ?>
					<h4><?php esc_html_e( 'Experiencia', 'mmpadel-core' ); ?></h4>
					<p><?php echo nl2br( esc_html( $entrenador['experiencia'] ) ); ?></p>
				<?php endif; ?>
				<?php if ( $entrenador['enfoque'] ) : ?>
					<h4><?php esc_html_e( 'Cómo enseña', 'mmpadel-core' ); ?></h4>
					<p><?php echo nl2br( esc_html( $entrenador['enfoque'] ) ); ?></p>
				<?php endif; ?>
			</div>
		</li>
	<?php endforeach; ?>
</ul>
