/**
 * Cabecera y menú:
 * - La cabecera pasa a «cristal» al bajar (.is-scrolled).
 * - Menú móvil accesible: botón con aria-expanded, cierre con Escape y al
 *   pasar a escritorio.
 * - En escritorio, una bola de pádel descansa bajo la página actual y rueda
 *   (girando lo que le toca por la distancia) hasta la opción señalada.
 */
( function () {
	'use strict';

	const header = document.querySelector( '.site-header' );

	if ( header ) {
		const onScroll = () => header.classList.toggle( 'is-scrolled', window.scrollY > 8 );
		window.addEventListener( 'scroll', onScroll, { passive: true } );
		onScroll();
	}

	const toggle = document.querySelector( '.menu-toggle' );
	const nav = document.getElementById( 'navegacion-principal' );

	if ( ! toggle || ! nav ) {
		return;
	}

	// Mismo punto de corte que el menú horizontal en main.css.
	const desktop = window.matchMedia( '(min-width: 75em)' );

	/* Menú móvil ---------------------------------------------------- */

	const isOpen = () => toggle.getAttribute( 'aria-expanded' ) === 'true';

	const setOpen = ( open ) => {
		toggle.setAttribute( 'aria-expanded', String( open ) );
		document.body.classList.toggle( 'menu-abierto', open );
	};

	// Orden de entrada escalonada de las opciones (main.css usa --i).
	nav.querySelectorAll( '.main-menu > li' ).forEach( ( item, index ) => {
		item.style.setProperty( '--i', index );
	} );

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

	/* Bola que rueda (escritorio) ----------------------------------- */

	const menu = nav.querySelector( '.main-menu' );
	const bola = document.createElement( 'span' );
	bola.className = 'main-menu__bola';
	bola.setAttribute( 'aria-hidden', 'true' );
	nav.appendChild( bola );

	const actual = menu
		? menu.querySelector( ':scope > .current-menu-item > a, :scope > .current_page_parent > a, :scope > .current-menu-ancestor > a' )
		: null;

	let x = null;
	let giro = 0;

	const moverA = ( link, conTransicion = true ) => {
		if ( ! desktop.matches || ! link ) {
			bola.classList.remove( 'is-visible' );
			return;
		}

		const navBox = nav.getBoundingClientRect();
		const linkBox = link.getBoundingClientRect();
		const destino = linkBox.left - navBox.left + linkBox.width / 2;

		// Rodar sin resbalar: el giro es la distancia entre el perímetro.
		if ( x !== null && conTransicion ) {
			giro += ( ( destino - x ) / ( Math.PI * bola.offsetWidth ) ) * 360;
		}
		x = destino;

		bola.classList.toggle( 'sin-transicion', ! conTransicion );
		bola.style.transform = `translateX(calc(${ destino }px - 50%)) rotate(${ giro }deg)`;
		bola.classList.add( 'is-visible' );
	};

	const recolocar = () => moverA( actual, false );

	if ( menu ) {
		menu.addEventListener( 'mouseover', ( event ) => {
			const link = event.target.closest( '.main-menu > li > a' );
			if ( link ) {
				moverA( link );
			}
		} );

		menu.addEventListener( 'focusin', ( event ) => {
			const link = event.target.closest( '.main-menu > li > a' );
			if ( link ) {
				moverA( link );
			}
		} );

		menu.addEventListener( 'mouseleave', () => moverA( actual ) );

		menu.addEventListener( 'focusout', ( event ) => {
			if ( ! menu.contains( event.relatedTarget ) ) {
				moverA( actual );
			}
		} );
	}

	window.addEventListener( 'resize', recolocar );
	if ( document.fonts ) {
		document.fonts.ready.then( recolocar );
	}
	recolocar();

	desktop.addEventListener( 'change', ( event ) => {
		if ( event.matches ) {
			setOpen( false );
		}
		recolocar();
	} );
} )();
