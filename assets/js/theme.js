/* Klyvorenza: menu, busca, modo escuro, copiar link, scripts adiados. JS puro. */
( function () {
	'use strict';
	var d = document, root = d.documentElement;
	var $ = function ( s, c ) { return ( c || d ).querySelector( s ); };
	var $$ = function ( s, c ) { return Array.prototype.slice.call( ( c || d ).querySelectorAll( s ) ); };
	var I = window.klyvI18n || {};
	var FOCUSABLE = 'a[href],button:not([disabled]),input:not([disabled]),select,textarea,[tabindex]:not([tabindex="-1"])';

	/* Modo escuro */
	var tbtn = $( '#klyv-theme' );
	function paintTheme() {
		var dark = root.dataset.theme === 'dark';
		if ( tbtn ) {
			tbtn.setAttribute( 'aria-pressed', dark ? 'true' : 'false' );
			tbtn.setAttribute( 'aria-label', dark ? ( I.light || 'Ativar modo claro' ) : ( I.dark || 'Ativar modo escuro' ) );
		}
		var m = $( 'meta[name=theme-color]' );
		if ( ! m ) { m = d.createElement( 'meta' ); m.name = 'theme-color'; d.head.appendChild( m ); }
		m.content = dark ? '#0D1612' : '#F6FBF7';
	}
	if ( tbtn ) {
		tbtn.addEventListener( 'click', function () {
			root.dataset.theme = root.dataset.theme === 'dark' ? 'light' : 'dark';
			try { localStorage.setItem( 'klyv-theme', root.dataset.theme ); } catch ( e ) {}
			paintTheme();
		} );
	}
	paintTheme();

	/* Camadas (gaveta e busca): foco preso, Esc, clique fora, rolagem travada */
	var open = null, opener = null;
	function trap( e ) {
		if ( ! open ) { return; }
		if ( e.key === 'Escape' ) { closeLayer(); return; }
		if ( e.key !== 'Tab' ) { return; }
		var f = $$( FOCUSABLE, open ).filter( function ( n ) { return n.offsetParent !== null; } );
		if ( ! f.length ) { return; }
		var first = f[ 0 ], last = f[ f.length - 1 ];
		if ( e.shiftKey && d.activeElement === first ) { e.preventDefault(); last.focus(); }
		else if ( ! e.shiftKey && d.activeElement === last ) { e.preventDefault(); first.focus(); }
	}
	function openLayer( el, trigger, focusSel ) {
		closeLayer( true );
		open = el; opener = trigger;
		el.hidden = false;
		var back = $( '.klyv-drawer-back' );
		if ( el.id === 'klyv-drawer' && back ) { back.hidden = false; }
		d.body.classList.add( 'klyv-lock' );
		if ( trigger ) { trigger.setAttribute( 'aria-expanded', 'true' ); }
		var f = focusSel ? $( focusSel, el ) : null;
		( f || $( FOCUSABLE, el ) ).focus();
	}
	function closeLayer( silent ) {
		if ( ! open ) { return; }
		open.hidden = true;
		var back = $( '.klyv-drawer-back' );
		if ( back ) { back.hidden = true; }
		d.body.classList.remove( 'klyv-lock' );
		if ( opener ) { opener.setAttribute( 'aria-expanded', 'false' ); if ( ! silent ) { opener.focus(); } }
		open = opener = null;
	}
	d.addEventListener( 'keydown', trap );
	var burger = $( '#klyv-burger' ), drawer = $( '#klyv-drawer' ), sbtn = $( '#klyv-open-search' ), search = $( '#klyv-search' );
	if ( burger && drawer ) { burger.addEventListener( 'click', function () { openLayer( drawer, burger ); } ); }
	if ( sbtn && search ) { sbtn.addEventListener( 'click', function () { openLayer( search, sbtn, 'input[type=search]' ); } ); }
	d.addEventListener( 'click', function ( e ) {
		var t = e.target;
		if ( t.closest && t.closest( '[data-close]' ) && open && ( open.contains( t ) || t.classList.contains( 'klyv-drawer-back' ) ) ) { closeLayer(); return; }
		if ( open === search && t === search ) { closeLayer(); return; }
		if ( open === drawer && t.closest && t.closest( '.klyv-drawer a' ) ) { closeLayer( true ); }
	} );
	d.addEventListener( 'keydown', function ( e ) {
		if ( e.key === '/' && ! open && search && ! /input|textarea|select/i.test( ( d.activeElement || {} ).tagName || '' ) && ! ( d.activeElement && d.activeElement.isContentEditable ) && matchMedia( '(min-width:1025px)' ).matches ) {
			e.preventDefault(); openLayer( search, sbtn, 'input[type=search]' );
		}
	} );
	matchMedia( '(min-width:1025px)' ).addEventListener( 'change', function ( m ) { if ( m.matches && open === drawer ) { closeLayer( true ); } } );

	/* Submenus: sanfona no mobile, clique e teclado no desktop */
	$$( '.klyv-sub-toggle' ).forEach( function ( b ) {
		b.addEventListener( 'click', function () {
			var li = b.parentNode, on = ! li.classList.contains( 'is-open' );
			$$( '.has-sub.is-open', li.parentNode ).forEach( function ( o ) { if ( o !== li ) { o.classList.remove( 'is-open' ); $( '.klyv-sub-toggle', o ).setAttribute( 'aria-expanded', 'false' ); } } );
			li.classList.toggle( 'is-open', on );
			b.setAttribute( 'aria-expanded', on ? 'true' : 'false' );
		} );
	} );
	d.addEventListener( 'click', function ( e ) {
		if ( ! e.target.closest( '.has-sub' ) ) {
			$$( '.klyv-head__in .has-sub.is-open' ).forEach( function ( o ) { o.classList.remove( 'is-open' ); $( '.klyv-sub-toggle', o ).setAttribute( 'aria-expanded', 'false' ); } );
		}
	} );

	/* Copiar link */
	d.addEventListener( 'click', function ( e ) {
		var b = e.target.closest && e.target.closest( '[data-copy]' );
		if ( ! b ) { return; }
		var url = b.getAttribute( 'data-copy' );
		function done() {
			var t = d.createElement( 'div' );
			t.className = 'klyv-toast'; t.setAttribute( 'role', 'status' ); t.textContent = I.copied || 'Link copiado';
			d.body.appendChild( t ); setTimeout( function () { t.remove(); }, 2200 );
		}
		var go = b.getAttribute( 'data-open' ); if ( go ) { window.open( go, '_blank', 'noopener' ); }
		if ( navigator.clipboard ) { navigator.clipboard.writeText( url ).then( done ); } else { var i = d.createElement( 'input' ); i.value = url; d.body.appendChild( i ); i.select(); d.execCommand( 'copy' ); i.remove(); done(); }
	} );

	/* Scripts de terceiros adiados (GTranslate): após interação ou load */
	var late = $$( 'script[data-klyv-delay]' ), fired = false;
	function runLate() {
		if ( fired ) { return; }
		fired = true;
		late.forEach( function ( o ) {
			var s = d.createElement( 'script' );
			Array.prototype.forEach.call( o.attributes, function ( a ) {
				if ( a.name === 'data-src' ) { s.src = a.value; } else if ( a.name !== 'type' && a.name !== 'data-klyv-delay' ) { s.setAttribute( a.name, a.value ); }
			} );
			s.text = o.text;
			o.parentNode.replaceChild( s, o );
		} );
	}
	if ( late.length ) {
		[ 'pointerdown', 'keydown', 'scroll', 'touchstart' ].forEach( function ( t ) { addEventListener( t, runLate, { once: true, passive: true } ); } );
		addEventListener( 'load', function () { setTimeout( runLate, 3500 ); } );
	}
} )();
