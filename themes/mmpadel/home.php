<?php
/**
 * Blog (Píldoras MMPadel): franja de cabecera con los temas, la última
 * píldora destacada, el resto en tarjetas y el cierre con Instagram.
 *
 * El título sale de la página de entradas; la entradilla, de su extracto
 * (o un texto por defecto si no tiene).
 *
 * @package MMPadel
 */

defined( 'ABSPATH' ) || exit;

$mmpadel_blog_page = (int) get_option( 'page_for_posts' );

get_header();
?>
<main id="contenido" class="site-main">
	<?php
	get_template_part(
		'template-parts/pildoras-cabecera',
		null,
		array(
			'antetitulo' => __( 'Aprende entre clase y clase', 'mmpadel' ),
			'titulo'     => $mmpadel_blog_page ? get_the_title( $mmpadel_blog_page ) : __( 'Píldoras MMPadel', 'mmpadel' ),
			'intro'      => $mmpadel_blog_page && has_excerpt( $mmpadel_blog_page )
				? get_the_excerpt( $mmpadel_blog_page )
				: __( 'Consejos breves de técnica, táctica y material, explicados por nuestros entrenadores.', 'mmpadel' ),
		)
	);
	?>

	<div class="container pildoras-listado">
		<?php get_template_part( 'template-parts/loop' ); ?>
	</div>

	<?php get_template_part( 'template-parts/pildoras-instagram' ); ?>
</main>
<?php
get_footer();
