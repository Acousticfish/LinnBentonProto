<?php
/**
 * Header, footer, contact form and the service portal embed. Rendered in PHP
 * so the header can carry the phone drawer and active states, and so none of
 * it can be dragged apart in the editor.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

function lbana_tel() {
	return 'tel:' . preg_replace( '/[^0-9]/', '', LBANA_HELPLINE );
}

function lbana_nav_items() {
	return array(
		array( 'Welcome',        home_url( '/' ) ),
		array( 'Meetings',       home_url( '/meetings/' ) ),
		array( 'Events',         home_url( '/events/' ) ),
		array( 'For the Member', home_url( '/members/' ) ),
		array( 'For the Public', home_url( '/public/' ) ),
		array( 'Contact',        home_url( '/contact/' ) ),
	);
}

/** Is this nav URL the current page, or an ancestor of it? */
function lbana_nav_is_current( $url ) {
	$here = trailingslashit( strtok( home_url( add_query_arg( array() ) ), '?' ) );
	$url  = trailingslashit( $url );
	if ( trailingslashit( home_url( '/' ) ) === $url ) { return is_front_page(); }
	return 0 === strpos( $here, $url );
}

function lbana_header_shortcode() {
	$items = lbana_nav_items();
	$links = '';
	foreach ( $items as $it ) {
		$cur    = lbana_nav_is_current( $it[1] ) ? ' aria-current="page"' : '';
		$links .= '<a href="' . esc_url( $it[1] ) . '"' . $cur . '>' . esc_html( $it[0] ) . '</a>';
	}
	$meet = esc_url( home_url( '/meetings/' ) );

	ob_start();
	?>
	<div class="lb-helpline">
		<div class="lb-wrap">
			<p>Helpline, any hour</p>
			<a href="<?php echo esc_attr( lbana_tel() ); ?>">877-ADDICTS &middot; <?php echo esc_html( LBANA_HELPLINE ); ?></a>
		</div>
	</div>
	<header class="lb-header">
		<div class="lb-wrap lb-header-row">
			<a class="lb-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<img src="<?php echo esc_url( get_theme_file_uri( 'assets/lbana-logo.png' ) ); ?>" alt="">LBANA
			</a>
			<nav class="lb-navlinks" aria-label="Main">
				<?php echo $links; // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<a class="lb-btn" href="<?php echo $meet; // phpcs:ignore ?>">Find a meeting</a>
			</nav>
			<button type="button" class="lb-menubtn" aria-expanded="false" aria-controls="lb-drawer" data-lb-menu>Menu</button>
		</div>
		<div class="lb-drawer" id="lb-drawer">
			<nav class="lb-wrap" aria-label="Main, mobile">
				<?php echo $links; // phpcs:ignore ?>
				<a class="lb-btn" href="<?php echo $meet; // phpcs:ignore ?>">Find a meeting</a>
			</nav>
		</div>
	</header>
	<?php
	return ob_get_clean();
}
add_shortcode( 'lbana_header', 'lbana_header_shortcode' );

function lbana_footer_shortcode() {
	$u = function ( $p ) { return esc_url( home_url( $p ) ); };
	ob_start();
	?>
	<footer class="lb-footer">
		<div class="lb-wrap lb-footer-cols">
			<div class="lb-footer-brand">
				<img src="<?php echo esc_url( get_theme_file_uri( 'assets/lbana-logo.png' ) ); ?>" alt="Linn Benton Area of Narcotics Anonymous">
				<p class="lb-footer-name">LBANA</p>
				<p class="lb-footer-blurb">Linn Benton Area of Narcotics Anonymous. Albany &middot; Corvallis &middot; Lebanon &middot; Sweet Home &middot; Philomath.</p>
				<p class="lb-footer-tel"><a href="<?php echo esc_attr( lbana_tel() ); ?>"><?php echo esc_html( LBANA_HELPLINE ); ?></a></p>
			</div>
			<div class="lb-footer-col">
				<p>Find help</p>
				<ul>
					<li><a href="<?php echo $u( '/meetings/' ); ?>">Meetings</a></li>
					<li><a href="<?php echo $u( '/events/' ); ?>">Events</a></li>
					<li><a href="<?php echo $u( '/contact/' ); ?>">Contact us</a></li>
				</ul>
			</div>
			<div class="lb-footer-col">
				<p>Members</p>
				<ul>
					<li><a href="<?php echo $u( '/members/' ); ?>">Documents &amp; forms</a></li>
					<li><a href="<?php echo $u( '/members/portal/' ); ?>">Service portal</a></li>
					<li><a href="<?php echo $u( '/donate/' ); ?>">Seventh tradition</a></li>
				</ul>
			</div>
			<div class="lb-footer-col">
				<p>Beyond the area</p>
				<ul>
					<li><a href="https://na.org">NA World Services</a></li>
					<li><a href="https://pacificcascaderegion.org">Pacific Cascade Region</a></li>
					<li><a href="https://virtual-na.org">Virtual NA</a></li>
				</ul>
			</div>
		</div>
		<div class="lb-footer-legal">
			<div class="lb-wrap">Anonymity is the spiritual foundation of all our traditions. This site is maintained by the Linn Benton Area Public Relations Subcommittee.</div>
		</div>
	</footer>
	<div class="lb-callbar">
		<a href="<?php echo esc_attr( lbana_tel() ); ?>">Call the helpline</a>
		<a href="<?php echo $u( '/meetings/' ); ?>">Meetings today</a>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'lbana_footer', 'lbana_footer_shortcode' );

/* -------------------------------------------------------------------------
 * [lbana_next_meetings limit="3"] — the next few meetings today, from BMLT.
 * Falls back to tomorrow's first meetings once today's are over.
 * ---------------------------------------------------------------------- */

function lbana_meetings_for_weekday( $weekday ) { // 1 = Sunday … 7 = Saturday (BMLT)
	$key    = 'lbana_day_' . $weekday;
	$cached = get_transient( $key );
	if ( is_array( $cached ) ) { return $cached; }

	$url = add_query_arg( array(
		'switcher'       => 'GetSearchResults',
		'services'       => LBANA_SERVICE_BODY,
		'weekdays'       => $weekday,
		'sort_keys'      => 'start_time',
		'data_field_key' => 'meeting_name,start_time,location_text,location_municipality,venue_type',
	), LBANA_BMLT_ROOT . 'client_interface/json/' );

	$res = wp_remote_get( $url, array( 'timeout' => 8 ) );
	if ( is_wp_error( $res ) || 200 !== wp_remote_retrieve_response_code( $res ) ) { return array(); }
	$rows = json_decode( wp_remote_retrieve_body( $res ), true );
	$rows = is_array( $rows ) ? $rows : array();
	set_transient( $key, $rows, 6 * HOUR_IN_SECONDS );
	return $rows;
}

function lbana_next_meetings_shortcode( $atts ) {
	$atts  = shortcode_atts( array( 'limit' => 3 ), $atts, 'lbana_next_meetings' );
	$limit = max( 1, (int) $atts['limit'] );
	$now   = new DateTimeImmutable( 'now', wp_timezone() );
	$today = (int) $now->format( 'w' ) + 1;
	$hm    = $now->format( 'H:i:s' );

	$list  = array_values( array_filter( lbana_meetings_for_weekday( $today ), function ( $m ) use ( $hm ) {
		return isset( $m['start_time'] ) && $m['start_time'] >= $hm;
	} ) );
	$label = '';
	if ( ! $list ) {
		$list  = lbana_meetings_for_weekday( $today % 7 + 1 );
		$label = 'Tomorrow';
	}
	$list = array_slice( $list, 0, $limit );

	if ( ! $list ) {
		return '<p class="lb-small">The schedule is loading slowly. <a href="' . esc_url( home_url( '/meetings/' ) ) . '">Open the full schedule</a> or call ' . esc_html( LBANA_HELPLINE ) . '.</p>';
	}

	$types = array( '1' => 'In person', '2' => 'Virtual', '3' => 'Hybrid' );
	$out   = '<div class="lb-next">';
	foreach ( $list as $m ) {
		$time = wp_date( 'g:i A', strtotime( '2000-01-01 ' . $m['start_time'] ), new DateTimeZone( 'UTC' ) );
		$bits = array_filter( array( $m['location_text'] ?? '', $m['location_municipality'] ?? '', $types[ $m['venue_type'] ?? '' ] ?? '' ) );
		$out .= '<div class="lb-next-row">';
		$out .= '<p class="lb-next-time">' . esc_html( ( $label ? $label . ' ' : '' ) . $time ) . '</p>';
		$out .= '<div><p class="lb-next-name">' . esc_html( $m['meeting_name'] ?? '' ) . '</p>';
		$out .= '<p class="lb-next-where">' . esc_html( implode( ' · ', $bits ) ) . '</p></div>';
		$out .= '</div>';
	}
	return $out . '</div>';
}
add_shortcode( 'lbana_next_meetings', 'lbana_next_meetings_shortcode' );

/* -------------------------------------------------------------------------
 * [lbana_portal] and [lbana_portal officer="1"]
 * The portal ships inside the theme at portal/index.html.
 * ---------------------------------------------------------------------- */

function lbana_portal_shortcode( $atts ) {
	$atts = shortcode_atts( array( 'officer' => '0' ), $atts, 'lbana_portal' );
	$src  = get_theme_file_uri( 'portal/index.html' );
	$src  = add_query_arg( 'v', LBANA_VERSION, $src );
	if ( '1' === (string) $atts['officer'] ) { $src = add_query_arg( 'officer', '1', $src ); }
	return '<div class="lb-portal"><iframe src="' . esc_url( $src ) . '" title="LBANA Service Portal" allow="clipboard-write" loading="eager"></iframe></div>';
}
add_shortcode( 'lbana_portal', 'lbana_portal_shortcode' );

/* -------------------------------------------------------------------------
 * [lbana_contact] — replaces the Divi contact module
 * ---------------------------------------------------------------------- */

function lbana_contact_shortcode() {
	$sent = false;
	$err  = '';

	if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['lbana_contact_nonce'] ) ) {
		if ( ! wp_verify_nonce( $_POST['lbana_contact_nonce'], 'lbana_contact' ) ) {
			$err = 'That form expired. Please try again.';
		} elseif ( ! empty( $_POST['lbana_website'] ) ) {
			$sent = true; // honeypot: pretend it worked
		} else {
			$name  = sanitize_text_field( wp_unslash( $_POST['lbana_name'] ?? '' ) );
			$email = sanitize_email( wp_unslash( $_POST['lbana_email'] ?? '' ) );
			$topic = sanitize_text_field( wp_unslash( $_POST['lbana_topic'] ?? '' ) );
			$msg   = sanitize_textarea_field( wp_unslash( $_POST['lbana_message'] ?? '' ) );
			if ( '' === $msg ) {
				$err = 'Please write a message.';
			} elseif ( $email && ! is_email( $email ) ) {
				$err = 'That email address does not look right.';
			} else {
				$headers = $email ? array( 'Reply-To: ' . ( $name ? $name : 'Visitor' ) . ' <' . $email . '>' ) : array();
				wp_mail(
					'info@lbana.org',
					'Website message' . ( $topic ? ': ' . $topic : '' ),
					"From: " . ( $name ? $name : '—' ) . "\nEmail: " . ( $email ? $email : '—' ) . "\nAbout: " . ( $topic ? $topic : '—' ) . "\n\n" . $msg,
					$headers
				);
				$sent = true;
			}
		}
	}

	if ( $sent ) {
		return '<div class="lb-form-msg">Thanks — your message is on its way to a trusted servant. If it is urgent, call the helpline at <a href="' . esc_attr( lbana_tel() ) . '">' . esc_html( LBANA_HELPLINE ) . '</a>.</div>';
	}

	ob_start();
	?>
	<form class="lb-form" method="post">
		<?php wp_nonce_field( 'lbana_contact', 'lbana_contact_nonce' ); ?>
		<?php if ( $err ) : ?><div class="lb-form-msg"><?php echo esc_html( $err ); ?></div><?php endif; ?>
		<div><label for="lbana_name">Name (first name is fine)</label><input type="text" id="lbana_name" name="lbana_name" autocomplete="given-name"></div>
		<div><label for="lbana_email">Email address</label><input type="email" id="lbana_email" name="lbana_email" inputmode="email" autocomplete="email"></div>
		<div><label for="lbana_topic">What's this about?</label><input type="text" id="lbana_topic" name="lbana_topic" placeholder="Meetings, public relations, service, other"></div>
		<div><label for="lbana_message">Message</label><textarea id="lbana_message" name="lbana_message" rows="6" required></textarea></div>
		<div class="lb-form-hp" aria-hidden="true"><label for="lbana_website">Website</label><input type="text" id="lbana_website" name="lbana_website" tabindex="-1" autocomplete="off"></div>
		<div><button type="submit" class="lb-btn">Reach out</button></div>
		<p class="lb-small" style="margin:0 !important">Messages go to the area's information email and are read by a trusted servant. Please don't include anything you wouldn't want read aloud — you can also use the phone line.</p>
	</form>
	<?php
	return ob_get_clean();
}
add_shortcode( 'lbana_contact', 'lbana_contact_shortcode' );
