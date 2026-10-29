<?php
/**
 * Rejilla de tarjetas con paginación. Lo usan el blog, los archivos y la búsqueda.
 *
 * @package MMPadel
 */

defined( 'ABSPATH' ) || exit;

if ( have_posts() ) :
	?>
	<div class="card-grid">
		<?php
		while ( have_posts() ) :
			the_post();
			get_template_part( 'template-parts/content', 'card' );
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
