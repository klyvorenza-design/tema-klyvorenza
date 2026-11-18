/* Painel "Imagem principal" no editor de blocos. JS puro, sem build. */
( function ( wp ) {
	var el = wp.element.createElement;
	var __ = wp.i18n.__;
	var useSelect = wp.data.useSelect;
	var useDispatch = wp.data.useDispatch;
	var PluginDocumentSettingPanel = ( wp.editor && wp.editor.PluginDocumentSettingPanel ) || ( wp.editPost && wp.editPost.PluginDocumentSettingPanel );
	var MediaUpload = ( wp.blockEditor || wp.editor ).MediaUpload;
	var MediaUploadCheck = ( wp.blockEditor || wp.editor ).MediaUploadCheck;
	var C = wp.components;

	function Panel() {
		var type = useSelect( function ( s ) { return s( 'core/editor' ).getCurrentPostType(); }, [] );
		var meta = useSelect( function ( s ) { return s( 'core/editor' ).getEditedPostAttribute( 'meta' ) || {}; }, [] );
		var edit = useDispatch( 'core/editor' ).editPost;
		var media = useSelect( function ( s ) {
			return meta._klyv_main_id ? s( 'core' ).getMedia( meta._klyv_main_id ) : null;
		}, [ meta._klyv_main_id ] );
		if ( type !== 'post' ) { return null; }
		function set( k, v ) { var m = {}; m[ k ] = v; edit( { meta: m } ); }
		var src = media && media.media_details && media.media_details.sizes && media.media_details.sizes.medium ? media.media_details.sizes.medium.source_url : ( media ? media.source_url : '' );
		return el( PluginDocumentSettingPanel, { name: 'klyv-main-image', title: __( 'Imagem principal do artigo', 'klyvorenza' ), className: 'klyv-main-image-panel' },
			el( 'p', { style: { marginTop: 0 } }, __( 'Aparece grande abaixo do título. Sem ela, o tema usa a imagem de destaque.', 'klyvorenza' ) ),
			src ? el( 'img', { src: src, alt: '', style: { maxWidth: '100%', height: 'auto', display: 'block', marginBottom: 8 } } ) : null,
			el( MediaUploadCheck, null,
				el( MediaUpload, {
					allowedTypes: [ 'image' ],
					value: meta._klyv_main_id,
					onSelect: function ( m ) { set( '_klyv_main_id', m.id ); if ( ! meta._klyv_main_alt && m.alt ) { set( '_klyv_main_alt', m.alt ); } },
					render: function ( o ) { return el( C.Button, { variant: 'secondary', onClick: o.open }, meta._klyv_main_id ? __( 'Trocar imagem', 'klyvorenza' ) : __( 'Escolher imagem', 'klyvorenza' ) ); }
				} )
			),
			meta._klyv_main_id ? el( C.Button, { variant: 'link', isDestructive: true, style: { marginLeft: 8 }, onClick: function () { set( '_klyv_main_id', 0 ); } }, __( 'Remover', 'klyvorenza' ) ) : null,
			el( C.TextControl, { label: __( 'Texto alternativo', 'klyvorenza' ), value: meta._klyv_main_alt || '', onChange: function ( v ) { set( '_klyv_main_alt', v ); }, __nextHasNoMarginBottom: true } ),
			el( C.TextControl, { label: __( 'Legenda (opcional)', 'klyvorenza' ), value: meta._klyv_main_caption || '', onChange: function ( v ) { set( '_klyv_main_caption', v ); }, __nextHasNoMarginBottom: true } )
		);
	}
	if ( PluginDocumentSettingPanel ) {
		wp.plugins.registerPlugin( 'klyv-main-image', { render: Panel } );
	}
} )( window.wp );
