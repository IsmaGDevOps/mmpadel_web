<?php
/**
 * Rejilla de tarjetas con paginación. Lo usan el blog, los archivos y la búsqueda.
 *
 * En la primera página de las píldoras y de cada tema, la entrada más
 * reciente sale destacada a todo el ancho.
 *
 * @package MMPadel
 */

defined( 'ABSPATH' ) || exit;

if ( have_posts() ) :
	$mmpadel_destacar = ( is_home() || is_category() ) && ! is_paged();
	$mmpadel_indice   = 0;
	?>
	<div class="card-grid">
		<?php
		while ( have_posts() ) :
			the_post();
			get_template_part( 'template-parts/content', 'card', array( 'destacada' => $mmpadel_destacar && 0 === $mmpadel_indice++ ) );
		endwhile;
		?>
	</div>
	<?php
	the_posts_pagination(
		array(
			'prev_text' => __( 'Anteriores', 'mmpadel' ),
			'next_text' => __( 'Siguientes', 'mmpadel' ),
		)
	);
else :
	get_template_part( 'template-parts/content', 'none' );
endif;
