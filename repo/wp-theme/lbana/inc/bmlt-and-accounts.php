<?php
/**
 * BMLT meeting finder wrapper, live stats, helpline shortcode and member
 * self-registration. Carried over unchanged from the Divi child theme.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

// [lbana_meetings]  — Crouton meeting finder, pre-pointed at service body 35.
function lbana_meetings_shortcode( $atts ) {
	$atts = shortcode_atts( array(
		'view'   => 'weekday',  // weekday | today | map
		'header' => 'false',
	), $atts );

	return do_shortcode( sprintf(
		'[crouton root_server="%s" service_body="%s" view="%s" has_tabs="true" show_map="true" header="%s"]',
		esc_attr( LBANA_BMLT_ROOT ),
		esc_attr( LBANA_SERVICE_BODY ),
		esc_attr( $atts['view'] ),
		esc_attr( $atts['header'] )
	) );
}
add_shortcode( 'lbana_meetings', 'lbana_meetings_shortcode' );

// [lbana_helpline]  — the number, as a link, wherever it is needed.
function lbana_helpline_shortcode() {
	$tel = preg_replace( '/[^0-9]/', '', LBANA_HELPLINE );
	return '<a href="tel:' . esc_attr( $tel ) . '">' . esc_html( LBANA_HELPLINE ) . '</a>';
}
add_shortcode( 'lbana_helpline', 'lbana_helpline_shortcode' );

/**
 * Live counts from BMLT, cached for twelve hours.
 *
 * Returns array( 'meetings' => int, 'towns' => int ) or nulls if the root
 * server cannot be reached. Cached so the homepage never waits on a remote
 * call, and so a BMLT outage shows the last known figures rather than zeros.
 */
function lbana_bmlt_counts() {
	$cached = get_transient( 'lbana_bmlt_counts' );
	if ( is_array( $cached ) ) { return $cached; }

	$url = add_query_arg( array(
		'switcher'     => 'GetSearchResults',
		'services'     => LBANA_SERVICE_BODY,
		'data_field_key' => 'meeting_name,location_municipality,weekday_tinyint',
	), LBANA_BMLT_ROOT . 'client_interface/json/' );

	$res = wp_remote_get( $url, array( 'timeout' => 8 ) );
	if ( is_wp_error( $res ) || 200 !== wp_remote_retrieve_response_code( $res ) ) {
		// Keep whatever the last good numbers were; never cache a failure.
		$stale = get_option( 'lbana_bmlt_counts_last' );
		return is_array( $stale ) ? $stale : array( 'meetings' => null, 'towns' => null );
	}

	$rows = json_decode( wp_remote_retrieve_body( $res ), true );
	if ( ! is_array( $rows ) || ! count( $rows ) ) {
		$stale = get_option( 'lbana_bmlt_counts_last' );
		return is_array( $stale ) ? $stale : array( 'meetings' => null, 'towns' => null );
	}

	$towns = array();
	foreach ( $rows as $row ) {
		$town = isset( $row['location_municipality'] ) ? trim( $row['location_municipality'] ) : '';
		if ( '' !== $town ) { $towns[ strtolower( $town ) ] = true; }
	}

	$counts = array(
		'meetings' => count( $rows ),
		'towns'    => count( $towns ),
	);

	set_transient( 'lbana_bmlt_counts', $counts, 12 * HOUR_IN_SECONDS );
	update_option( 'lbana_bmlt_counts_last', $counts, false );

	return $counts;
}

/**
 * [lbana_stat key="meetings"]  — a live number from BMLT.
 * [lbana_stat key="towns" fallback="5"]
 *
 * key: meetings | towns. fallback prints if BMLT is unreachable and nothing
 * has ever been cached.
 */
function lbana_stat_shortcode( $atts ) {
	$atts = shortcode_atts( array(
		'key'      => 'meetings',
		'fallback' => '—',
	), $atts, 'lbana_stat' );

	$counts = lbana_bmlt_counts();
	$key    = in_array( $atts['key'], array( 'meetings', 'towns' ), true ) ? $atts['key'] : 'meetings';

	return isset( $counts[ $key ] ) && null !== $counts[ $key ]
		? esc_html( (string) $counts[ $key ] )
		: esc_html( $atts['fallback'] );
}
add_shortcode( 'lbana_stat', 'lbana_stat_shortcode' );

/* -------------------------------------------------------------------------
 * Member self-registration
 *
 * Open signup, but not a bare WordPress registration page: three guards,
 * none of which asks a member to prove anything about themselves.
 *
 *   1. A honeypot field bots fill in and humans never see.
 *   2. A dwell-time check — a form submitted in under four seconds was not
 *      read by a person.
 *   3. One question a member can answer and a bot cannot: the name of any
 *      meeting in the area, matched against BMLT.
 *
 * Accounts are named per person as first name and last initial. No address,
 * no phone, no last name. Group and position are optional and stored as
 * plain user meta so the area can see who fills what.
 *
 * Usage: [lbana_register] on a public page.
 * ---------------------------------------------------------------------- */

// Off until the area decides to open it. Flip to true, or define
// LBANA_REGISTRATION_OPEN in wp-config.php, when accounts go live.
if ( ! defined( 'LBANA_REGISTRATION_OPEN' ) ) {
	define( 'LBANA_REGISTRATION_OPEN', false );
}

/** Meeting names from BMLT, lowercased, cached twelve hours. */
function lbana_meeting_names() {
	$cached = get_transient( 'lbana_meeting_names' );
	if ( is_array( $cached ) ) { return $cached; }

	$url = add_query_arg( array(
		'switcher'       => 'GetSearchResults',
		'services'       => LBANA_SERVICE_BODY,
		'data_field_key' => 'meeting_name',
	), LBANA_BMLT_ROOT . 'client_interface/json/' );

	$res = wp_remote_get( $url, array( 'timeout' => 8 ) );
	if ( is_wp_error( $res ) || 200 !== wp_remote_retrieve_response_code( $res ) ) {
		return array();
	}

	$rows  = json_decode( wp_remote_retrieve_body( $res ), true );
	$names = array();
	if ( is_array( $rows ) ) {
		foreach ( $rows as $row ) {
			$name = isset( $row['meeting_name'] ) ? trim( $row['meeting_name'] ) : '';
			if ( '' !== $name ) { $names[] = strtolower( $name ); }
		}
	}

	if ( count( $names ) ) { set_transient( 'lbana_meeting_names', $names, 12 * HOUR_IN_SECONDS ); }
	return $names;
}

/**
 * Does this answer look like an area meeting name?
 *
 * Deliberately loose — a member typing "keep it simple" should pass whether
 * BMLT calls it "Keep It Simple" or "Keep It Simple Group". If BMLT cannot be
 * reached the check passes rather than locking everyone out.
 */
function lbana_answer_matches_meeting( $answer ) {
	$answer = strtolower( trim( $answer ) );
	if ( strlen( $answer ) < 4 ) { return false; }

	$names = lbana_meeting_names();
	if ( ! count( $names ) ) { return true; }

	foreach ( $names as $name ) {
		if ( false !== strpos( $name, $answer ) || false !== strpos( $answer, $name ) ) {
			return true;
		}
	}
	return false;
}

function lbana_register_shortcode() {
	if ( is_user_logged_in() ) {
		return '<p class="lb-reg-note">You are already signed in. <a href="' . esc_url( home_url( '/members' ) ) . '">Go to the member area</a>.</p>';
	}

	if ( ! LBANA_REGISTRATION_OPEN ) {
		return '<p class="lb-reg-note">Accounts are not open yet. During the test the portal needs no login &mdash; '
			. '<a href="' . esc_url( home_url( '/members/portal' ) ) . '">open the service portal</a>.</p>';
	}

	$notice = '';
	$values = array( 'first' => '', 'initial' => '', 'email' => '', 'group' => '', 'position' => '' );

	if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['lbana_register_nonce'] ) ) {
		$result = lbana_handle_registration();
		if ( is_wp_error( $result ) ) {
			$notice = '<div class="lb-reg-error">' . esc_html( $result->get_error_message() ) . '</div>';
			foreach ( $values as $k => $_ ) {
				$values[ $k ] = isset( $_POST[ 'lbana_' . $k ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'lbana_' . $k ] ) ) : '';
			}
		} else {
			return '<div class="lb-reg-done">'
				. '<p class="lb-reg-done-title">You&rsquo;re signed up.</p>'
				. '<p>Check your email for a link to set a password. If it does not arrive within a few minutes, look in spam, then write to '
				. '<a href="mailto:webservant@lbana.org">webservant@lbana.org</a>.</p>'
				. '</div>';
		}
	}

	$positions = array( '', 'GSR', 'Alt-GSR', 'Group treasurer', 'Group secretary', 'Literature', 'Area officer', 'Subcommittee chair', 'No position right now' );
	$options   = '';
	foreach ( $positions as $p ) {
		$label     = '' === $p ? 'Choose one (optional)' : $p;
		$selected  = selected( $values['position'], $p, false );
		$options  .= '<option value="' . esc_attr( $p ) . '" ' . $selected . '>' . esc_html( $label ) . '</option>';
	}

	ob_start();
	?>
	<form method="post" class="lb-reg" novalidate>
		<?php wp_nonce_field( 'lbana_register', 'lbana_register_nonce' ); ?>
		<input type="hidden" name="lbana_opened" value="<?php echo esc_attr( time() ); ?>">

		<?php echo $notice; // phpcs:ignore WordPress.Security.EscapeOutput ?>

		<p class="lb-reg-intro">First name and last initial is all we ask for. No last name, no address, no phone.</p>

		<div class="lb-reg-row">
			<div class="lb-field">
				<label for="lbana_first">First name</label>
				<input class="lb-input" type="text" id="lbana_first" name="lbana_first" autocomplete="given-name"
				       value="<?php echo esc_attr( $values['first'] ); ?>" required>
			</div>
			<div class="lb-field lb-field-initial">
				<label for="lbana_initial">Last initial</label>
				<input class="lb-input" type="text" id="lbana_initial" name="lbana_initial" maxlength="1" size="1"
				       value="<?php echo esc_attr( $values['initial'] ); ?>" required>
			</div>
		</div>

		<div class="lb-field">
			<label for="lbana_email">Email</label>
			<input class="lb-input" type="email" id="lbana_email" name="lbana_email" autocomplete="email"
			       value="<?php echo esc_attr( $values['email'] ); ?>" required>
			<p class="lb-field-help">Used for your password link and nothing else. Never shown on the site.</p>
		</div>

		<div class="lb-field">
			<label for="lbana_group">Home group <span class="lb-optional">optional</span></label>
			<input class="lb-input" type="text" id="lbana_group" name="lbana_group"
			       value="<?php echo esc_attr( $values['group'] ); ?>">
		</div>

		<div class="lb-field">
			<label for="lbana_position">Service position <span class="lb-optional">optional</span></label>
			<select class="lb-input" id="lbana_position" name="lbana_position"><?php echo $options; // phpcs:ignore WordPress.Security.EscapeOutput ?></select>
		</div>

		<div class="lb-field">
			<label for="lbana_meeting">Name any meeting in the Linn Benton Area</label>
			<input class="lb-input" type="text" id="lbana_meeting" name="lbana_meeting" autocomplete="off" required>
			<p class="lb-field-help">A quick check that you&rsquo;re a person, not a bot. Any group on the <a href="<?php echo esc_url( home_url( '/meetings' ) ); ?>">meeting list</a> counts.</p>
		</div>

		<?php /* Honeypot. Hidden from people, catnip for bots. */ ?>
		<div class="lb-reg-hp" aria-hidden="true">
			<label for="lbana_website">Website</label>
			<input type="text" id="lbana_website" name="lbana_website" tabindex="-1" autocomplete="off">
		</div>

		<button type="submit" class="lb-btn">Create my account</button>
		<p class="lb-reg-foot">Already have an account? <a href="<?php echo esc_url( wp_login_url( home_url( '/members' ) ) ); ?>">Sign in</a>.</p>
	</form>
	<?php
	return ob_get_clean();
}
add_shortcode( 'lbana_register', 'lbana_register_shortcode' );

/** Validate and create. Returns true or WP_Error. */
function lbana_handle_registration() {
	if ( ! wp_verify_nonce( $_POST['lbana_register_nonce'], 'lbana_register' ) ) {
		return new WP_Error( 'nonce', 'That form expired. Please try again.' );
	}

	// Guard 1 — honeypot.
	if ( ! empty( $_POST['lbana_website'] ) ) {
		return new WP_Error( 'spam', 'Something went wrong. Please try again.' );
	}

	// Guard 2 — dwell time.
	$opened = isset( $_POST['lbana_opened'] ) ? absint( $_POST['lbana_opened'] ) : 0;
	if ( $opened && ( time() - $opened ) < 4 ) {
		return new WP_Error( 'fast', 'Something went wrong. Please try again.' );
	}

	$first   = sanitize_text_field( wp_unslash( $_POST['lbana_first'] ?? '' ) );
	$initial = sanitize_text_field( wp_unslash( $_POST['lbana_initial'] ?? '' ) );
	$email   = sanitize_email( wp_unslash( $_POST['lbana_email'] ?? '' ) );
	$meeting = sanitize_text_field( wp_unslash( $_POST['lbana_meeting'] ?? '' ) );

	if ( '' === $first || '' === $initial ) {
		return new WP_Error( 'name', 'Please give a first name and a last initial.' );
	}
	if ( ! is_email( $email ) ) {
		return new WP_Error( 'email', 'That email address does not look right.' );
	}
	if ( email_exists( $email ) ) {
		return new WP_Error( 'dupe', 'There is already an account on that email. Try signing in, or reset the password.' );
	}

	// Guard 3 — the meeting-name question.
	if ( ! lbana_answer_matches_meeting( $meeting ) ) {
		return new WP_Error( 'meeting', 'We could not match that to a meeting in the area. Check the spelling against the meeting list, or write to webservant@lbana.org.' );
	}

	$initial = strtoupper( substr( $initial, 0, 1 ) );
	$display = $first . ' ' . $initial . '.';

	// Login name: firstinitial, then firstinitial2 and so on.
	$base  = sanitize_user( strtolower( $first . $initial ), true );
	$login = $base;
	$n     = 2;
	while ( username_exists( $login ) ) {
		$login = $base . $n;
		$n++;
	}

	$user_id = wp_insert_user( array(
		'user_login'   => $login,
		'user_email'   => $email,
		'user_pass'    => wp_generate_password( 20 ),
		'first_name'   => $first,
		'last_name'    => $initial . '.',
		'display_name' => $display,
		'nickname'     => $display,
		'role'         => 'lbana_member',
	) );

	if ( is_wp_error( $user_id ) ) {
		return new WP_Error( 'create', 'We could not create the account. Please write to webservant@lbana.org.' );
	}

	$group    = sanitize_text_field( wp_unslash( $_POST['lbana_group'] ?? '' ) );
	$position = sanitize_text_field( wp_unslash( $_POST['lbana_position'] ?? '' ) );
	if ( '' !== $group )    { update_user_meta( $user_id, 'lbana_group', $group ); }
	if ( '' !== $position ) { update_user_meta( $user_id, 'lbana_position', $position ); }

	// Sends the "set your password" link. No password is ever typed on our form.
	wp_new_user_notification( $user_id, null, 'user' );

	wp_mail(
		'webservant@lbana.org',
		'New member account: ' . $display,
		"A new member signed up at " . home_url() . "\n\n"
		. "Name: $display\n"
		. "Group: " . ( '' !== $group ? $group : '—' ) . "\n"
		. "Position: " . ( '' !== $position ? $position : '—' ) . "\n"
		. "Answered with meeting: $meeting\n"
	);

	return true;
}

/** Group and position on the user list, so the area can see who fills what. */
function lbana_user_columns( $cols ) {
	$cols['lbana_group']    = 'Home group';
	$cols['lbana_position'] = 'Position';
	return $cols;
}
add_filter( 'manage_users_columns', 'lbana_user_columns' );

function lbana_user_column_value( $out, $col, $user_id ) {
	if ( 'lbana_group' === $col || 'lbana_position' === $col ) {
		$val = get_user_meta( $user_id, $col, true );
		return $val ? esc_html( $val ) : '—';
	}
	return $out;
}
add_filter( 'manage_users_custom_column', 'lbana_user_column_value', 10, 3 );

// Let an editor force a refresh without waiting out the transient:
// visit any admin page with ?lbana_flush_stats=1
function lbana_maybe_flush_stats() {
	if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) { return; }
	if ( empty( $_GET['lbana_flush_stats'] ) ) { return; }
	delete_transient( 'lbana_bmlt_counts' );
}
add_action( 'admin_init', 'lbana_maybe_flush_stats' );
