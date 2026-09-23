<?php
/**
 * The three things officers change in the browser:
 *   1. Area meeting address and times   — Settings > Area meeting
 *   2. Sundays the area meeting skips   — Settings > Area meeting
 *   3. Agenda, minutes and form PDFs    — Media Library, "LBANA document" field
 *
 * Everything else is in the theme.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

function lbana_area_defaults() {
	return array(
		'address'  => '525 N Santiam Hwy, Lebanon, OR 97355',
		'room'     => 'Conference room',
		'pr_time'  => '11:00 AM',
		'asc_time' => '2:00 PM',
		'skip'     => '',
		'note'     => '',
	);
}

function lbana_area() {
	return wp_parse_args( (array) get_option( 'lbana_area', array() ), lbana_area_defaults() );
}

/* -------------------------------------------------------------------------
 * Settings > Area meeting
 * ---------------------------------------------------------------------- */

function lbana_area_menu() {
	add_options_page( 'Area meeting', 'Area meeting', 'edit_pages', 'lbana-area', 'lbana_area_page' );
}
add_action( 'admin_menu', 'lbana_area_menu' );

// Editors (officers) can save this page, not only administrators.
add_filter( 'option_page_capability_lbana_area', function () { return 'edit_pages'; } );

function lbana_area_register() {
	register_setting( 'lbana_area', 'lbana_area', array(
		'type'              => 'array',
		'sanitize_callback' => 'lbana_area_sanitize',
		'default'           => lbana_area_defaults(),
	) );
}
add_action( 'admin_init', 'lbana_area_register' );

function lbana_area_sanitize( $in ) {
	$out = lbana_area_defaults();
	foreach ( array( 'address', 'room', 'pr_time', 'asc_time', 'note' ) as $k ) {
		$out[ $k ] = isset( $in[ $k ] ) ? sanitize_text_field( $in[ $k ] ) : '';
	}
	// Keep only valid YYYY-MM-DD lines, sorted, de-duplicated.
	$dates = array();
	foreach ( preg_split( '/\r\n|\r|\n/', (string) ( $in['skip'] ?? '' ) ) as $line ) {
		$line = trim( $line );
		if ( preg_match( '/^(\d{4}-\d{2}-\d{2})/', $line, $m ) ) { $dates[ $m[1] ] = $line; }
	}
	ksort( $dates );
	$out['skip'] = implode( "\n", array_values( $dates ) );
	return $out;
}

function lbana_area_page() {
	$a    = lbana_area();
	$next = lbana_next_area_meeting();
	?>
	<div class="wrap">
		<h1>Area meeting</h1>
		<p style="max-width:60ch">What you save here shows on the For the Member page, the Contact page and anywhere else the area meeting is mentioned. The date is worked out automatically: the first Sunday of the month, moved a week later when that Sunday is on the skip list.</p>
		<?php if ( $next ) : ?>
			<p><strong>Next area meeting, as the site shows it:</strong> <?php echo esc_html( wp_date( 'l, F j, Y', $next->getTimestamp() ) ); ?></p>
		<?php endif; ?>
		<form method="post" action="options.php">
			<?php settings_fields( 'lbana_area' ); ?>
			<table class="form-table" role="presentation">
				<tr><th scope="row"><label for="lbana_address">Address</label></th>
					<td><input class="regular-text" id="lbana_address" name="lbana_area[address]" value="<?php echo esc_attr( $a['address'] ); ?>"></td></tr>
				<tr><th scope="row"><label for="lbana_room">Room</label></th>
					<td><input class="regular-text" id="lbana_room" name="lbana_area[room]" value="<?php echo esc_attr( $a['room'] ); ?>"></td></tr>
				<tr><th scope="row"><label for="lbana_pr">Public Relations starts</label></th>
					<td><input class="small-text" style="width:8em" id="lbana_pr" name="lbana_area[pr_time]" value="<?php echo esc_attr( $a['pr_time'] ); ?>"></td></tr>
				<tr><th scope="row"><label for="lbana_asc">ASC starts</label></th>
					<td><input class="small-text" style="width:8em" id="lbana_asc" name="lbana_area[asc_time]" value="<?php echo esc_attr( $a['asc_time'] ); ?>"></td></tr>
				<tr><th scope="row"><label for="lbana_skip">Sundays to skip</label></th>
					<td>
						<textarea class="large-text code" rows="8" id="lbana_skip" name="lbana_area[skip]" placeholder="2026-10-04 Example holiday"><?php echo esc_textarea( $a['skip'] ); ?></textarea>
						<p class="description">One date per line as YYYY-MM-DD. Anything after the date is a note to yourself. When a first Sunday is listed here, the meeting moves to the following Sunday. The area skips Super Bowl Sunday, Easter, Mother's Day, Father's Day, Memorial Day, July 4th, Labor Day, Thanksgiving and Christmas / New Year weekends.</p>
					</td></tr>
				<tr><th scope="row"><label for="lbana_note">Short notice</label></th>
					<td><input class="large-text" id="lbana_note" name="lbana_area[note]" value="<?php echo esc_attr( $a['note'] ); ?>" placeholder="Optional, e.g. Enter by the north door this month">
						<p class="description">Shown under the meeting details. Leave it empty when there's nothing to say.</p></td></tr>
			</table>
			<?php submit_button( 'Save area meeting' ); ?>
		</form>
	</div>
	<?php
}

/* -------------------------------------------------------------------------
 * Next area meeting
 * ---------------------------------------------------------------------- */

function lbana_skip_dates() {
	$dates = array();
	foreach ( preg_split( '/\r\n|\r|\n/', lbana_area()['skip'] ) as $line ) {
		if ( preg_match( '/^(\d{4}-\d{2}-\d{2})/', trim( $line ), $m ) ) { $dates[ $m[1] ] = true; }
	}
	return $dates;
}

/** The area meeting date for a given month, with skips applied. */
function lbana_area_meeting_for_month( $year, $month ) {
	$tz   = wp_timezone();
	$day  = new DateTimeImmutable( sprintf( '%04d-%02d-01', $year, $month ), $tz );
	$day  = $day->modify( 'first sunday of this month' );
	$skip = lbana_skip_dates();
	$guard = 0;
	while ( isset( $skip[ $day->format( 'Y-m-d' ) ] ) && $guard < 4 ) {
		$day = $day->modify( '+7 days' );
		$guard++;
	}
	return $day;
}

/** Today counts until the day is over — the meeting is "next" all Sunday. */
function lbana_next_area_meeting() {
	$tz    = wp_timezone();
	$today = new DateTimeImmutable( 'today', $tz );
	$this_month = lbana_area_meeting_for_month( (int) $today->format( 'Y' ), (int) $today->format( 'n' ) );
	if ( $this_month >= $today ) { return $this_month; }
	$next = $today->modify( 'first day of next month' );
	return lbana_area_meeting_for_month( (int) $next->format( 'Y' ), (int) $next->format( 'n' ) );
}

/**
 * [lbana_area_meeting]            — date, address, room, both times
 * [lbana_area_meeting show="date"] — just the date
 * [lbana_area_meeting show="address"]
 */
function lbana_area_meeting_shortcode( $atts ) {
	$atts = shortcode_atts( array( 'show' => 'full' ), $atts, 'lbana_area_meeting' );
	$a    = lbana_area();
	$next = lbana_next_area_meeting();
	$date = wp_date( 'l, F j', $next->getTimestamp() );

	if ( 'date' === $atts['show'] ) { return esc_html( $date ); }
	if ( 'address' === $atts['show'] ) { return esc_html( $a['address'] ); }

	$map = 'https://maps.google.com/?q=' . rawurlencode( $a['address'] );
	$out  = '<div class="lb-area-meeting">';
	$out .= '<p class="lb-area-date">' . esc_html( $date ) . '</p>';
	$out .= '<p class="lb-area-where">';
	$out .= '<a href="' . esc_url( $map ) . '">' . esc_html( $a['address'] ) . '</a>';
	if ( $a['room'] ) { $out .= ' &mdash; ' . esc_html( $a['room'] ); }
	$out .= '<br>Public Relations ' . esc_html( $a['pr_time'] ) . ' &middot; ASC ' . esc_html( $a['asc_time'] );
	$out .= '</p>';
	if ( $a['note'] ) { $out .= '<p class="lb-area-where">' . esc_html( $a['note'] ) . '</p>'; }
	$out .= '</div>';
	return $out;
}
add_shortcode( 'lbana_area_meeting', 'lbana_area_meeting_shortcode' );

/* -------------------------------------------------------------------------
 * Documents — agenda, minutes and forms straight from the Media Library
 *
 * Upload a PDF, then set "LBANA document" in its details panel. It appears
 * on the site in the right list, newest first. No page editing needed.
 * ---------------------------------------------------------------------- */

function lbana_doc_types() {
	return array(
		''          => '— Not a site document —',
		'agenda'    => 'Agenda',
		'minutes'   => 'Minutes',
		'form'      => 'Form',
		'guideline' => 'Guidelines & handbooks',
	);
}

function lbana_doc_field( $fields, $post ) {
	$current = get_post_meta( $post->ID, '_lbana_doc_type', true );
	$options = '';
	foreach ( lbana_doc_types() as $val => $label ) {
		$options .= '<option value="' . esc_attr( $val ) . '"' . selected( $current, $val, false ) . '>' . esc_html( $label ) . '</option>';
	}
	$fields['lbana_doc_type'] = array(
		'label' => 'LBANA document',
		'input' => 'html',
		'html'  => '<select name="attachments[' . $post->ID . '][lbana_doc_type]">' . $options . '</select>',
		'helps' => 'Pick a type and it shows on the For the Member page. The title above is what members see.',
	);
	return $fields;
}
add_filter( 'attachment_fields_to_edit', 'lbana_doc_field', 10, 2 );

function lbana_doc_field_save( $post, $attachment ) {
	if ( isset( $attachment['lbana_doc_type'] ) ) {
		$val = sanitize_key( $attachment['lbana_doc_type'] );
		if ( '' === $val || ! array_key_exists( $val, lbana_doc_types() ) ) {
			delete_post_meta( $post['ID'], '_lbana_doc_type' );
		} else {
			update_post_meta( $post['ID'], '_lbana_doc_type', $val );
		}
	}
	return $post;
}
add_filter( 'attachment_fields_to_save', 'lbana_doc_field_save', 10, 2 );

/**
 * [lbana_documents]                      — everything, grouped by type
 * [lbana_documents type="agenda,minutes" limit="6"]
 */
function lbana_documents_shortcode( $atts ) {
	$atts  = shortcode_atts( array( 'type' => 'agenda,minutes,form,guideline', 'limit' => 24 ), $atts, 'lbana_documents' );
	$types = array_filter( array_map( 'sanitize_key', explode( ',', $atts['type'] ) ) );
	$names = lbana_doc_types();

	$docs = get_posts( array(
		'post_type'      => 'attachment',
		'post_status'    => 'inherit',
		'posts_per_page' => max( 1, (int) $atts['limit'] ),
		'orderby'        => 'date',
		'order'          => 'DESC',
		'meta_query'     => array( array( 'key' => '_lbana_doc_type', 'value' => $types, 'compare' => 'IN' ) ),
	) );

	if ( ! $docs ) {
		return '<p class="lb-small">Nothing posted yet. Documents appear here as soon as an officer uploads them.</p>';
	}

	$out = '<div class="lb-cards">';
	foreach ( $docs as $doc ) {
		$type = get_post_meta( $doc->ID, '_lbana_doc_type', true );
		$file = get_attached_file( $doc->ID );
		$size = ( $file && file_exists( $file ) ) ? size_format( filesize( $file ), 0 ) : '';
		$ext  = strtoupper( pathinfo( (string) $file, PATHINFO_EXTENSION ) );
		$note = $doc->post_excerpt ? $doc->post_excerpt : 'Posted ' . get_the_date( 'F j, Y', $doc );

		$out .= '<a class="lb-card" href="' . esc_url( wp_get_attachment_url( $doc->ID ) ) . '">';
		$out .= '<p class="lb-card-title">' . esc_html( get_the_title( $doc ) ) . '</p>';
		$out .= '<p class="lb-card-note">' . esc_html( $note ) . '</p>';
		$out .= '<p class="lb-card-kind">' . esc_html( $names[ $type ] ?? '' ) . ( $ext ? ' &middot; ' . esc_html( $ext ) : '' ) . ( $size ? ' &middot; ' . esc_html( $size ) : '' ) . '</p>';
		$out .= '</a>';
	}
	$out .= '</div>';
	return $out;
}
add_shortcode( 'lbana_documents', 'lbana_documents_shortcode' );
