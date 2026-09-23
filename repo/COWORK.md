# For Cowork — replace the Divi build on test.lbana.org

The site on test.lbana.org was built with Divi from an older draft. Replace it
with the WordPress block theme in `wp-theme/lbana/`. It is a straight port of
`LBANA Site - Slate.dc.html` — that file is what the finished site should look
like. No Divi, no page builder.

You have SSH and SFTP to the SiteGround account (SSH port 18765).

## 1. Upload

Upload the whole `wp-theme/lbana/` folder to:

```
~/www/test.lbana.org/public_html/wp-content/themes/lbana/
```

`portal/index.html` inside it is about 7 MB. It must go up as-is — it is the
service portal, fully self-contained.

Upload `scripts/lbana-setup.sh` to `~/lbana-setup.sh`.

## 2. Run the setup

```
ssh -p 18765 <user>@<host>
cd ~/www/test.lbana.org/public_html
bash ~/lbana-setup.sh
```

It backs up the database to `~/lbana-backups/` first, then:

- activates the `lbana` theme and deletes Divi and the child theme
- deletes Divi Theme Builder layouts, all pages and all menus
- deactivates WooCommerce (the literature store is off the site)
- sets `/%postname%/` permalinks, discourages search engines, closes
  registration and comments, sets the Pacific timezone
- creates the eleven pages from the theme's patterns and sets Welcome as the
  front page
- flushes caches and reports on the plugins

Run it once. Re-running recreates the pages and throws away any text officers
have edited since.

## 3. Fix whatever the script flags

The last block of output checks the plugins and their shortcodes:

- **`[crouton]`** is used by Meetings, through `[lbana_meetings]` in
  `inc/bmlt-and-accounts.php`. If Crouton's shortcode has a different name or
  attributes, edit `lbana_meetings_shortcode()` there and nothing else.
- **`[mayo_event_list]`** and **`[mayo_event_form show_flyer="true"]`** are used
  by Events and Submit an event, in `patterns/events.php` and
  `patterns/submit-event.php`. Check Mayo's settings screen for the real names.
  If they differ, fix them in the pattern files **and** on the two pages in
  wp-admin (the pages hold a copy of the pattern).
- **Bread** is linked as `/?current-meeting-list=1` on Meetings and For the
  Public. Configure its format once in Bread's settings so that URL produces
  the PDF.

## 4. Check it

Open each page on a phone-width window and a desktop window, next to
`LBANA Site - Slate.dc.html`:

| URL | Should show |
| --- | --- |
| `/` | Hero, "Starting next" from BMLT, four stats, what to expect, the area, blue banner |
| `/meetings/` | Crouton finder, print link, two notes |
| `/events/` | Mayo list, submit button |
| `/events/submit-an-event/` | Flyer note, Mayo form with image upload |
| `/members/` | Next area meeting (Lebanon), service cards, documents, support + get involved |
| `/members/portal/` | The portal in a frame |
| `/members/officer-view/` | The portal with the officer view on. Unlisted. |
| `/public/` | Split with the river photo, three columns |
| `/contact/` | Facts on the left, message form on the right |
| `/donate/` | PayPal button and QR |
| `/members/create-account/` | "Accounts are not open yet" — correct for the test |

Phone width should show the Menu button, the drawer, and the fixed two-button
call bar at the bottom.

## How editing works after this

**Officers, in wp-admin:**

- **Page text.** Pages open in the block editor with the layout locked. Text,
  button labels and images can be changed; blocks can't be moved, added or
  deleted.
- **Settings → Area meeting.** Address, room, PR and ASC times, the list of
  Sundays to skip, and an optional short notice. The next meeting date is
  computed from these and shows everywhere it's used.
- **Media Library → a PDF → "LBANA document".** Pick Agenda, Minutes, Form or
  Guidelines and it appears on For the Member, newest first. The title shown is
  the media title; the caption, if any, is the note under it.

**Code, by Cowork or Codex:** everything else. Layout and copy structure
(`patterns/`), header and footer (`inc/site.php`), styles (`style.css`), BMLT
and accounts (`inc/bmlt-and-accounts.php`), area settings and documents
(`inc/area.php`), and the portal (`portal/index.html`, rebuilt from
`LBANA Service Portal.dc.html`).

To ship a code change: upload the changed files over SFTP, bump `Version` in
`style.css` and `LBANA_VERSION` in `functions.php` so browsers drop the cached
CSS, then purge the SiteGround cache. A change to a pattern file does **not**
update a page that already exists — edit the page in wp-admin too, or delete
and recreate it from the pattern.

## Fixed facts

- Helpline 877-233-4287 (877-ADDICTS).
- Area service: first Sunday, 525 N Santiam Hwy, Lebanon, OR 97355,
  conference room. PR 11:00 AM, ASC 2:00 PM. Editable in Settings → Area
  meeting.
- BMLT root `https://bmlt.wszf.org/main_server/`, service body 35.
- Donations: https://paypal.me/LBABasket.
- No login for the test. Registration code exists but stays off until
  `define( 'LBANA_REGISTRATION_OPEN', true );` goes in `wp-config.php`.
- Members-only gating is also off (`LBANA_GATE_MEMBERS`).

## Don't

- Don't install Divi, Elementor or any page builder.
- Don't turn on Settings → General → "Anyone can register".
- Don't let the staging site be indexed.
- Don't add personal names to group reports.
