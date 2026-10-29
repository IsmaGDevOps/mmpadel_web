/**
 * Menú móvil accesible: botón con aria-expanded, cierre con Escape y al
 * pasar a escritorio.
 */
( function () {
	'use strict';

	const toggle = document.querySelector( '.menu-toggle' );
	const nav = document.getElementById( 'navegacion-principal' );

	if ( ! toggle || ! nav ) {
		return;
	}

	const isOpen = () => toggle.getAttribute( 'aria-expanded' ) === 'true';

	const setOpen = ( open ) => {
		toggle.setAttribute( 'aria-expanded', String( open ) );
		document.body.classList.toggle( 'menu-abierto', open );
	};

	toggle.addEventListener( 'click', () => setOpen( ! isOpen() ) );

	document.addEventListener( 'keydown', ( event ) => {
		if ( event.key === 'Escape' && isOpen() ) {
			setOpen( false );
			toggle.focus();
		}
	} );

	// Al pulsar un enlace del menú (p. ej. un ancla de la misma página), se cierra.
	nav.addEventListener( 'click', ( event ) => {
		if ( event.target.closest( 'a' ) ) {
			setOpen( false );
		}
	} );

	// Mismo punto de corte que el menú horizontal en main.css.
	window.matchMedia( '(min-width: 75em)' ).addEventListener( 'change', ( event ) => {
		if ( event.matches ) {
			setOpen( false );
		}
	} );
} )();
