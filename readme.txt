=== TOCguide ===
Contributors: matthummel
Donate link: https://matthummel.com
Tags: table of contents, toc, reading guide, block, study tools
Requires at least: 6.4
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.6.13
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Table of Contents block with a built-in Reading Guide — section previews, read time, reactions, and citations. Server-rendered, no external APIs.

== Description ==

TOCguide adds a Table of Contents block to the WordPress block editor. It is an independent plugin by Matt Hummel and is not affiliated with any other company or plugin. Optional **Reading Guide** mode turns the outline into a reading companion (section previews, read time, progress). Every feature is rendered server-side or handled in the browser with no external API calls, no accounts, and no tracking.

= Standard TOC features =

* Standard Gutenberg block (`block.json`, live editor preview, Block Styles)
* Choose H1–H6 heading levels
* Numbered or bulleted lists, including nested 1.1.1 numbering
* Five style presets: Default, Minimal, Boxed, Underline, Card
* Show or hide the title; render it as a paragraph or H2–H4
* Smooth scroll with a configurable offset for sticky headers
* Collapsible outline and optional sticky positioning
* Close button hides the outline for the visit; Show outline brings it back
* Optional Focus control clears the page so only the post copy remains, on a plain sheet
* Scroll-spy highlights the section currently in view
* Hide bullets, two-column layout, compact spacing, max height with scroll
* Auto-generate the block sitewide (top of content or after first heading)
* `[tocguide]` shortcode for classic content and theme templates
* Skip a heading with the CSS class `no-toc`
* Accessible `<nav>` landmark with ARIA labels
* Optional ItemList JSON-LD schema markup
* No account, no external API calls, no tracking

= Reading Guide mode =

Enable **Reading Guide** in the block sidebar to transform the TOC into a live reading companion. Every enrichment is computed from your block content — no JavaScript fetches, no third-party services.

**Section content previews** — The opening sentence of every section appears beneath its TOC link, extracted server-side from your block content. Readers scan the outline and know exactly what they are about to read before they click.

**Content density bars** — A subtle bar under each item shows that section's word count as a proportion of the longest section. Readers see at a glance which sections are brief and which are deep dives.

**Per-section read-time estimates** — Word count divided by average reading speed gives a `~N min` badge next to each link. Readers can decide what to read and how long it will take.

**Reading progress** — As a reader scrolls past each section, its TOC item fades out. The outline becomes a live checklist of what has been read and what remains.

**Author section notes** — Write a short teaser or hook for any section directly in the block editor's Section Notes panel. Readers reveal it with a single tap (✍ button). The note is stored in block attributes — no database round-trip.

**Emoji reactions** — Readers react to individual sections (💡 Insightful · ⭐ Saved · 🤔 Unclear · ✅ Got it) with a single click. Choices are stored in the browser's localStorage. No accounts, no server calls, no tracking.

**Per-section academic citations** — A § button on each TOC item copies a fully formatted academic citation for that specific section. Five formats supported: APA, MLA, Chicago, Harvard, and plain link. Citation data comes entirely from WordPress post meta (author, title, site name, published date, permalink). Ideal for research blogs, documentation sites, and long-form journalism.

= Compatibility =

TOCguide is designed to work with every major WordPress stack:

* **Theme builders:** Gutenberg (native block), Elementor, Divi, Beaver Builder, Bricks Builder, WPBakery, Oxygen, Breakdance — use `[tocguide]` in any shortcode/HTML element.
* **SEO plugins:** Yoast SEO, Rank Math, All in One SEO, SEOPress — schema markup is opt-in and off by default; no conflicts with any SEO plugin's TOC schema output.
* **Themes:** Works with any theme — inherits theme fonts and colors; no injected brand styles; tested on Twenty Twenty-Four, Twenty Twenty-Five, Astra, Kadence, GeneratePress, Blocksy.
* **Multilingual:** WPML, Polylang, TranslatePress — fully translation-ready with the standard `tocguide` text domain.
* **PHP:** 7.4, 8.0, 8.1, 8.2, 8.3.
* **WordPress:** 6.4 – 7.1 (tested on all major releases in this range).

= Source and development =

Unminified JavaScript and SCSS ship in `src/`. Compiled assets are in `build/`. From the GitHub repo: `npm install && npm run build`. Node 20+ (see `.nvmrc`). PHP coding standards: `composer install && composer phpcs`.

This plugin does **not** load JavaScript or CSS from a third-party CDN. Front-end assets are enqueued from files inside the plugin. The GitHub Pages marketing site may load a webfont; that is not part of the plugin zip.

= Third-party services =

None. TOCguide does not call remote APIs, does not require an account, and does not send data off-site. Details: https://github.com/matthummel-pa/tocguide/blob/main/PRIVACY.md

= Privacy =

No personal data collection, no marketing cookies, no phone-home. Settings live in the `tocguide_settings` option. Uninstall deletes data only if you opted in under Settings → TOCguide. Deactivate does not delete settings. Optional JSON-LD is off by default. Study tools that use `localStorage` stay on the visitor's device.

= Credits on the front end =

TOCguide does not print a “powered by” or credit link on published posts.

= How to use =

1. Edit a post that contains Heading blocks.
2. Insert the **Table of Contents** block (typically after the introduction).
3. Optionally open the **Reading Guide** panel in the block sidebar to enable any of the reading companion features.
4. Open **Settings → TOCguide** to auto-generate the block sitewide or set a scroll offset.

= Shortcode =

`[tocguide]`

Layout & behavior: `title`, `showtitle`, `titletag`, `h1`–`h6`, `ordered`, `numbering`, `markers`, `collapsible`, `collapsed`, `sticky`, `compact`, `columns`, `underline`, `highlight`, `maxheight`, `min`, `smooth`, `style`.

Reading Guide: `preview="1"` (hover tooltip on links), `guide="1"` (full Reading Guide mode), `previews="1"`, `density="1"`, `readtime="1"`, `progress="1"`, `reactions="1"`, `citations="1"`, `citation="apa|mla|chicago|harvard|plain"`.

Study assistant: `rprogress="1"` (document progress bar), `bookmark="1"` (resume last heading), `rnotes="1"` (private reader note pads).

Examples:

`[tocguide title="On this page" style="boxed" preview="1"]`

`[tocguide guide="1" previews="1" readtime="1" reactions="1" citations="1"]`

= Skip a heading =

Add the CSS class `no-toc` or `tocguide-skip` to a Heading block (Advanced → Additional CSS class(es)).

= Support =

Documentation and support: https://matthummel-pa.github.io/tocguide/
Issues: https://github.com/matthummel-pa/tocguide/issues

== Installation ==

1. Upload the `tocguide` folder to `/wp-content/plugins/`, or install the ZIP through **Plugins → Add New → Upload Plugin**.
2. Activate **TOCguide**.
3. Add the Table of Contents block to a post that contains headings, or enable auto-insert under **Settings → TOCguide**.

== Frequently Asked Questions ==

= Does it work with the classic editor? =

The block is for the block editor. For classic content or a theme template, use the `[tocguide]` shortcode.

= Does it work with Elementor? =

Yes. Add a Shortcode widget to your Elementor layout and enter `[tocguide]`. TOCguide automatically reads headings from your Elementor Heading widgets (via the stored widget JSON) and injects matching ID anchors into the rendered page so all TOC links scroll correctly. No extra plugin or configuration needed.

= Does it work with Divi? =

Yes. Add a Code module or Shortcode module to your Divi section and enter `[tocguide]`. TOCguide scans the rendered page HTML for headings and injects the correct anchor IDs.

= Does it work with Bricks Builder? =

Yes. Paste `[tocguide]` into a Bricks Code element or Shortcode element. TOCguide reads headings directly from Bricks' element meta (heading and rich-text elements) and injects the correct IDs.

= Does it work with Beaver Builder? =

Yes. Add an HTML module or Shortcode module containing `[tocguide]`. TOCguide uses `[tocguide]` detected in Beaver Builder data to ensure headings receive the correct anchor IDs.

= Does it work with WPBakery / Visual Composer? =

Yes. Add a Raw HTML element or Shortcode element with `[tocguide]`. TOCguide scans the rendered HTML for headings and injects IDs automatically.

= Does it work with Oxygen Builder or Breakdance? =

Yes. Insert `[tocguide]` via a Shortcode element or Code Block. TOCguide detects the shortcode in Oxygen/Breakdance meta and handles heading ID injection through the rendered HTML.

= Can it co-exist with my SEO plugin (Yoast, Rank Math)? =

Yes. The optional JSON-LD schema is off by default. If you enable it, turn off TOC schema in your SEO plugin (or vice versa) to avoid duplicate structured data. The `<nav>` landmark itself does not conflict with any SEO plugin.

= Will the links scroll to my headings? =

Yes. Matching anchor IDs are added automatically. Custom HTML anchors on a heading are respected. Smooth scrolling and a pixel offset are configurable under Settings → TOCguide.

= How are section previews generated? =

The server walks the parsed block content and extracts the first ~20 words after each heading. No JavaScript fetch, no API, no external service.

= How do emoji reactions work? =

Each reader's choices are stored in their browser's `localStorage`. Nothing is sent to your server or any third party. If two readers use the same browser profile they see the same reactions, but reactions are per-device and never aggregated.

= How are citations generated? =

Citations are built from data already in WordPress: the post author's display name, post title, blog name, published date, and permalink. The § button constructs the formatted string in JavaScript and copies it to the clipboard. The citation includes the section anchor (`#slug`) so it links directly to that section.

= Can I hide the TOC on short posts? =

Yes. Set **Minimum headings** in Settings → TOCguide.

= Does it work in columns or groups? =

Yes. Headings nested inside Group, Columns, Cover, and similar blocks are included in both the TOC and the Reading Guide section data.

= Can I have more than one TOC on a page? =

Each instance lists the same headings from that post. Prefer one outline per page.

= Is any data sent off-site? =

No. Zero external network requests from TOCguide in any mode.

= What license is TOCguide under? =

GNU GPLv2 or later, covering the whole plugin (PHP, JavaScript, CSS, and images). Copyright Matt Hummel. Full text: `license.txt`.

= Is TOCguide affiliated with another product? =

No. Independent plugin by Matt Hummel. Display name TOCguide; slug, folder, and text domain `tocguide`. That naming is intentional for WordPress.org guideline 17.

= Does it load scripts or fonts from a CDN? =

No. Plugin CSS and JS are local. Core-bundled libraries are used where WordPress already ships them. No remote executable code.

= Who can change settings? =

Users with the `manage_options` capability (typically administrators). The welcome notice is dismissible and uses a nonce.

= What happens when I delete the plugin? =

Deactivate leaves settings in place. Delete runs `uninstall.php`, which removes stored options only if you enabled “delete data on uninstall” in Settings.

== Screenshots ==

1. Front-end Table of Contents with optional Reading Guide (previews, density, read time).
2. Settings → TOCguide: auto-insert, design tokens, study tools, accessibility.
3. Block sidebar in the editor (heading levels, layout, Reading Guide).
4. Docs & Support tab in wp-admin (links to the documentation site).

== Changelog ==

= 1.6.13 =
* Show page in focused reading is a floating control with an expand icon, a short restore hint, and an Esc keycap so it is obvious how to leave Focus.

= 1.6.12 =
* Focused reading keeps a Show page control (and Escape) so you can restore the rest of the page after clicking Focus.

= 1.6.11 =
* Fixed left stays inside the main content area. The rest of the page does not shift.
* The left outline has no scrollbar. Focus, collapse, close, and Resume sit on one line under the title.

= 1.6.10 =
* Design changes show in a preview that stays on screen. A whole number such as 15 is saved as 15px, and a small decimal such as 0.95 is saved as 0.95rem, so a size you type is the size that prints.
* Settings sections keep unsaved changes when you switch between them. Colour pickers and the style choices are keyboard accessible, and the preview links show the focus ring.

= 1.6.9 =
* Focus clears the page so only the post copy remains, on a plain sheet. Focus again brings the rest of the page back.

= 1.6.8 =
* The left-edge outline stays open, so the heading links stay on screen. Collapsing hides the list and the tools together.
* Desktop, tablet, and mobile share one size for type, markers, and header buttons. Small screens use less padding unless Design sets a padding.
* Settings → TOCguide → Design sets the style, colours, type size, and spacing, including on the left-edge outline. A size you enter is the size that prints.

= 1.6.7 =
* On tablet and desktop, a top-of-content outline uses slightly smaller type, markers, and padding so the article keeps more of the screen.
* Open, close, and Focus are smaller, with more space between them.

= 1.6.6 =
* Outline text, title, markers, and header buttons scale with the width of the outline box.
* On the left-edge panel, that narrower box uses smaller type and controls, with tighter padding so the rows stay on one line.
* On small screens the outline padding tightens, and two columns stack when the outline itself is narrow.

= 1.6.5 =
* Settings → Auto-insert → Position includes Fixed left. On wide screens the outline docks to the left edge and the article starts beside it.
* Close and expand controls are circles, with more space around the outline rows and header buttons.
* Docked outline uses a dark panel, a thick green border, and right-side rounded corners. Text, links, and controls meet WCAG AA contrast.
* Outline items, read-time, and previews have more space, and secondary text stays dark enough to read.

= 1.6.4 =
* A close button hides the outline. Show outline brings it back for that visit.
* Focused reading is optional. Focus clears the page so only the post copy remains, on a plain sheet.

= 1.6.3 =
* The outline has default space above and below it. A margin set on the block still replaces that default.
* Theme checklist marks no longer paint a check in front of each heading. The outline keeps its own dot or number.

= 1.6.2 =
* Fix: Design colours, fonts, and spacing stay on the outline, including the title, when the theme styles every heading and link.
* Minimal, Boxed, Underline, and Card are distinct, and Compact tightens padding and type. Saved settings clear the page cache so the front end updates.
* Settings → TOCguide is split into section tabs, with a live outline preview.

= 1.6.1 =
* Fix: with Exclude theme styles on, the outline is not a theme list, so theme counters can no longer print “0.” in front of every heading. Note and citation buttons stay on the right of the heading.
* Note, collapse, resume, and export controls share one icon-button style.
* Design settings: link case, title colour, icon colour, number style (circle, square, or plain), and shadow. Per-block Theme styles can follow the site setting or override it. Shortcode: `theme="exclude"` or `theme="include"`.

= 1.6.0 =
* Fix: section labels no longer pick up a theme list counter that printed “0.” in front of every heading. Numbered lists use real badges (1, 2, 3 or 1.1) when Exclude theme styles is on.
* The note, citation, resume, and export controls are icon buttons. The note button sits to the right of the heading instead of on the line below.
* Settings → TOCguide → Design: Exclude theme styles (on by default), plus font, title size and weight, letter spacing, item spacing, accent, hover, and number-badge colours.

= 1.5.0 =
* One identity: PHP (`tocguide_*` / `TOCguide_*` / `TOCGUIDE_*`), CSS (`.tocguide`, `--tocguide-*`), block `tocguide/table-of-contents`, shortcode `[tocguide]`, option `tocguide_settings`, skip class `tocguide-skip`.
* Docs, support site, and in-admin Docs & Support URLs: `matthummel-pa.github.io/tocguide`.
* GitHub repository: `matthummel-pa/tocguide`.
* Breaking: re-insert the block and re-save Settings if you used an earlier zip. No automatic migration of old keys.

= 1.4.0 =
* Rebrand for WordPress.org guideline 17: display name is now TOCguide and the plugin slug / text domain / main file are `tocguide`. Not affiliated with any other product.
* Shortcode `[tocguide]`.
* Existing blocks, CSS classes, and settings keys stayed on the previous identifier so current content kept working.

= 1.3.3 =
* Fix: `$guide_attrs` array was re-initialized to empty on the line after `data-tocguide-focus` was written to it — the accessibility focus-ring attribute was always discarded. Moved array init before the focus-style assignment.
* Fix: global background and font custom properties (`--tocguide-bg`, `--tocguide-color`, `--tocguide-link-color`, etc.) now correctly propagate to the `<nav>` style attribute for both the block and shortcode/auto-insert rendering paths.

= 1.3.2 =
* Fix: short description in `readme.txt` trimmed from 175 to 145 characters — WordPress.org parser silently truncates anything over 150 characters.

= 1.3.1 =
* Fix: escape `$swatch_val` at the point of `printf()` output in the admin colour-picker widget — resolves `WordPress.Security.EscapeOutput` PHPCS error flagged by WordPress.org reviewers.
* Fix: added missing PHPDoc block for `TOCguide_Settings::sanitize()`.
* Fix: inline comment casing and full-stop in `class-tocguide-plugin.php` to satisfy PHPCS `Squiz.Commenting.InlineComment`.
* Chore: PHPCBF auto-fixed 181 array-alignment warnings across `class-tocguide-settings.php`, `class-tocguide-plugin.php`, and `class-tocguide-headings.php`.

= 1.3.0 =
* New: **Design & Appearance** section in Settings → TOCguide — set global background, text, link, and border colours; font size, weight, and line height; border width / style / radius; and padding. All values are applied as CSS custom properties so block-editor per-instance overrides still win.
* New: **Reading Guide & Study Tools** section in settings — configure global defaults for hover section preview, guide mode, density bars, read time, progress fade, emoji reactions, and academic citations.
* New: **Study Tools & Export** section in settings — global defaults for reading progress bar, resume bookmark, reader note pads, and export toolbar.
* New: **Accessibility** section in settings — choose focus ring style: Default, Bold (3 px, WCAG AA), or High-contrast (yellow background, WCAG AAA).
* New: Admin JS syncs hex colour text inputs with companion colour swatches and toggles guide-mode sub-options conditionally.
* Fix: CSS preset styles (minimal, boxed, underline, card) correctly override global design custom properties.

= 1.2.2 =
* Fix: `TOCGUIDE_VERSION` constant was stuck at `1.2.0`; now reads `1.2.2` correctly (admin version badge and `Version %s` string now match the plugin header).
* Fix: `Tested up to` updated from 6.7 to 7.1 (WordPress 7.1 "Mary Lou", released 2026-08-19).
* Fix: removed third-party trademark `elementor` from tags; replaced with `study tools`.
* Fix: PHP coding-standards indentation in shortcode attribute registration and admin settings view.

= 1.2.1 =
* Fix: debounced resume-bookmark localStorage writes from every IntersectionObserver callback to every 500 ms, preventing redundant writes during fast scrolling.
* Docs: completed v1.1.0 CHANGELOG entry (hover preview, export/print toolbar, full accessibility and page-builder details).
* Docs: added "What's new" tables and "Performance" section to README for reviewers.

= 1.2.0 =
* New: **Reader note pads** — a 📝 button per section lets readers jot personal notes stored privately in localStorage. No account, no server. Enable with `rnotes="1"` in the shortcode or the new "Reader note pads" toggle in the block sidebar.
* New: **Reading progress bar** — a thin animated bar shows 0–100 % of the document read (based on headings scrolled past via IntersectionObserver). Enable with `rprogress="1"`.
* New: **Resume reading bookmark** — automatically bookmarks the last-read heading in localStorage; shows a "↩ Resume" button on return visits. Enable with `bookmark="1"`.
* New: **Section Planner** — the "Section Notes" sidebar panel is now "Section Planner" with per-heading writing status (✏️ Draft / 🔄 In progress / ✅ Done) tracked in the editor canvas and a reader-facing teaser note.
* New: **Total read-time badge** — when Reading Guide + read-time are active, the TOC header shows the aggregated total read time for the entire post.
* New shortcode attributes: `rprogress`, `bookmark`, `rnotes`.
* New block attributes: `showReaderNotes`, `showReadingProgress`, `showBookmark`, `sectionStatus`.

= 1.1.0 =
* New: **Reading Guide mode** — enable per-block to show section content previews (server-extracted, no JS fetch), visual content-density bars, per-section read-time estimates, and scroll-based reading progress.
* New: **Author section notes** — type a teaser or hook per heading in the block sidebar (Section Notes panel); readers reveal it with a ✍ button.
* New: **Emoji reactions** — readers react per section (💡 ⭐ 🤔 ✅) with state stored in localStorage; no accounts, no server calls.
* New: **Per-section academic citations** — § button copies APA, MLA, Chicago, Harvard, or plain-link citation built from WordPress post meta; no external API.
* New: **Hover section preview** — floating tooltip on TOC link hover/focus; viewport-aware positioning; `aria-describedby` for screen readers.
* Compatibility: **Elementor** — heading data extracted from `_elementor_data` widget JSON; IDs injected into rendered output via `the_content` at priority 999.
* Compatibility: **Bricks Builder** — heading and rich-text elements parsed from `_bricks_page_content_2` meta.
* Compatibility: **Divi, WPBakery, Oxygen, Beaver Builder, Breakdance** — HTML-based heading scan as a generic fallback; IDs injected into rendered HTML so all builder-generated headings are reachable from TOC links.
* New block attributes: `guideMode`, `showPreviews`, `showDensity`, `showReadTime`, `trackProgress`, `showReactions`, `showCitations`, `citationStyle`, `sectionNotes`, `previewOnHover`.
* Block internals: `TOCguide_Headings::get_sections()` (word count + preview extraction), `citation_meta()`, `get_all_from_html()`, `get_all_from_elementor()`, `get_all_from_bricks()`, `inject_ids_in_html()`; updated `render_list()` and `render_nav()`.

= 1.0.2 =
* Removed `load_plugin_textdomain()` — WordPress.org directory auto-loads the `tocguide` text domain since WP 4.6.
* Prefixed template and uninstall variables with `tocguide_`.
* `Tested up to` 6.7.

= 1.0.1 =
* First installable GitHub zip of the 1.0 feature set (build + plugin-zip on tag).
* Docs site, 100% GPLv2-or-later packaging, translation template, and WordPress.org banner/icon assets.

= 1.0.0 =
* Live editor preview, style presets, collapse, sticky, and scroll-spy.
* Smooth scroll with offset; H5/H6 support.
* Auto-generate the Gutenberg block, `[tocguide]` shortcode, skip class `no-toc`.
* Settings and Docs & Support admin screens.
* Heading IDs via WP_HTML_Tag_Processor; custom anchors respected.

== Upgrade Notice ==

= 1.6.13 =
Show page is easier to find in focused reading. Click it or press Escape to restore the rest of the page.

= 1.6.12 =
Focused reading now keeps a Show page control. Press Escape or click it to restore the rest of the page.

= 1.5.0 =
One `tocguide` identity for PHP, CSS, the block, shortcode, and settings. Re-insert the Table of Contents block and re-save Settings if you used an earlier zip.

= 1.4.0 =
Display name and WordPress.org slug are now TOCguide / tocguide so the listing is clearly independent. Existing blocks and CSS from that release kept working.

= 1.3.3 =
Bug fix: custom background and font colours set in Settings → TOCguide now display correctly. The accessibility focus-ring setting also now applies as intended.

= 1.3.2 =
Readme-only fix: short description trimmed to satisfy the WordPress.org 150-character parser limit.

= 1.3.1 =
Security/code-quality fix: escaping error in the settings colour-picker widget resolved. No functional changes.

= 1.3.0 =
Major settings expansion: global design customization (colours, typography, borders), Reading Guide & Study Tools defaults, and a new Accessibility section for focus ring styles. No changes to existing block output.

= 1.2.2 =
WordPress.org compliance fixes: corrects the in-admin version badge, updates "Tested up to" to 7.1, and removes a disallowed trademark tag. No functional changes.

= 1.2.1 =
Performance fix for the resume bookmark feature (debounced localStorage writes). Documentation updates only — no functional changes to the block, shortcode, or settings.

= 1.2.0 =
Adds study-assistant features: reader note pads, a reading progress bar, resume bookmark, Section Planner writing status, and a total read-time badge. No breaking changes — all new features are opt-in via the block sidebar or shortcode.

= 1.1.0 =
Adds Reading Guide mode: section previews, density bars, read-time estimates, reading progress, author notes, emoji reactions, and per-section academic citations. No breaking changes — all new features are opt-in via the block sidebar.

= 1.0.2 =
Plugin Check and readme cleanup for the WordPress.org review. Same block, settings, and shortcode as 1.0.1.

== License ==

TOCguide is copyright 2026 Matt Hummel and licensed under the GNU General Public License, version 2 or later. That license covers the whole plugin (PHP, JavaScript, CSS, and images). The full text is in `license.txt`.
