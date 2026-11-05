<?php
/**
 * Cabecera de las píldoras (listado y temas): franja azul con el título, la
 * entradilla, los accesos a cada tema y tres cápsulas flotando.
 *
 * @package MMPadel
 *
 * @var array $args {
 *     @type string $antetitulo Texto pequeño sobre el título.
 *     @type string $titulo     Título (h1).
 *     @type string $intro      Entradilla.
 * }
 */

defined( 'ABSPATH' ) || exit;
?>
<header class="pildoras-hero">
	<div class="container pildoras-hero__inner">
		<div class="pildoras-hero__texto">
			<?php if ( ! empty( $args['antetitulo'] ) ) : ?>
				<p class="pildoras-hero__antetitulo"><?php echo esc_html( $args['antetitulo'] ); ?></p>
			<?php endif; ?>
			<h1 class="page-title"><?php echo esc_html( $args['titulo'] ); ?></h1>
			<?php if ( ! empty( $args['intro'] ) ) : ?>
				<p class="page-intro"><?php echo esc_html( $args['intro'] ); ?></p>
			<?php endif; ?>
			<?php mmpadel_theme_category_nav(); ?>
		</div>
		<div class="pildoras-hero__arte" aria-hidden="true">
			<span class="capsula capsula--1"></span>
			<span class="capsula capsula--2"></span>
			<span class="capsula capsula--3"></span>
		</div>
	</div>
</header>
