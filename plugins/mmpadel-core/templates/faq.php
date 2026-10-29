<?php
/**
 * Plantilla: preguntas frecuentes en desplegables (<details>, sin JavaScript).
 *
 * @package MMPadel_Core
 *
 * @var array $args {
 *     @type array $grupos         Lista de grupos: titulo, preguntas[] (pregunta, respuesta en HTML).
 *     @type bool  $con_categorias Mostrar el título de cada categoría.
 * }
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $args['grupos'] ) ) {
	return;
}
?>
<div class="mmpadel-faq">
	<?php foreach ( $args['grupos'] as $grupo ) : ?>
		<section class="mmpadel-faq__grupo">
			<?php if ( $args['con_categorias'] ) : ?>
				<h3 class="mmpadel-faq__categoria"><?php echo esc_html( $grupo['titulo'] ); ?></h3>
			<?php endif; ?>
			<?php foreach ( $grupo['preguntas'] as $item ) : ?>
				<details class="mmpadel-faq__item">
					<summary><?php echo esc_html( $item['pregunta'] ); ?></summary>
					<div class="mmpadel-faq__respuesta"><?php echo wp_kses_post( $item['respuesta'] ); ?></div>
				</details>
			<?php endforeach; ?>
		</section>
	<?php endforeach; ?>
</div>
