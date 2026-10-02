=== TOCguide ===
Contributors: matthummel
Donate link: https://matthummel.com
Tags: table of contents, toc, reading guide, block, study tools
Requires at least: 6.4
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.6.15
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
* Optional Focus control clears the page so only the post copy remains, on a plain sheet. Show page, or Escape, brings the rest of the page back
* Fixed left, on a wide single post or page, keeps the outline in a left column of the main content. The header and the rest of the page stay put. Archives and small screens leave the outline with the content
* Settings → TOCguide → Design sets the style, colours, type size, and spacing. The preview updates as you edit
* Scroll-spy highlights the section currently in view
* Hide bullets, two-column layout, compact spacing, max height with scroll
* Auto-generate the block sitewide: top of content, after the first heading, or Fixed left
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
4. Open **Settings → TOCguide** to auto-generate the block, choose Fixed left, set colours and type, or set a scroll offset.

= Shortcode =

`[tocguide]`

Layout & behavior: `title`, `showtitle`, `titletag`, `h1`–`h6`, `ordered`, `numbering`, `markers`, `collapsible`, `collapsed`, `close`, `focus`, `sticky`, `fixed`, `compact`, `columns`, `underline`, `highlight`, `maxheight`, `min`, `smooth`, `style`, `theme`, `export`. `fixed="1"` keeps the outline in a left column on a wide single post or page.

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

= Where does Fixed left put the outline? =

On a wide screen, a single post or page keeps the outline in a left column of the main content. The header and the rest of the page stay put. On a small screen, and on blog or archive pages, the outline stays with the post.

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

= 1.6.15 =
* Fixed left stays in the post on blog and archive pages. It still sits in a left column of the main content when you open a single post or page on a wide screen.

= 1.6.14 =
* Resume stays hidden until this visit has a saved place in the post.
* The left outline keeps its column on themes that center the content. Closing it leaves a readable Show outline control.

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

Older releases are in the GitHub changelog: https://github.com/matthummel-pa/tocguide/blob/main/CHANGELOG.md

== Upgrade Notice ==

= 1.6.15 =
Fixed left stays with the post on blog and archive pages. On a single post or page, wide screens still keep the outline in a left column of the main content.

= 1.6.13 =
Show page is easier to find in focused reading. Click it or press Escape to restore the rest of the page.

= 1.6.12 =
Focused reading now keeps a Show page control. Press Escape or click it to restore the rest of the page.

= 1.5.0 =
One `tocguide` identity for PHP, CSS, the block, shortcode, and settings. Re-insert the Table of Contents block and re-save Settings if you used an earlier zip.

== License ==

TOCguide is copyright 2026 Matt Hummel and licensed under the GNU General Public License, version 2 or later. That license covers the whole plugin (PHP, JavaScript, CSS, and images). The full text is in `license.txt`.
