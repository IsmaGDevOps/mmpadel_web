/**
 * MMPadel – origen del visitante y eventos de dataLayer para GA4.
 *
 * 1. Origen: en el primer acceso de la sesión guarda los parámetros UTM, el
 *    referrer externo y la página de entrada en sessionStorage
 *    («mmpadel_origen») y los copia a los campos ocultos del formulario.
 * 2. Eventos (window.dataLayer; GA4 todavía no está instalado):
 *    - form_submit            al mostrarse la confirmación del formulario
 *    - click_whatsapp         enlaces a wa.me, api.whatsapp.com, chat/canales
 *    - click_phone            enlaces tel:
 *    - click_inscripcion_pool enlaces con data-mmpadel-track
 *    Los clics se escuchan en todo el documento, así que también cuentan los
 *    enlaces que se añadan desde el editor.
 */
( function () {
	'use strict';

	var STORAGE_KEY = 'mmpadel_origen';
	var UTM_KEYS = [ 'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content' ];

	window.dataLayer = window.dataLayer || [];

	function push( data ) {
		window.dataLayer.push( data );
	}

	// sessionStorage puede no estar disponible (modo privado, bloqueos): nunca debe romper la página.
	function readOrigin() {
		try {
			return JSON.parse( window.sessionStorage.getItem( STORAGE_KEY ) || 'null' );
		} catch ( e ) {
			return null;
		}
	}

	function writeOrigin( origin ) {
		try {
			window.sessionStorage.setItem( STORAGE_KEY, JSON.stringify( origin ) );
		} catch ( e ) {}
	}

	function hasUtm( origin ) {
		return !! origin && UTM_KEYS.some( function ( key ) {
			return !! origin[ key ];
		} );
	}

	function externalReferrer() {
		if ( ! document.referrer ) {
			return '';
		}
		try {
			return new URL( document.referrer ).host === window.location.host ? '' : document.referrer;
		} catch ( e ) {
			return '';
		}
	}

	/**
	 * Primer acceso de la sesión. Si se entró sin campaña y después llega una
	 * visita con UTM (otra pestaña desde Instagram, por ejemplo), gana la campaña.
	 */
	function captureOrigin() {
		var params = new URLSearchParams( window.location.search );
		var current = {
			referrer: externalReferrer(),
			landing: window.location.href,
		};
		UTM_KEYS.forEach( function ( key ) {
			current[ key ] = ( params.get( key ) || '' ).slice( 0, 150 );
		} );

		var stored = readOrigin();
		if ( ! stored || ( ! hasUtm( stored ) && hasUtm( current ) ) ) {
			writeOrigin( current );
			return current;
		}
		return stored;
	}

	function fillOriginFields( origin ) {
		if ( ! origin ) {
			return;
		}
		document.querySelectorAll( '[data-mmpadel-origen]' ).forEach( function ( input ) {
			var key = input.getAttribute( 'data-mmpadel-origen' );
			if ( Object.prototype.hasOwnProperty.call( origin, key ) ) {
				input.value = origin[ key ] || '';
			}
		} );
	}

	function linkText( link ) {
		return ( link.textContent || '' ).replace( /\s+/g, ' ' ).trim().slice( 0, 100 );
	}

	function onClick( event ) {
		var link = event.target.closest ? event.target.closest( 'a[href]' ) : null;
		if ( ! link ) {
			return;
		}

		var href = link.getAttribute( 'href' ) || '';
		var custom = link.getAttribute( 'data-mmpadel-track' );

		if ( custom ) {
			push( {
				event: custom,
				evento_nombre: link.getAttribute( 'data-mmpadel-evento' ) || '',
				link_url: link.href,
				link_text: linkText( link ),
			} );
			return;
		}

		if ( /^tel:/i.test( href ) ) {
			push( {
				event: 'click_phone',
				link_url: href,
				link_text: linkText( link ),
			} );
			return;
		}

		if ( /^https?:\/\/(wa\.me|api\.whatsapp\.com|chat\.whatsapp\.com|(www\.)?whatsapp\.com\/channel)\b/i.test( href ) ) {
			push( {
				event: 'click_whatsapp',
				whatsapp_tipo: /whatsapp\.com\/channel/i.test( href ) ? 'canal' : 'chat',
				link_url: link.href,
				link_text: linkText( link ),
			} );
		}
	}

	function onReady() {
		fillOriginFields( captureOrigin() );

		// Los campos se rellenan otra vez al enviar, por si el formulario se cargó antes que el script.
		document.addEventListener( 'submit', function ( event ) {
			if ( event.target.matches && event.target.matches( '.mmpadel-form' ) ) {
				fillOriginFields( readOrigin() );
			}
		} );

		var ok = document.querySelector( '[data-mmpadel-event="form_submit"]' );
		if ( ok ) {
			push( {
				event: 'form_submit',
				form_id: ok.getAttribute( 'data-mmpadel-form' ) || '',
				tipo_clase: ok.getAttribute( 'data-mmpadel-tipo-clase' ) || '',
			} );
			ok.focus();
		}

		// Tras un envío con errores, el foco va al resumen para que se lea primero.
		var summary = document.querySelector( '.mmpadel-form__resumen' );
		if ( summary ) {
			summary.focus();
		}
	}

	// Fase de captura: el evento se registra aunque otro script detenga la propagación.
	document.addEventListener( 'click', onClick, true );

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', onReady );
	} else {
		onReady();
	}
} )();
