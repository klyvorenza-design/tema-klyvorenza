/* Carrossel simples (glossário da home): setas, teclado e arrastar com o dedo (scroll-snap). */
( function () {
	'use strict';
	document.querySelectorAll( '.klyv-car' ).forEach( function ( root ) {
		var track = root.querySelector( '.klyv-car__track' );
		var prev = root.querySelector( '.klyv-car__prev' );
		var next = root.querySelector( '.klyv-car__next' );
		if ( ! track ) { return; }
		function step() { return Math.max( 200, track.clientWidth * 0.9 ); }
		function sync() {
			var max = track.scrollWidth - track.clientWidth - 2;
			if ( prev ) { prev.disabled = track.scrollLeft <= 2; }
			if ( next ) { next.disabled = track.scrollLeft >= max; }
		}
		if ( prev ) { prev.addEventListener( 'click', function () { track.scrollBy( { left: -step(), behavior: 'smooth' } ); } ); }
		if ( next ) { next.addEventListener( 'click', function () { track.scrollBy( { left: step(), behavior: 'smooth' } ); } ); }
		track.addEventListener( 'keydown', function ( e ) {
			if ( e.key === 'ArrowRight' ) { e.preventDefault(); track.scrollBy( { left: step(), behavior: 'smooth' } ); }
			if ( e.key === 'ArrowLeft' ) { e.preventDefault(); track.scrollBy( { left: -step(), behavior: 'smooth' } ); }
		} );
		var raf;
		track.addEventListener( 'scroll', function () { cancelAnimationFrame( raf ); raf = requestAnimationFrame( sync ); }, { passive: true } );
		window.addEventListener( 'resize', sync );
		sync();
	} );
} )();
