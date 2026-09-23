# LinnBentonProto

The website for the Linn Benton Area of Narcotics Anonymous. Staging is at
test.lbana.org.

- `wp-theme/lbana/` is the WordPress block theme. Upload it to
  `wp-content/themes/lbana/`.
- `scripts/lbana-setup.sh` is a one-time setup over SSH. It removes the Divi
  build, creates the pages and sets up the site.
- `COWORK.md` has the full deploy and edit instructions. Read it first.
- `prototypes/` holds the design references. They open in the design tool,
  not in a browser on their own. `portal/index.html` in the theme is the
  compiled portal.

## Updating the site

1. Change the files here and commit.
2. Upload the changed files to `wp-content/themes/lbana/` over SFTP.
3. Bump `Version` in `style.css` and `LBANA_VERSION` in `functions.php`.
4. Purge the SiteGround cache.

Pattern changes don't reach pages that already exist. Edit those pages in
wp-admin as well.
