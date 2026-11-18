<?php
/**
 * Newsletter: formulário, pop-up, endpoint REST, armazenamento e exportação CSV.
 *
 * @package Klyvorenza
 */

defined( 'ABSPATH' ) || exit;

/** Nome da tabela. */
function klyv_news_table() {
	global $wpdb;
	return $wpdb->prefix . 'klyv_subscribers';
}

/** Cria a tabela. */
function klyv_news_install() {
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$t  = klyv_news_table();
	$cs = $wpdb->get_charset_collate();
	dbDelta( "CREATE TABLE $t (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		name varchar(120) NOT NULL,
		email varchar(190) NOT NULL,
		status varchar(12) NOT NULL DEFAULT 'pending',
		token varchar(40) NOT NULL,
		created_at datetime NOT NULL,
		confirmed_at datetime NULL,
		PRIMARY KEY  (id),
		UNIQUE KEY email (email)
	) $cs;" );
	update_option( 'klyv_news_db', '1' );
}
add_action(
	'admin_init',
	function () {
		if ( '1' !== get_option( 'klyv_news_db' ) ) {
			klyv_news_install();
		}
	}
);

/** Newsletter ligada e fora de páginas de Web Stories. */
function klyv_newsletter_active() {
	return (bool) klyv_opt( 'news_on' ) && ! klyv_is_story();
}

/** O pop-up pode abrir nesta página? (não em política, termos, contato) */
function klyv_newsletter_popup_allowed() {
	if ( is_admin() || is_404() || ( function_exists( 'is_privacy_policy' ) && is_privacy_policy() ) ) {
		return false;
	}
	if ( is_page( array( 'politica-de-privacidade', 'termos-de-uso', 'contato', 'privacidade', 'termos' ) ) ) {
		return false;
	}
	return (bool) apply_filters( 'klyvorenza_newsletter_popup_allowed', true );
}

/** Formulário (usado na home e no pop-up). */
function klyv_news_form( $suffix = 'p' ) {
	$privacy = get_privacy_policy_url();
	$privacy = $privacy ? $privacy : home_url( '/politica-de-privacidade/' );
	$id      = 'klyv-n-' . $suffix;
	ob_start();
	?>
	<form class="klyv-news-form" method="post" novalidate data-klyv-news>
		<p><label for="<?php echo esc_attr( $id ); ?>-nome"><?php esc_html_e( 'Nome', 'klyvorenza' ); ?></label>
		<input id="<?php echo esc_attr( $id ); ?>-nome" name="nome" type="text" autocomplete="given-name" required maxlength="120"></p>
		<p><label for="<?php echo esc_attr( $id ); ?>-email"><?php esc_html_e( 'E-mail', 'klyvorenza' ); ?></label>
		<input id="<?php echo esc_attr( $id ); ?>-email" name="email" type="email" autocomplete="email" inputmode="email" required maxlength="190"></p>
		<p class="klyv-hp" aria-hidden="true"><label>Website<input name="website" type="text" tabindex="-1" autocomplete="off"></label></p>
		<p class="klyv-check"><label><input name="consent" type="checkbox" required>
			<span><?php
			printf(
				/* translators: %s: link da política de privacidade. */
				esc_html__( 'Concordo em receber e-mails e com a %s.', 'klyvorenza' ),
				'<a href="' . esc_url( $privacy ) . '">' . esc_html__( 'Política de Privacidade', 'klyvorenza' ) . '</a>'
			); // phpcs:ignore WordPress.Security.EscapeOutput
			?></span></label></p>
		<button type="submit" class="klyv-btn"><?php esc_html_e( 'Quero receber', 'klyvorenza' ); ?></button>
		<p class="klyv-news-msg" role="status" aria-live="polite"></p>
	</form>
	<?php
	return ob_get_clean();
}

/** Bloco da home. */
function klyv_render_newsletter() {
	if ( ! klyv_newsletter_active() ) {
		return '';
	}
	$h  = '<section class="klyv-news" aria-labelledby="klyv-news-t"><div class="klyv-news__logo">' . klyv_logo_img( 'simbolo', array( 'alt' => '', 'lazy' => true ) ) . '</div><div class="klyv-news__body">';
	$h .= '<h2 id="klyv-news-t">' . esc_html( klyv_opt( 'news_title' ) ) . '</h2><p>' . esc_html( klyv_opt( 'news_text' ) ) . '</p>' . klyv_news_form( 'home' ) . '</div></section>';
	return $h;
}

/** Pop-up no rodapé (fechado por padrão; a regra de exibição é do JS). */
add_action(
	'wp_footer',
	function () {
		if ( ! klyv_newsletter_active() || ! klyv_newsletter_popup_allowed() ) {
			return;
		}
		?>
		<div class="klyv-pop" id="klyv-pop" role="dialog" aria-modal="true" aria-labelledby="klyv-pop-t" hidden>
			<div class="klyv-pop__box">
				<button type="button" class="klyv-btn-i klyv-pop__x" data-close aria-label="<?php esc_attr_e( 'Fechar', 'klyvorenza' ); ?>"><?php echo klyv_icon( 'close' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
				<div class="klyv-pop__head"><?php echo klyv_logo_img( 'simbolo', array( 'alt' => '', 'lazy' => true ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?><h2 id="klyv-pop-t"><?php echo esc_html( klyv_opt( 'news_title' ) ); ?></h2></div>
				<p><?php echo esc_html( klyv_opt( 'news_text' ) ); ?></p>
				<?php echo klyv_news_form( 'pop' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</div>
		</div>
		<?php
	},
	40
);

/** REST. */
add_action(
	'rest_api_init',
	function () {
		register_rest_route(
			'klyvorenza/v1',
			'/newsletter/nonce',
			array(
				'methods'             => 'GET',
				'permission_callback' => '__return_true',
				'callback'            => function () {
					$r = new WP_REST_Response( array( 'nonce' => wp_create_nonce( 'klyv_news' ) ) );
					$r->header( 'Cache-Control', 'no-store' );
					return $r;
				},
			)
		);
		register_rest_route(
			'klyvorenza/v1',
			'/newsletter',
			array(
				'methods'             => 'POST',
				'permission_callback' => '__return_true',
				'callback'            => 'klyv_news_subscribe',
			)
		);
	}
);

/**
 * Inscrição.
 *
 * @param WP_REST_Request $req Requisição.
 * @return WP_REST_Response|WP_Error
 */
function klyv_news_subscribe( $req ) {
	global $wpdb;
	if ( ! wp_verify_nonce( (string) $req->get_param( 'nonce' ), 'klyv_news' ) ) {
		return new WP_Error( 'nonce', __( 'Sessão expirada. Recarregue a página.', 'klyvorenza' ), array( 'status' => 403 ) );
	}
	if ( '' !== trim( (string) $req->get_param( 'website' ) ) ) { // honeypot.
		return new WP_REST_Response( array( 'ok' => true, 'pending' => false ) ); // finge sucesso para o robô.
	}
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$key = 'klyv_rl_' . md5( $ip );
	$n   = (int) get_transient( $key );
	if ( $n >= 5 ) {
		return new WP_Error( 'limit', __( 'Muitas tentativas. Tente mais tarde.', 'klyvorenza' ), array( 'status' => 429 ) );
	}
	set_transient( $key, $n + 1, HOUR_IN_SECONDS );

	$name  = sanitize_text_field( (string) $req->get_param( 'nome' ) );
	$email = sanitize_email( (string) $req->get_param( 'email' ) );
	if ( '' === $name || ! is_email( $email ) || ! $req->get_param( 'consent' ) ) {
		return new WP_Error( 'invalid', __( 'Preencha nome, e-mail e aceite os termos.', 'klyvorenza' ), array( 'status' => 400 ) );
	}
	$name  = mb_substr( $name, 0, 120 );
	$pre   = apply_filters( 'klyvorenza_newsletter_pre_store', null, $name, $email );
	$token = wp_generate_password( 32, false );
	$t     = klyv_news_table();
	$dbl   = (bool) klyv_opt( 'news_double_optin' );
	$st    = $dbl ? 'pending' : 'confirmed';
	$exist = $wpdb->get_row( $wpdb->prepare( "SELECT id,status FROM $t WHERE email=%s", $email ) ); // phpcs:ignore WordPress.DB
	if ( $exist && 'confirmed' === $exist->status ) {
		return new WP_REST_Response( array( 'ok' => true, 'pending' => false ) );
	}
	if ( $exist ) {
		$wpdb->update( $t, array( 'name' => $name, 'token' => $token ), array( 'id' => $exist->id ) ); // phpcs:ignore WordPress.DB
		$id = (int) $exist->id;
	} elseif ( null === $pre ) {
		$wpdb->insert( $t, array( 'name' => $name, 'email' => $email, 'status' => $st, 'token' => $token, 'created_at' => current_time( 'mysql', true ), 'confirmed_at' => $dbl ? null : current_time( 'mysql', true ) ) ); // phpcs:ignore WordPress.DB
		$id = (int) $wpdb->insert_id;
	} else {
		$id = 0;
	}
	if ( $dbl ) {
		$link = add_query_arg( array( 'klyv_confirm' => $token ), home_url( '/' ) );
		/* translators: %s: nome do site. */
		$subj = sprintf( __( 'Confirme sua inscrição em %s', 'klyvorenza' ), get_bloginfo( 'name' ) );
		/* translators: 1: nome, 2: link. */
		$body = sprintf( __( "Oi, %1\$s!\n\nPara confirmar sua inscrição, abra este link:\n%2\$s\n\nSe não foi você, ignore esta mensagem.", 'klyvorenza' ), $name, $link );
		wp_mail( $email, $subj, $body );
	} else {
		do_action( 'klyvorenza_newsletter_subscribed', $name, $email, $id );
	}
	return new WP_REST_Response( array( 'ok' => true, 'pending' => $dbl ) );
}

/** Confirmação por link. */
add_action(
	'init',
	function () {
		if ( empty( $_GET['klyv_confirm'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return;
		}
		global $wpdb;
		$tok = sanitize_text_field( wp_unslash( $_GET['klyv_confirm'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
		$t   = klyv_news_table();
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $t WHERE token=%s", $tok ) ); // phpcs:ignore WordPress.DB
		if ( $row && 'confirmed' !== $row->status ) {
			$wpdb->update( $t, array( 'status' => 'confirmed', 'confirmed_at' => current_time( 'mysql', true ) ), array( 'id' => $row->id ) ); // phpcs:ignore WordPress.DB
			do_action( 'klyvorenza_newsletter_subscribed', $row->name, $row->email, (int) $row->id );
		}
		wp_die(
			esc_html( $row ? __( 'Inscrição confirmada. Obrigada!', 'klyvorenza' ) : __( 'Link inválido ou já usado.', 'klyvorenza' ) ),
			esc_html__( 'Newsletter', 'klyvorenza' ),
			array( 'response' => 200, 'link_url' => home_url( '/' ), 'link_text' => __( 'Voltar ao site', 'klyvorenza' ) )
		);
	}
);

/** Link de exportação CSV (painel). */
function klyv_news_export_link() {
	return wp_nonce_url( admin_url( 'admin-post.php?action=klyv_news_export' ), 'klyv_news_export' );
}
add_action(
	'admin_post_klyv_news_export',
	function () {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Sem permissão.', 'klyvorenza' ), '', array( 'response' => 403 ) );
		}
		check_admin_referer( 'klyv_news_export' );
		global $wpdb;
		$t    = klyv_news_table();
		$rows = $wpdb->get_results( "SELECT name,email,status,created_at,confirmed_at FROM $t ORDER BY id DESC", ARRAY_A ); // phpcs:ignore WordPress.DB
		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=newsletter-' . gmdate( 'Ymd' ) . '.csv' );
		$out = fopen( 'php://output', 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		fwrite( $out, "\xEF\xBB\xBF" ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		fputcsv( $out, array( 'nome', 'email', 'status', 'criado_em', 'confirmado_em' ) );
		foreach ( (array) $rows as $r ) {
			// Evita injeção de fórmulas em planilhas.
			foreach ( $r as $k => $v ) {
				$r[ $k ] = preg_match( '/^[=+\-@]/', (string) $v ) ? "'" . $v : $v;
			}
			fputcsv( $out, $r );
		}
		fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		exit;
	}
);
