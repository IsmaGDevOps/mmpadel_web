<?php
/**
 * Pie del sitio: contacto, accesos rápidos y textos legales.
 *
 * @package MMPadel
 */

defined( 'ABSPATH' ) || exit;

$mmpadel_canal     = mmpadel_theme_option( 'canal_plazas_url' );
$mmpadel_software  = mmpadel_theme_option( 'software_url' );
$mmpadel_instagram = mmpadel_theme_option( 'instagram_url' );
$mmpadel_facebook  = mmpadel_theme_option( 'facebook_url' );
$mmpadel_resenas   = mmpadel_theme_option( 'google_resenas_url' );
?>
<footer class="site-footer">
	<svg class="site-footer__ball" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
		<circle cx="12" cy="12" r="10" />
		<path d="M5.6 4.4c3.1 3 3.1 12.2 0 15.2M18.4 4.4c-3.1 3-3.1 12.2 0 15.2" />
	</svg>

	<div class="site-footer__inner container">
		<section class="site-footer__col site-footer__brand">
			<?php mmpadel_theme_branding(); ?>
			<?php if ( get_bloginfo( 'description' ) ) : ?>
				<p class="site-footer__tagline"><?php echo esc_html( get_bloginfo( 'description' ) ); ?></p>
			<?php endif; ?>
			<?php if ( $mmpadel_instagram || $mmpadel_facebook ) : ?>
				<ul class="social-links">
					<?php if ( $mmpadel_instagram ) : ?>
						<li><a href="<?php echo esc_url( $mmpadel_instagram ); ?>" target="_blank" rel="noopener" aria-label="Instagram"><?php mmpadel_theme_icon( 'instagram' ); ?></a></li>
					<?php endif; ?>
					<?php if ( $mmpadel_facebook ) : ?>
						<li><a href="<?php echo esc_url( $mmpadel_facebook ); ?>" target="_blank" rel="noopener" aria-label="Facebook"><?php mmpadel_theme_icon( 'facebook' ); ?></a></li>
					<?php endif; ?>
				</ul>
			<?php endif; ?>
		</section>

		<section class="site-footer__col" aria-labelledby="pie-contacto">
			<h2 id="pie-contacto" class="site-footer__title"><?php esc_html_e( 'Contacto', 'mmpadel' ); ?></h2>
			<?php mmpadel_theme_contact_list(); ?>
		</section>

		<section class="site-footer__col" aria-labelledby="pie-enlaces">
			<h2 id="pie-enlaces" class="site-footer__title"><?php esc_html_e( 'Accesos rápidos', 'mmpadel' ); ?></h2>
			<ul class="footer-links">
				<li><a href="<?php echo esc_url( mmpadel_theme_page_url( 'encuentra-tu-grupo' ) ); ?>"><?php esc_html_e( 'Encuentra tu grupo', 'mmpadel' ); ?></a></li>
				<?php if ( $mmpadel_canal ) : ?>
					<li><a href="<?php echo esc_url( $mmpadel_canal ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Canal de plazas libres', 'mmpadel' ); ?></a></li>
				<?php endif; ?>
				<?php if ( $mmpadel_software ) : ?>
					<li><a href="<?php echo esc_url( $mmpadel_software ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Acceso alumnos', 'mmpadel' ); ?></a></li>
				<?php endif; ?>
				<?php if ( $mmpadel_resenas ) : ?>
					<li><a href="<?php echo esc_url( $mmpadel_resenas ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Opiniones en Google', 'mmpadel' ); ?></a></li>
				<?php endif; ?>
			</ul>
		</section>
	</div>

	<div class="site-footer__legal container">
		<p class="site-footer__copy">
			<?php
			/* translators: 1: año, 2: nombre del sitio. */
			printf( esc_html__( '© %1$s %2$s', 'mmpadel' ), esc_html( wp_date( 'Y' ) ), esc_html( get_bloginfo( 'name' ) ) );
			?>
		</p>
		<nav aria-label="<?php esc_attr_e( 'Legal', 'mmpadel' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'legal',
					'container'      => false,
					'menu_class'     => 'legal-menu',
					'depth'          => 1,
					'fallback_cb'    => 'mmpadel_theme_legal_menu_fallback',
				)
			);
			?>
		</nav>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
