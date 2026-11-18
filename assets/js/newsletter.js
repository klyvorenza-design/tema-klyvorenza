/* Newsletter: envio por REST e pop-up (1 vez por visita, 24 h após fechar, 1 ano após inscrição). */
( function () {
	'use strict';
	var C = window.klyvNews || {}, d = document;
	var KEY_SUB = 'klyv-news-sub', KEY_CLOSED = 'klyv-news-closed', KEY_SESSION = 'klyv-news-shown';
	var DAY = 864e5, YEAR = 365 * DAY;
	function get( s, k ) { try { return s.getItem( k ); } catch ( e ) { return null; } }
	function set( s, k, v ) { try { s.setItem( k, v ); } catch ( e ) {} }
	function fresh( k ) { var t = parseInt( get( localStorage, k ), 10 ); return t && t > Date.now(); }

	/* Envio */
	var nonce = null;
	function getNonce() {
		if ( nonce ) { return Promise.resolve( nonce ); }
		return fetch( C.rest + '/nonce', { credentials: 'omit' } ).then( function ( r ) { return r.json(); } ).then( function ( j ) { nonce = j.nonce; return nonce; } );
	}
	function submit( form ) {
		var msg = form.querySelector( '.klyv-news-msg' ), btn = form.querySelector( 'button[type=submit]' );
		msg.classList.remove( 'is-err' ); msg.textContent = '';
		if ( ! form.checkValidity() ) { form.reportValidity(); return; }
		btn.disabled = true;
		getNonce().then( function ( n ) {
			var body = new URLSearchParams( new FormData( form ) );
			body.set( 'nonce', n ); body.set( 'consent', form.consent.checked ? '1' : '' );
			return fetch( C.rest, { method: 'POST', body: body, credentials: 'omit' } );
		} ).then( function ( r ) { return r.json().then( function ( j ) { return { ok: r.ok, j: j }; } ); } ).then( function ( x ) {
			if ( ! x.ok ) { throw new Error( x.j && x.j.message ? x.j.message : C.error ); }
			msg.textContent = x.j.pending ? C.ok : C.okDirect;
			set( localStorage, KEY_SUB, String( Date.now() + YEAR ) );
			form.reset();
			setTimeout( closePop, 2500 );
		} ).catch( function ( e ) {
			msg.classList.add( 'is-err' ); msg.textContent = e.message || C.error; nonce = null;
		} ).then( function () { btn.disabled = false; } );
	}
	d.addEventListener( 'submit', function ( e ) {
		if ( e.target.matches && e.target.matches( '[data-klyv-news]' ) ) { e.preventDefault(); submit( e.target ); }
	} );

	/* Pop-up */
	var pop = d.getElementById( 'klyv-pop' ), lastFocus = null;
	function closePop( byUser ) {
		if ( ! pop || pop.hidden ) { return; }
		pop.hidden = true; d.body.classList.remove( 'klyv-lock' );
		if ( byUser === true && ! fresh( KEY_SUB ) ) { set( localStorage, KEY_CLOSED, String( Date.now() + DAY ) ); }
		if ( lastFocus ) { lastFocus.focus(); }
	}
	if ( ! pop || ! C.popup || fresh( KEY_SUB ) || fresh( KEY_CLOSED ) || get( sessionStorage, KEY_SESSION ) ) { return; }
	function show() {
		if ( fresh( KEY_SUB ) || get( sessionStorage, KEY_SESSION ) ) { return; }
		set( sessionStorage, KEY_SESSION, '1' );
		lastFocus = d.activeElement; pop.hidden = false; d.body.classList.add( 'klyv-lock' );
		var f = pop.querySelector( 'input[type=text]' ); if ( f ) { f.focus(); }
	}
	var t = setTimeout( show, C.delay );
	addEventListener( 'pagehide', function () { clearTimeout( t ); } );
	pop.addEventListener( 'click', function ( e ) { if ( e.target === pop || e.target.closest( '[data-close]' ) ) { closePop( true ); } } );
	d.addEventListener( 'keydown', function ( e ) {
		if ( pop.hidden ) { return; }
		if ( e.key === 'Escape' ) { closePop( true ); return; }
		if ( e.key === 'Tab' ) {
			var f = Array.prototype.filter.call( pop.querySelectorAll( 'a[href],button,input:not([tabindex="-1"]),textarea' ), function ( n ) { return n.offsetParent !== null && ! n.disabled; } );
			if ( ! f.length ) { return; }
			if ( e.shiftKey && d.activeElement === f[ 0 ] ) { e.preventDefault(); f[ f.length - 1 ].focus(); }
			else if ( ! e.shiftKey && d.activeElement === f[ f.length - 1 ] ) { e.preventDefault(); f[ 0 ].focus(); }
		}
	} );
} )();
