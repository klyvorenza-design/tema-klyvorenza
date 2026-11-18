/* Slide de destaques: scroll-snap, setas, bolinhas, autoplay, pausa e teclado. */
( function () {
	'use strict';
	var root = document.querySelector( '.klyv-slide' );
	if ( ! root ) { return; }
	var track = root.querySelector( '.klyv-slide__track' );
	var items = root.querySelectorAll( '.klyv-slide__item' );
	var dots = root.querySelectorAll( '.klyv-slide__dots button' );
	var n = items.length, cur = 0, timer = null, paused = false;
	var reduce = matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	function go( i, smooth ) {
		cur = ( i + n ) % n;
		track.scrollTo( { left: items[ cur ].offsetLeft, behavior: smooth === false || reduce ? 'auto' : 'smooth' } );
	}
	function mark() {
		dots.forEach( function ( b, i ) { if ( i === cur ) { b.setAttribute( 'aria-current', 'true' ); } else { b.removeAttribute( 'aria-current' ); } } );
	}
	var raf;
	track.addEventListener( 'scroll', function () {
		cancelAnimationFrame( raf );
		raf = requestAnimationFrame( function () {
			var i = Math.round( track.scrollLeft / track.clientWidth );
			if ( i !== cur && i >= 0 && i < n ) { cur = i; mark(); }
		} );
	}, { passive: true } );
	var prev = root.querySelector( '.klyv-slide__prev' ), next = root.querySelector( '.klyv-slide__next' );
	if ( prev ) { prev.addEventListener( 'click', function () { go( cur - 1 ); } ); }
	if ( next ) { next.addEventListener( 'click', function () { go( cur + 1 ); } ); }
	dots.forEach( function ( b, i ) { b.addEventListener( 'click', function () { go( i ); } ); } );
	track.addEventListener( 'keydown', function ( e ) {
		if ( e.key === 'ArrowRight' ) { e.preventDefault(); go( cur + 1 ); }
		if ( e.key === 'ArrowLeft' ) { e.preventDefault(); go( cur - 1 ); }
	} );
	function stop() { clearInterval( timer ); timer = null; }
	function play() {
		stop();
		if ( root.dataset.auto !== '1' || reduce || paused || n < 2 || document.hidden ) { return; }
		timer = setInterval( function () { go( cur + 1 ); }, parseInt( root.dataset.interval, 10 ) || 6000 );
	}
	[ 'mouseenter', 'focusin', 'touchstart', 'pointerdown' ].forEach( function ( t ) { root.addEventListener( t, function () { paused = true; stop(); }, { passive: true } ); } );
	[ 'mouseleave', 'focusout' ].forEach( function ( t ) { root.addEventListener( t, function () { paused = false; play(); } ); } );
	document.addEventListener( 'visibilitychange', play );
	window.addEventListener( 'resize', function () { go( cur, false ); } );
	play();
} )();
