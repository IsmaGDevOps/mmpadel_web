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
 * Pinta un icono SVG de trazo (24×24, color del texto). Decorativo: el texto
 * que lo acompaña es el que da el significado.
 *
 * @param string $name pin, reloj, telefono, chat, email, instagram o facebook.
 */
function mmpadel_theme_icon( $name ) {
	$icons = array(
		'pin'       => '<path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/>',
		'reloj'     => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
		'telefono'  => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/>',
		'chat'      => '<path d="M21 11.5a8.4 8.4 0 0 1-12.3 7.5L3 21l2-5.7A8.4 8.4 0 1 1 21 11.5z"/>',
		'email'     => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
		'instagram' => '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><path d="M17.5 6.5h.01"/>',
		'facebook'  => '<path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>',
	);
	if ( ! isset( $icons[ $name ] ) ) {
		return;
	}
	echo '<svg class="icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">' . $icons[ $name ] . '</svg>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- literal.
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

	$tel_url      = mmpadel_theme_tel_url( $telefono );
	$whatsapp_url = mmpadel_theme_whatsapp_url( $whatsapp );
	// Mismo número para llamar y para WhatsApp: una sola fila.
	$mismo_numero = $tel_url && $whatsapp_url && preg_replace( '/\D/', '', $tel_url ) === preg_replace( '/\D/', '', $whatsapp_url );
	?>
	<ul class="contact-list">
		<?php if ( $direccion ) : ?>
			<li>
				<span class="contact-list__icon"><?php mmpadel_theme_icon( 'pin' ); ?></span>
				<div>
					<span class="contact-list__label"><?php esc_html_e( 'Dirección', 'mmpadel' ); ?></span>
					<?php if ( $mapa ) : ?>
						<a href="<?php echo esc_url( $mapa ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $direccion ); ?></a>
					<?php else : ?>
						<?php echo esc_html( $direccion ); ?>
					<?php endif; ?>
				</div>
			</li>
		<?php endif; ?>
		<?php if ( $horario ) : ?>
			<li>
				<span class="contact-list__icon"><?php mmpadel_theme_icon( 'reloj' ); ?></span>
				<div>
					<span class="contact-list__label"><?php esc_html_e( 'Horario', 'mmpadel' ); ?></span>
					<?php echo nl2br( esc_html( $horario ) ); ?>
				</div>
			</li>
		<?php endif; ?>
		<?php if ( $mismo_numero ) : ?>
			<li>
				<span class="contact-list__icon"><?php mmpadel_theme_icon( 'telefono' ); ?></span>
				<div>
					<span class="contact-list__label"><?php esc_html_e( 'Teléfono y WhatsApp', 'mmpadel' ); ?></span>
					<a href="<?php echo esc_url( $tel_url, array( 'tel' ) ); ?>"><?php echo esc_html( $telefono ); ?></a>
					<a class="contact-list__pill" href="<?php echo esc_url( $whatsapp_url ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'WhatsApp', 'mmpadel' ); ?></a>
				</div>
			</li>
		<?php else : ?>
			<?php if ( $tel_url ) : ?>
				<li>
					<span class="contact-list__icon"><?php mmpadel_theme_icon( 'telefono' ); ?></span>
					<div>
						<span class="contact-list__label"><?php esc_html_e( 'Teléfono', 'mmpadel' ); ?></span>
						<a href="<?php echo esc_url( $tel_url, array( 'tel' ) ); ?>"><?php echo esc_html( $telefono ); ?></a>
					</div>
				</li>
			<?php endif; ?>
			<?php if ( $whatsapp_url ) : ?>
				<li>
					<span class="contact-list__icon"><?php mmpadel_theme_icon( 'chat' ); ?></span>
					<div>
						<span class="contact-list__label"><?php esc_html_e( 'WhatsApp', 'mmpadel' ); ?></span>
						<a href="<?php echo esc_url( $whatsapp_url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $whatsapp ); ?></a>
					</div>
				</li>
			<?php endif; ?>
		<?php endif; ?>
		<?php if ( $email ) : ?>
			<li>
				<span class="contact-list__icon"><?php mmpadel_theme_icon( 'email' ); ?></span>
				<div>
					<span class="contact-list__label"><?php esc_html_e( 'Email', 'mmpadel' ); ?></span>
					<a href="<?php echo esc_url( 'mailto:' . antispambot( $email ) ); ?>"><?php echo esc_html( antispambot( $email ) ); ?></a>
				</div>
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
 * Minutos de lectura de una entrada (200 palabras por minuto, mínimo 1).
 *
 * @param int|WP_Post|null $post Entrada; la actual si se omite.
 * @return string «3 min de lectura».
 */
function mmpadel_theme_reading_time( $post = null ) {
	$text    = wp_strip_all_tags( strip_shortcodes( (string) get_post_field( 'post_content', $post ) ) );
	$words   = count( preg_split( '/\s+/u', trim( $text ), -1, PREG_SPLIT_NO_EMPTY ) );
	$minutes = max( 1, (int) ceil( $words / 200 ) );

	/* translators: %s: minutos. */
	return sprintf( __( '%s min de lectura', 'mmpadel' ), number_format_i18n( $minutes ) );
}

/**
 * Tema principal de una píldora: su primera categoría que no sea la de por
 * defecto («Sin categoría»), o esa si no tiene otra.
 *
 * @param int|WP_Post|null $post Entrada; la actual si se omite.
 * @return WP_Term|null
 */
function mmpadel_theme_post_category( $post = null ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return null;
	}
	$categories = get_the_category( $post->ID );
	foreach ( $categories as $category ) {
		if ( (int) get_option( 'default_category' ) !== $category->term_id ) {
			return $category;
		}
	}
	return $categories ? $categories[0] : null;
}

/**
 * Si una entrada lleva vídeo: bloque de vídeo o de inserción, o un enlace de
 * Instagram, YouTube, Vimeo o TikTok en el contenido.
 *
 * @param int|WP_Post|null $post Entrada; la actual si se omite.
 * @return bool
 */
function mmpadel_theme_post_has_video( $post = null ) {
	$content = (string) get_post_field( 'post_content', $post );
	return (bool) preg_match( '#<!-- wp:(video|embed)|https?://(www\.)?(instagram\.com|youtube\.com|youtu\.be|vimeo\.com|tiktok\.com)/#i', $content );
}

/**
 * URL del listado de píldoras (la página de entradas, o la portada si no hay).
 *
 * @return string
 */
function mmpadel_theme_blog_url() {
	$page = (int) get_option( 'page_for_posts' );
	return $page ? (string) get_permalink( $page ) : home_url( '/' );
}

/**
 * Accesos a los temas de las píldoras: «Todas» y una píldora por categoría
 * con entradas, con el número de cada una. Solo si hay al menos dos temas.
 */
function mmpadel_theme_category_nav() {
	$categories = get_categories(
		array(
			'hide_empty' => true,
			'orderby'    => 'count',
			'order'      => 'DESC',
		)
	);
	if ( count( $categories ) < 2 ) {
		return;
	}

	$current = is_category() ? get_queried_object_id() : 0;

	echo '<nav class="pildoras-temas" aria-label="' . esc_attr__( 'Temas de las píldoras', 'mmpadel' ) . '"><ul role="list">';
	printf(
		'<li><a href="%1$s"%2$s>%3$s</a></li>',
		esc_url( mmpadel_theme_blog_url() ),
		$current ? '' : ' aria-current="page"',
		esc_html__( 'Todas', 'mmpadel' )
	);
	foreach ( $categories as $category ) {
		printf(
			'<li><a href="%1$s"%2$s>%3$s <span class="pildoras-temas__cuenta">%4$s</span></a></li>',
			esc_url( get_category_link( $category ) ),
			$current === $category->term_id ? ' aria-current="page"' : '',
			esc_html( $category->name ),
			esc_html( number_format_i18n( $category->count ) )
		);
	}
	echo '</ul></nav>';
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
