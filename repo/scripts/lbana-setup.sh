#!/usr/bin/env bash
# LBANA — wipe the Divi build on test.lbana.org and stand up the block theme.
#
# Run over SSH from the WordPress root:
#   ssh -p 18765 <user>@<host>
#   cd ~/www/test.lbana.org/public_html
#   bash ~/lbana-setup.sh
#
# Expects the theme already uploaded to wp-content/themes/lbana/.
# Safe to re-run: it deletes and recreates the site pages each time, so any
# text officers have edited on those pages is lost on a re-run. Run it once.

set -euo pipefail

say() { printf '\n\033[1m%s\033[0m\n' "$*"; }

if [ ! -f wp-content/themes/lbana/style.css ]; then
  echo "wp-content/themes/lbana is missing — upload the theme first." >&2
  exit 1
fi

say "Backing up the database first"
mkdir -p ~/lbana-backups
wp db export ~/lbana-backups/before-block-theme-$(date +%Y%m%d-%H%M).sql

say "Activating the LBANA theme"
wp theme activate lbana

say "Removing the Divi build"
for t in lbana-child Divi; do
  if wp theme is-installed "$t"; then wp theme delete "$t" || true; fi
done
# Divi's Theme Builder layouts and library items
for type in et_template et_header_layout et_body_layout et_footer_layout et_pb_layout et_theme_builder; do
  ids="$(wp post list --post_type="$type" --post_status=any --format=ids 2>/dev/null || true)"
  if [ -n "$ids" ]; then wp post delete $ids --force >/dev/null; echo "  - removed $type"; fi
done
# The literature store is off the site
if wp plugin is-active woocommerce 2>/dev/null; then wp plugin deactivate woocommerce; fi

say "Deleting old pages and menus"
ids="$(wp post list --post_type=page --post_status=any --format=ids)"
if [ -n "$ids" ]; then wp post delete $ids --force >/dev/null; fi
for m in $(wp menu list --fields=term_id --format=ids 2>/dev/null || true); do wp menu delete "$m" >/dev/null; done

say "Site settings"
wp option update blog_public 0
wp option update users_can_register 0
wp option update default_comment_status closed
wp rewrite structure '/%postname%/' --hard
wp option update timezone_string 'America/Los_Angeles'

# page_from_pattern <title> <slug> <pattern-slug> [parent-id]
page_from_pattern() {
  local title="$1" slug="$2" pattern="$3" parent="${4:-0}"
  local tmp; tmp="$(mktemp)"
  wp eval "\$p = WP_Block_Patterns_Registry::get_instance()->get_registered('lbana/$pattern'); if (!\$p) { fwrite(STDERR, 'missing pattern lbana/$pattern'); exit(1); } echo \$p['content'];" > "$tmp"
  wp post create "$tmp" --post_type=page --post_status=publish \
    --post_title="$title" --post_name="$slug" --post_parent="$parent" --porcelain
  rm -f "$tmp"
}

say "Creating pages"
home=$(page_from_pattern "Welcome" "welcome" "home")
meet=$(page_from_pattern "Meetings" "meetings" "meetings")
events=$(page_from_pattern "Events" "events" "events")
page_from_pattern "Submit an event" "submit-an-event" "submit-event" "$events" >/dev/null
members=$(page_from_pattern "For the Member" "members" "members")
page_from_pattern "Service portal" "portal" "portal" "$members" >/dev/null
page_from_pattern "Service portal — officer view" "officer-view" "officer-view" "$members" >/dev/null
page_from_pattern "Create an account" "create-account" "register" "$members" >/dev/null
page_from_pattern "For the Public" "public" "public" >/dev/null
page_from_pattern "Contact" "contact" "contact" >/dev/null
page_from_pattern "Donate" "donate" "donate" >/dev/null

wp option update show_on_front page
wp option update page_on_front "$home"

say "Clearing caches"
wp transient delete --all >/dev/null || true
wp cache flush >/dev/null || true
if wp plugin is-active sg-cachepress 2>/dev/null; then wp sg purge >/dev/null 2>&1 || true; fi

say "Checking plugins"
for p in crouton bread mayo-events-manager; do
  if wp plugin is-active "$p" 2>/dev/null; then echo "  ok  $p"; else echo "  !!  $p not active — check the slug with: wp plugin list"; fi
done
for s in crouton mayo_event_list mayo_event_form; do
  if wp eval "exit(shortcode_exists('$s') ? 0 : 1);"; then echo "  ok  [$s]"; else echo "  !!  [$s] not registered — the Events or Meetings page will show the raw shortcode"; fi
done

say "Done. $(wp option get home)"
