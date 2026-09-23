<?php
/**
 * Title: Home
 * Slug: lbana/home
 * Categories: lbana
 * Inserter: no
 */
?>
<!-- wp:group {"tagName":"section","className":"lb-section","templateLock":"contentOnly","layout":{"type":"default"}} -->
<section class="wp-block-group lb-section">
<!-- wp:group {"className":"lb-wrap lb-pad-hero lb-hero","layout":{"type":"default"}} -->
<div class="wp-block-group lb-wrap lb-pad-hero lb-hero">
<!-- wp:group {"className":"lb-hero-main","layout":{"type":"default"}} -->
<div class="wp-block-group lb-hero-main">
<!-- wp:paragraph {"className":"lb-kicker"} -->
<p class="lb-kicker">Linn Benton Area of Narcotics Anonymous</p>
<!-- /wp:paragraph -->
<!-- wp:heading {"level":1,"className":"lb-display"} -->
<h1 class="wp-block-heading lb-display">You never have to<br>use again.</h1>
<!-- /wp:heading -->
<!-- wp:paragraph {"className":"lb-lede"} -->
<p class="lb-lede">Narcotics Anonymous is a fellowship of people for whom drugs had become a major problem. There are no dues, no forms, and nothing to prove. Come as you are — meetings run every day across Albany, Corvallis, Lebanon, Sweet Home and Philomath.</p>
<!-- /wp:paragraph -->
<!-- wp:buttons -->
<div class="wp-block-buttons">
<!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/meetings/' ) ); ?>">Meetings happening today</a></div>
<!-- /wp:button -->
<!-- wp:button {"className":"is-style-outline"} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="tel:8772334287">Call the helpline</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:group -->
<!-- wp:group {"className":"lb-aside","layout":{"type":"default"}} -->
<div class="wp-block-group lb-aside">
<!-- wp:paragraph {"className":"lb-kicker"} -->
<p class="lb-kicker">Starting next</p>
<!-- /wp:paragraph -->
<!-- wp:shortcode -->
[lbana_next_meetings limit="3"]
<!-- /wp:shortcode -->
<!-- wp:paragraph {"className":"lb-small"} -->
<p class="lb-small"><a href="<?php echo esc_url( home_url( '/meetings/' ) ); ?>">See the full schedule</a></p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
</section>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","className":"lb-section lb-rule-top","templateLock":"contentOnly","layout":{"type":"default"}} -->
<section class="wp-block-group lb-section lb-rule-top">
<!-- wp:group {"className":"lb-wrap lb-pad-sm lb-stats","layout":{"type":"default"}} -->
<div class="wp-block-group lb-wrap lb-pad-sm lb-stats">
<!-- wp:group {"className":"lb-stat","layout":{"type":"default"}} -->
<div class="wp-block-group lb-stat">
<!-- wp:paragraph {"className":"lb-stat-num"} -->
<p class="lb-stat-num">[lbana_stat key="meetings" fallback="61"]</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph {"className":"lb-stat-label"} -->
<p class="lb-stat-label">Weekly meetings</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
<!-- wp:group {"className":"lb-stat","layout":{"type":"default"}} -->
<div class="wp-block-group lb-stat">
<!-- wp:paragraph {"className":"lb-stat-num"} -->
<p class="lb-stat-num">[lbana_stat key="towns" fallback="5"]</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph {"className":"lb-stat-label"} -->
<p class="lb-stat-label">Towns in the area</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
<!-- wp:group {"className":"lb-stat","layout":{"type":"default"}} -->
<div class="wp-block-group lb-stat">
<!-- wp:paragraph {"className":"lb-stat-num"} -->
<p class="lb-stat-num">37</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph {"className":"lb-stat-label"} -->
<p class="lb-stat-label">Years of the fellowship here</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
<!-- wp:group {"className":"lb-stat","layout":{"type":"default"}} -->
<div class="wp-block-group lb-stat">
<!-- wp:paragraph {"className":"lb-stat-num"} -->
<p class="lb-stat-num">$0</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph {"className":"lb-stat-label"} -->
<p class="lb-stat-label">Cost to attend, ever</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
</section>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","className":"lb-section lb-rule-top","templateLock":"contentOnly","layout":{"type":"default"}} -->
<section class="wp-block-group lb-section lb-rule-top">
<!-- wp:group {"className":"lb-wrap lb-pad","layout":{"type":"default"}} -->
<div class="wp-block-group lb-wrap lb-pad">
<!-- wp:paragraph {"className":"lb-kicker"} -->
<p class="lb-kicker">What to expect</p>
<!-- /wp:paragraph -->
<!-- wp:group {"className":"lb-row","layout":{"type":"default"}} -->
<div class="wp-block-group lb-row">
<!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">Just walk in</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>No appointment, no referral, no paperwork. Open meetings welcome anyone; closed meetings are for people who think they may have a drug problem. Both are listed on the schedule.</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
<!-- wp:group {"className":"lb-row","layout":{"type":"default"}} -->
<div class="wp-block-group lb-row">
<!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">You don't have to speak</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Sit in the back and listen for an hour if that's all you can do. People will say hello, and that's about the extent of what's asked of you.</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
<!-- wp:group {"className":"lb-row","layout":{"type":"default"}} -->
<div class="wp-block-group lb-row">
<!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">What's said here stays here</h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p>Anonymity is the spiritual foundation of our traditions. First names only, and nothing leaves the room.</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
<!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"lb-photo is-wide lb-photo-gap"} -->
<figure class="wp-block-image size-full lb-photo is-wide lb-photo-gap"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/meeting-room.png' ) ); ?>" alt="An empty meeting room, chairs set in a circle"/></figure>
<!-- /wp:image -->
</div>
<!-- /wp:group -->
</section>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","className":"lb-section","templateLock":"contentOnly","layout":{"type":"default"}} -->
<section class="wp-block-group lb-section">
<!-- wp:group {"className":"lb-wrap lb-pad lb-split","layout":{"type":"default"}} -->
<div class="wp-block-group lb-wrap lb-pad lb-split">
<!-- wp:group {"className":"lb-split-text","layout":{"type":"default"}} -->
<div class="wp-block-group lb-split-text">
<!-- wp:paragraph {"className":"lb-kicker"} -->
<p class="lb-kicker">The area</p>
<!-- /wp:paragraph -->
<!-- wp:heading {"level":2,"className":"lb-h2"} -->
<h2 class="wp-block-heading lb-h2">From the valley floor to the coast range</h2>
<!-- /wp:heading -->
<!-- wp:paragraph {"className":"lb-body"} -->
<p class="lb-body">The Linn Benton Area carries the message in Albany, Corvallis, Lebanon, Sweet Home and Philomath — plus the Canal Creek Campout each summer, where the fellowship spends a weekend in the trees.</p>
<!-- /wp:paragraph -->
<!-- wp:buttons -->
<div class="wp-block-buttons">
<!-- wp:button {"className":"is-style-outline"} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/events/' ) ); ?>">Upcoming events</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:group -->
<!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"lb-photo is-land lb-split-media"} -->
<figure class="wp-block-image size-full lb-photo is-land lb-split-media"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/marys-peak.jpeg' ) ); ?>" alt="Fog over the coast range ridgelines"/></figure>
<!-- /wp:image -->
</div>
<!-- /wp:group -->
</section>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","className":"lb-section lb-banner","templateLock":"contentOnly","layout":{"type":"default"}} -->
<section class="wp-block-group lb-section lb-banner">
<!-- wp:group {"className":"lb-wrap ","layout":{"type":"default"}} -->
<div class="wp-block-group lb-wrap ">
<!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">An addict, any addict,<br>can stop using drugs.</h2>
<!-- /wp:heading -->
<!-- wp:buttons -->
<div class="wp-block-buttons">
<!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="tel:8772334287">Call 877-233-4287</a></div>
<!-- /wp:button -->
<!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/meetings/' ) ); ?>">Find a meeting tonight</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:group -->
</section>
<!-- /wp:group -->
