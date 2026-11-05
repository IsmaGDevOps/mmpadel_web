<?php
/**
 * Archivos: categorías, etiquetas, fechas y tipos de contenido con archivo.
 *
 * Las categorías son los temas de las píldoras y usan su mismo diseño
 * (cabecera azul con los temas, tarjetas y cierre).
 *
 * @package MMPadel
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="contenido" class="site-main">
	<?php if ( is_category() ) : ?>
		<?php
		$mmpadel_tema = get_queried_object();
		get_template_part(
			'template-parts/pildoras-cabecera',
			null,
			array(
				'antetitulo' => __( 'Píldoras MMPadel', 'mmpadel' ),
				'titulo'     => single_cat_title( '', false ),
				'intro'      => $mmpadel_tema->description
					? wp_strip_all_tags( $mmpadel_tema->description )
					/* translators: %s: nombre del tema. */
					: sprintf( __( 'Todas nuestras píldoras de %s.', 'mmpadel' ), mb_strtolower( $mmpadel_tema->name ) ),
			)
		);
		?>
		<div class="container pildoras-listado">
			<?php get_template_part( 'template-parts/loop' ); ?>
		</div>
		<?php get_template_part( 'template-parts/pildoras-instagram' ); ?>
	<?php else : ?>
		<div class="container">
			<header class="page-header">
				<h1 class="page-title"><?php echo esc_html( wp_strip_all_tags( get_the_archive_title() ) ); ?></h1>
				<?php the_archive_description( '<div class="page-intro">', '</div>' ); ?>
			</header>

			<?php get_template_part( 'template-parts/loop' ); ?>
		</div>
	<?php endif; ?>
</main>
<?php
get_footer();
