<?php
/**
 * Formulario de búsqueda.
 *
 * @package MMPadel
 */

defined( 'ABSPATH' ) || exit;

$mmpadel_search_id = wp_unique_id( 'buscar-' );
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $mmpadel_search_id ); ?>"><?php esc_html_e( 'Buscar', 'mmpadel' ); ?></label>
	<input type="search" id="<?php echo esc_attr( $mmpadel_search_id ); ?>" class="search-form__input" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Buscar…', 'mmpadel' ); ?>">
	<button type="submit" class="button"><?php esc_html_e( 'Buscar', 'mmpadel' ); ?></button>
</form>
