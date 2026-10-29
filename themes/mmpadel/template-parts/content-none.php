<?php
/**
 * Mensaje cuando un listado no tiene resultados.
 *
 * @package MMPadel
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="no-results">
	<p><?php esc_html_e( 'Todavía no hay nada publicado aquí.', 'mmpadel' ); ?></p>
	<?php if ( is_search() ) : ?>
		<p><?php esc_html_e( 'Prueba con otras palabras:', 'mmpadel' ); ?></p>
		<?php get_search_form(); ?>
	<?php endif; ?>
</section>
