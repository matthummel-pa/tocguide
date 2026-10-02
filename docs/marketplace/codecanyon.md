# CodeCanyon (Envato) listing

WordPress plugins are sold on **CodeCanyon**, category **WordPress**, not on ThemeForest.

Envato WordPress items can use a **split license** (PHP GPL, other assets under the Regular License) or **100% GPL**. TOCguide uses **100% GPLv2 or later** so the same zip stays eligible for WordPress.org. On the item form, choose 100% GPL — not split. Details: [licensing.md](licensing.md).

You are selling a convenient package, documentation, and item support — not a proprietary PHP lock-in.

## Item title (recommended)

**TOCguide — Table of Contents Block for WordPress (Gutenberg)**

## Tags

`wordpress` `toc` `table of contents` `gutenberg` `block` `seo` `navigation`

## Short description (~150 characters)

Server-rendered Table of Contents block for Gutenberg. Auto outline from headings, live preview, auto-insert, shortcode, accessible and SEO-friendly.

## Reader features to mention on the item page

- **Fixed left:** on a wide single post or page (1100px and up), an 18rem column inside the main content. Archives, the blog index, and small screens leave the outline with the content. The header stays put.
- **Focus:** optional. **Show page**, **Bring the rest back**, or Escape restores the page. Focus paper uses Segoe UI / system UI sans.
- **Design:** Settings → TOCguide → Design, with a preview that updates as you edit.
- **Shortcode:** `close`, `focus`, `fixed`, `theme` (`inherit`, `exclude`, `include`), `export` (Copy, .md, .doc, Print). On/off values: `1`, `true`, `yes`, `on`.

## What to upload

1. `npm run build && npm run plugin-zip` → installable `tocguide.zip`
2. A documentation ZIP or the HTML in `docs/documentation.html` (offline docs satisfy Envato)
3. Preview graphics: logo, settings, front-end TOC, style presets
4. Optional video: insert block → publish → click a link

## Requirements checklist (already implemented)

See [Envato WordPress Plugin Requirements](https://help.author.envato.com/hc/en-us/articles/360000510603-WordPress-Plugin-Requirements).

- Unique prefix `tocguide_` / `TOCguide_`
- Admin separated with `is_admin()`
- Settings API + `current_user_can( 'manage_options' )` + nonces
- Escaping on output, sanitization on input
- Assets via `wp_enqueue_*` / `block.json` (no direct `<script>` in PHP)
- No jQuery migrate hacks; no deregister of core jQuery
- `uninstall.php` does not run on deactivate; deletion is opt-in
- Translations: literal text domain `tocguide`, `languages/tocguide.pot`
- No third-party tracking
- No nagging review banners except a dismissible welcome notice
- Gutenberg block via `block.json` + dynamic `render.php`

## Support text for the item page

Support is provided via the item’s Envato support tab and GitHub issues for the GPL source. Policy: [SUPPORT.md](../../SUPPORT.md).

## Pricing note

A focused, well-documented TOC block typically sits in the lower CodeCanyon price band. Compete on quality, docs, and support — not on shipping a 40-block suite.
