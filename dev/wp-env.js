#!/usr/bin/env node
/**
 * Lanzador de @wordpress/env.
 *
 * wp-env crea dentro de los contenedores un usuario con el mismo nombre que el
 * usuario del equipo (os.userInfo().username). En Windows ese nombre puede
 * llevar espacios o mayúsculas («Despacho 1»), adduser lo rechaza y la imagen
 * no llega a construirse. Aquí normalizamos el nombre antes de cargar wp-env.
 * Con nombres válidos no cambia nada.
 */
'use strict';

const os = require( 'os' );

const userInfo = os.userInfo;

os.userInfo = function ( ...args ) {
	const info = userInfo.apply( os, args );
	const safe = String( info.username )
		.toLowerCase()
		.replace( /[^a-z0-9_-]+/g, '-' )
		.replace( /^[^a-z_]+/, '' )
		.replace( /-+$/, '' );

	return { ...info, username: safe || 'wpenv' };
};

// Igual que node_modules/@wordpress/env/bin/wp-env.
const command = process.argv.slice( 2 );
require( '@wordpress/env/lib/cli.js' )().parse( command.length ? command : [ '--help' ] );
