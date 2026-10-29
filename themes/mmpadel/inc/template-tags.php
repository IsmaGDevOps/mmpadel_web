<?php
/**
 * Funciones de plantilla del tema.
 *
 * @package MMPadel
 */

defined( 'ABSPATH' ) || exit;

/**
 * Lee un ajuste de «Ajustes MMPadel» (plugin mmpadel-core).
 *
 * Si el plugin no está activo devuelve el valor por defecto, así el tema
 * nunca falla por su ausencia.
 *
 * @param string $key      Clave del ajuste (telefono, whatsapp, email, direccion…).
 * @param string $fallback Valor si no existe.
 * @return string
 */
function mmpadel_theme_option( $key, $fallback = '' ) {
	if ( function_exists( 'mmpadel_get_option' ) ) {
		$value = mmpadel_get_option( $key );
		return '' !== $value ? $value : $fallback;
	}
	return $fallback;
}

/**
 * Enlace tel: a partir de un teléfono escrito a mano («952 00 00 00»).
 *
 * @param string $phone Teléfono.
 * @return string URL tel: o cadena vacía.
 */
function mmpadel_theme_tel_url( $phone ) {
	$digits = preg_replace( '/[^0-9+]/', '', (string) $phone );
	if ( '' === $digits ) {
		return '';
	}
	if ( 9 === strlen( $digits ) ) {
		$digits = '+34' . $digits;
	}
	return 'tel:' . $digits;
}

/**
 * Enlace de WhatsApp (wa.me) a partir de un número.
 *
 * @param string $number Número con o sin prefijo.
 * @param string $text   Mensaje inicial opcional.
 * @return string URL o cadena vacía.
 */
function mmpadel_theme_whatsapp_url( $number, $text = '' ) {
	$digits = preg_replace( '/\D/', '', (string) $number );
	if ( '' === $digits ) {
		return '';
	}
	if ( 9 === strlen( $digits ) ) {
		$digits = '34' . $digits;
	}
	$url = 'https://wa.me/' . $digits;
	if ( '' !== $text ) {
		$url = add_query_arg( 'text', rawurlencode( $text ), $url );
	}
	return $url;
}

/**
 * URL de una imagen de assets/images/ del tema.
 *
 * @param string $file Nombre del archivo, p. ej. «logo.svg».
 * @return string
 */
function mmpadel_theme_image_url( $file ) {
	return get_theme_file_uri( 'assets/images/' . ltrim( $file, '/' ) );
}

/**
 * Foto de portada de una página, por orden de preferencia:
 * 1. Su imagen destacada (se cambia desde el editor).
 * 2. assets/images/portadas/{slug}.webp del tema (y {slug}-movil.webp para móvil).
 *
 * @param int|WP_Post|null $post Página; por defecto la actual.
 * @return array{grande: string, movil: string}|null URLs, o null si no tiene.
 */
function mmpadel_theme_cover( $post = null ) {
	$post = get_post( $post );
	if ( ! $post || 'page' !== $post->post_type ) {
		return null;
	}

	if ( has_post_thumbnail( $post ) ) {
		$id = get_post_thumbnail_id( $post );
		return array(
			'grande' => (string) wp_get_attachment_image_url( $id, '2048x2048' ),
			'movil'  => (string) wp_get_attachment_image_url( $id, 'large' ),
		);
	}

	$file = 'portadas/' . $post->post_name;
	if ( ! file_exists( get_theme_file_path( 'assets/images/' . $file . '.webp' ) ) ) {
		return null;
	}
	$movil = file_exists( get_theme_file_path( 'assets/images/' . $file . '-movil.webp' ) ) ? $file . '-movil' : $file;

	return array(
		'grande' => mmpadel_theme_image_url( $file . '.webp' ),
		'movil'  => mmpadel_theme_image_url( $movil . '.webp' ),
	);
}

/**
 * Atributo style con las variables CSS de la portada (--mmpadel-portada y
 * --mmpadel-portada-movil). Vacío si no hay portada.
 *
 * @param array|null $cover Resultado de mmpadel_theme_cover().
 * @return string Atributo ya escapado, con espacio inicial.
 */
function mmpadel_theme_cover_style( $cover ) {
	if ( ! $cover ) {
		return '';
	}
	$style = sprintf(
		'--mmpadel-portada:url("%1$s");--mmpadel-portada-movil:url("%2$s")',
		esc_url( $cover['grande'] ),
		esc_url( $cover['movil'] )
	);
	return ' style="' . esc_attr( $style ) . '"';
}

/**
 * URL de una página por su slug, con la ruta prevista como reserva.
 *
 * @param string $slug Slug de la página.
 * @return string
 */
function mmpadel_theme_page_url( $slug ) {
	$page = get_page_by_path( $slug );
	return $page ? get_permalink( $page ) : home_url( '/' . $slug . '/' );
}

/**
 * Logo del sitio, por orden de preferencia:
 * 1. El subido en Apariencia → Personalizar → Identidad del sitio.
 * 2. El del tema (assets/images/logo-blanco.webp), pensado para fondo azul.
 * 3. El nombre del sitio en texto.
 */
function mmpadel_theme_branding() {
	if ( has_custom_logo() ) {
		the_custom_logo();
		return;
	}

	$logo = 'logo-blanco.webp';
	if ( file_exists( get_theme_file_path( 'assets/images/' . $logo ) ) ) {
		printf(
			'<a class="site-logo" href="%1$s" rel="home"><img src="%2$s" width="665" height="228" alt="%3$s"></a>',
			esc_url( home_url( '/' ) ),
			esc_url( mmpadel_theme_image_url( $logo ) ),
			esc_attr( get_bloginfo( 'name' ) )
		);
		return;
	}

	printf(
		'<a class="site-title" href="%1$s" rel="home">%2$s</a>',
		esc_url( home_url( '/' ) ),
		esc_html( get_bloginfo( 'name' ) )
	);
}

/**
 * Datos de contacto del pie. Solo pinta los que estén rellenos en Ajustes MMPadel.
 */
function mmpadel_theme_contact_list() {
	$direccion = mmpadel_theme_option( 'direccion' );
	$horario   = mmpadel_theme_option( 'horario' );
	$telefono  = mmpadel_theme_option( 'telefono' );
	$whatsapp  = mmpadel_theme_option( 'whatsapp' );
	$email     = mmpadel_theme_option( 'email_publico' );
	$mapa      = mmpadel_theme_option( 'mapa_url' );

	if ( ! $direccion && ! $horario && ! $telefono && ! $whatsapp && ! $email ) {
		return;
	}
	?>
	<ul class="contact-list">
		<?php if ( $direccion ) : ?>
			<li>
				<span class="contact-list__label"><?php esc_html_e( 'Dirección', 'mmpadel' ); ?></span>
				<?php if ( $mapa ) : ?>
					<a href="<?php echo esc_url( $mapa ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $direccion ); ?></a>
				<?php else : ?>
					<?php echo esc_html( $direccion ); ?>
				<?php endif; ?>
			</li>
		<?php endif; ?>
		<?php if ( $horario ) : ?>
			<li>
				<span class="contact-list__label"><?php esc_html_e( 'Horario', 'mmpadel' ); ?></span>
				<?php echo nl2br( esc_html( $horario ) ); ?>
			</li>
		<?php endif; ?>
		<?php if ( $telefono ) : ?>
			<li>
				<span class="contact-list__label"><?php esc_html_e( 'Teléfono', 'mmpadel' ); ?></span>
				<a href="<?php echo esc_url( mmpadel_theme_tel_url( $telefono ), array( 'tel' ) ); ?>"><?php echo esc_html( $telefono ); ?></a>
			</li>
		<?php endif; ?>
		<?php if ( $whatsapp ) : ?>
			<li>
				<span class="contact-list__label"><?php esc_html_e( 'WhatsApp', 'mmpadel' ); ?></span>
				<a href="<?php echo esc_url( mmpadel_theme_whatsapp_url( $whatsapp ) ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $whatsapp ); ?></a>
			</li>
		<?php endif; ?>
		<?php if ( $email ) : ?>
			<li>
				<span class="contact-list__label"><?php esc_html_e( 'Email', 'mmpadel' ); ?></span>
				<a href="<?php echo esc_url( 'mailto:' . antispambot( $email ) ); ?>"><?php echo esc_html( antispambot( $email ) ); ?></a>
			</li>
		<?php endif; ?>
	</ul>
	<?php
}

/**
 * Reserva del menú legal: enlaza las páginas legales por slug si existen.
 */
function mmpadel_theme_legal_menu_fallback() {
	$pages = array(
		'aviso-legal' => __( 'Aviso legal', 'mmpadel' ),
		'privacidad'  => __( 'Privacidad', 'mmpadel' ),
		'cookies'     => __( 'Cookies', 'mmpadel' ),
	);

	$items = '';
	foreach ( $pages as $slug => $label ) {
		$page = get_page_by_path( $slug );
		if ( $page ) {
			$items .= sprintf( '<li><a href="%1$s">%2$s</a></li>', esc_url( get_permalink( $page ) ), esc_html( $label ) );
		}
	}

	if ( $items ) {
		echo '<ul class="legal-menu">' . $items . '</ul>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escapado arriba.
	}
}

/**
 * Fecha de publicación de una entrada.
 */
function mmpadel_theme_posted_on() {
	printf(
		'<time class="entry-date" datetime="%1$s">%2$s</time>',
		esc_attr( get_the_date( DATE_W3C ) ),
		esc_html( get_the_date() )
	);
}
