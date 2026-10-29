<?php
/**
 * Cabecera del sitio.
 *
 * @package MMPadel
 */

defined( 'ABSPATH' ) || exit;
?>
<!doctype html>
<html <?php language_attributes(); ?> class="no-js">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#contenido"><?php esc_html_e( 'Saltar al contenido', 'mmpadel' ); ?></a>

<header class="site-header">
	<div class="site-header__inner container">
		<div class="site-branding">
			<?php mmpadel_theme_branding(); ?>
		</div>

		<?php if ( has_nav_menu( 'principal' ) ) : ?>
			<button class="menu-toggle" type="button" aria-controls="navegacion-principal" aria-expanded="false">
				<span class="menu-toggle__icon" aria-hidden="true"></span>
				<span class="menu-toggle__label"><?php esc_html_e( 'Menú', 'mmpadel' ); ?></span>
			</button>

			<nav id="navegacion-principal" class="main-navigation" aria-label="<?php esc_attr_e( 'Principal', 'mmpadel' ); ?>">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'principal',
						'container'      => false,
						'menu_class'     => 'main-menu',
						'depth'          => 2,
						'fallback_cb'    => false,
					)
				);
				?>
			</nav>
		<?php endif; ?>

		<a class="button button--cta site-header__cta" href="<?php echo esc_url( mmpadel_theme_page_url( 'encuentra-tu-grupo' ) ); ?>">
			<?php esc_html_e( 'Encuentra tu grupo', 'mmpadel' ); ?>
		</a>
	</div>
</header>
