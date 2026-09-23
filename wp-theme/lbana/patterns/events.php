<?php
/**
 * Title: Events
 * Slug: lbana/events
 * Categories: lbana
 * Inserter: no
 */
?>
<!-- wp:group {"tagName":"section","className":"lb-section","templateLock":"contentOnly","layout":{"type":"default"}} -->
<section class="wp-block-group lb-section">
<!-- wp:group {"className":"lb-wrap lb-pad-head lb-pagehead","layout":{"type":"default"}} -->
<div class="wp-block-group lb-wrap lb-pad-head lb-pagehead">
<!-- wp:group {"className":"lb-pagehead-text","layout":{"type":"default"}} -->
<div class="wp-block-group lb-pagehead-text">
<!-- wp:paragraph {"className":"lb-kicker"} -->
<p class="lb-kicker">Events</p>
<!-- /wp:paragraph -->
<!-- wp:heading {"level":1,"className":"lb-title"} -->
<h1 class="wp-block-heading lb-title">What's coming up</h1>
<!-- /wp:heading -->
<!-- wp:paragraph {"className":"lb-lede"} -->
<p class="lb-lede">Area events, group anniversaries and everything in between. Groups can submit an event here or through the service portal — both go to the activities chair.</p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
<!-- wp:buttons -->
<div class="wp-block-buttons">
<!-- wp:button {"className":"is-style-outline"} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/events/submit-an-event/' ) ); ?>">Submit an event</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
<!-- /wp:group -->
</section>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","className":"lb-section lb-rule-top","templateLock":"contentOnly","layout":{"type":"default"}} -->
<section class="wp-block-group lb-section lb-rule-top">
<!-- wp:group {"className":"lb-wrap lb-pad-sm lb-pad-end","layout":{"type":"default"}} -->
<div class="wp-block-group lb-wrap lb-pad-sm lb-pad-end">
<!-- wp:shortcode -->
[mayo_event_list]
<!-- /wp:shortcode -->
</div>
<!-- /wp:group -->
</section>
<!-- /wp:group -->
