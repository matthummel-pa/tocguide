# Changelog

All notable changes to TOCguide are documented here.
This project adheres to [Semantic Versioning](https://semver.org/).

## [1.6.13] - 2026-09-30

### Changed
- **Show page** in focused reading is a centered floating control with an expand icon, a short restore hint, and an Esc keycap so it is obvious how to leave Focus.

## [1.6.12] - 2026-09-30

### Fixed
- Focused reading no longer hides the control that turns it off. Click **Show page** (or press Escape) to restore the header, sidebar, footer, and outline.

## [1.6.11] - 2026-09-30

### Changed
- Fixed left stays inside the main content area. The rest of the page does not shift.
- The left outline has no scrollbar. Focus, collapse, close, and Resume sit on one line under the title.

## [1.6.10] - 2026-09-30

### Changed
- The settings preview stays beside the form and updates as you edit colours, type, spacing, style, and the focus ring.
- A whole number in a size field is saved as pixels. A small decimal is saved as rem. An invalid size is marked and is not applied.
- Switching settings sections keeps what you have typed. Save still publishes that look on every outline.

## [1.6.9] - 2026-09-30

### Changed
- Focus clears the header, sidebar, footer, and outline chrome. The post copy stays, set on a plain sheet. Focus again brings the page back.

## [1.6.8] - 2026-09-30

### Fixed
- The left-edge outline stays open, so the heading links stay on screen. Start collapsed still applies to an outline inside the content. Collapsing hides the list and the tools together.

### Changed
- Desktop, tablet, and mobile share one size for type, markers, and header buttons.
- On small screens the outline uses less padding and tighter row spacing, unless a Design setting supplies those values.
- Settings → TOCguide → Design includes the style preset. Colours, type size, title size, padding, and item spacing apply on every outline, including the left-edge panel.

## [1.6.7] - 2026-09-30

### Changed
- On tablet and desktop, a top-of-content outline uses slightly smaller type, markers, and padding so the article keeps more of the screen.
- Open, close, and Focus are smaller, with more space between them. The left-edge panel is unchanged.

## [1.6.6] - 2026-09-30

### Changed
- Outline text, the title, markers, gaps, and header buttons scale with the width of the outline box.
- On the left-edge panel, that narrower box uses smaller type and controls, with tighter padding so the rows stay on one line.
- On small screens the outline padding tightens. Two columns stack when the outline itself is narrow, including in a sidebar on a wide page.

## [1.6.5] - 2026-09-30

### Changed
- Settings → TOCguide → Auto-insert → Position includes Fixed left. On screens 1100px and wider, that choice docks the outline to the left edge. The article starts beside it, so the panel does not cover the text.
- Close and expand controls are circles, with more space inside the button and between outline rows.
- The docked outline uses a dark panel, a 3px green border, and rounded corners on the side away from the screen edge.
- Title, links, the active section, markers, and buttons on the dock meet WCAG AA contrast.
- List items have more space. Read-time, previews, and sections already read stay dark enough to read.

## [1.6.4] - 2026-09-29

### Added
- A close button hides the outline. Show outline brings it back for that visit.
- Focused reading is an optional Focus control. It dims the rest of the page and keeps the section in view clear.

## [1.6.3] - 2026-09-23

### Changed
- The Table of Contents block and the `[tocguide]` shortcode have a default `1.5rem` margin above and below the outline. A margin chosen in the block sidebar replaces it.

### Fixed
- Theme checklist styles no longer draw a checkmark on each outline row. The outline uses its own dot or number badge.

## [1.6.2] - 2026-09-23

### Fixed
- Design colours, fonts, borders, and spacing apply to the outline title and links even when the theme sets its own heading and link styles.
- Minimal, Boxed, Underline, and Card each change the outline’s frame. Compact tightens padding, type, and number badges. Turning markers off hides the badges as well as the theme’s list markers.
- Padding accepts one to four lengths (`1rem` or `1.25rem 1.5rem`).
- Saving settings clears common full-page caches so the new look shows up.

### Changed
- Settings → TOCguide uses a tab for each section (Reading, Auto-insert, Design, Reading Guide, Study tools, SEO & data, Accessibility) and shows a live outline preview.

## [1.6.1] - 2026-09-23

### Fixed
- Exclude theme styles now renders the outline as a list of `div`s (`role="list"` / `role="listitem"`) so theme `ol` / `li::before` counters cannot print a leading “0.”. Number badges stay TOCguide’s own.
- Note, citation, and other row buttons stay on the right of the heading instead of wrapping underneath it.

### Changed
- Collapse, note, resume, and export controls use one icon-button style (accent border, filled on hover).
- Design settings add link case, title colour, icon colour, number style (circle, square, plain), and a soft or medium shadow.
- Each block can follow the site “Exclude theme styles” setting or override it. Shortcode: `theme="exclude"` or `theme="include"`.

## [1.6.0] - 2026-09-23

### Fixed
- Theme list counters no longer print a leading “0.” on every section when **Exclude theme styles** is on. Numbered outlines use text badges (`1`, `1.1`) instead of CSS `counters()`.

### Changed
- Reader-note, author-note, and citation buttons sit in the heading row, to the right of the title. Resume and export controls use the same icon-button treatment.
- **Settings → TOCguide → Design** can exclude theme fonts, link decorations, and list markers (on by default). New controls: font, title size, title weight, letter spacing, item spacing, link hover, accent, and number-badge colours. Fonts are device sans or monospace only — nothing is loaded from a CDN.

## [1.5.0] - 2026-09-21

One identity for the whole product: **TOCguide** / **`tocguide`**. Built for WordPress.org guideline 17 (no implied affiliation) and for a slug that matches the folder, text domain, block name, and GitHub repo.

### Added
- Docs, support, and in-admin **Docs & Support** links all use `https://matthummel-pa.github.io/tocguide/` (`index.html`, `documentation.html`, `support.html`).
- Directory-facing copy: independence statement, third-party/service disclosure (none), uninstall opt-in, `manage_options` + nonce notes, screenshot captions.

### Changed
- PHP: `tocguide_*` functions, `TOCguide_*` classes, `TOCGUIDE_*` constants; includes `class-tocguide-*.php`.
- Gutenberg block: `tocguide/table-of-contents` (class `.wp-block-tocguide-table-of-contents`).
- Option key: `tocguide_settings`. Shortcode: `[tocguide]` only. Skip class: `tocguide-skip` (plus `no-toc`).
- CSS root class `.tocguide` and custom properties `--tocguide-*`.
- Brand SVGs, GitHub Pages, and the GitHub repo are `matthummel-pa/tocguide`.

### Breaking
- Existing blocks, custom CSS, and stored settings that used the previous prefix will not match until you re-insert the block, update CSS, and re-save **Settings → TOCguide**. There is no automatic key migration.

### Fixed
- PHPCS `DoubleArrowNotAligned` on the `<nav>` wrapper attributes after the longer `data-tocguide-*` keys.

## [1.4.0] - 2026-09-19

### Changed
- **WordPress.org identity:** display name is **TOCguide**; slug, folder, text domain, and main file are **`tocguide`**. This answers the plugins team request to make it clear the plugin is not affiliated with any other entity (guideline 17). The previous listing name was too close to other `*flow` brands.
- Public shortcode became `[tocguide]`.
- User-facing admin labels, docs, and directory artwork use TOCguide.
- Translation text domain is now the literal `tocguide`.

### Unchanged (on purpose, until 1.5.0)
- Gutenberg block name, CSS classes/variables, option keys, and PHP prefixes stayed on the previous identifier so existing content kept working.
- GitHub repository name was still the old repo slug until it was renamed to `tocguide`.

## [1.3.3] - 2026-09-09

### Fixed
- `$guide_attrs` was reset to `array()` immediately after `data-tocguide-focus` was written into it — the accessibility focus-ring `data-` attribute was always discarded. Moved the array initialisation to _before_ the focus-style assignment so the attribute is correctly merged into the `<nav>` wrapper for both the block and shortcode/auto-insert paths.
- As a consequence, the global design CSS custom properties (`--tocguide-bg`, `--tocguide-color`, `--tocguide-link-color`, `--tocguide-font-size`, `--tocguide-font-weight`, `--tocguide-line-height`, `--tocguide-border-*`, `--tocguide-radius`, `--tocguide-padding`) set in Settings → Design & Appearance now reliably propagate through `$style_attr` to the rendered `<nav>` element and are picked up by `var()` references in `style.scss`.

## [1.3.1] - 2026-09-09

### Fixed
- **`WordPress.Security.EscapeOutput`**: `$swatch_val` in the admin colour-picker `printf()` was pre-escaped at construction time but not at the call site — PHPCS (and WordPress.org reviewers) require escaping _at the point of output_. Moved `esc_attr()` to the `printf` argument and removed the premature escape.
- Added missing PHPDoc block for `TOCguide_Settings::sanitize()`.
- Inline comment in `class-tocguide-plugin.php` now ends with a full stop (WPCS `Squiz.Commenting.InlineComment`).
- PHPCBF auto-corrected 181 array-alignment and indentation warnings across three PHP files.

## [1.3.0] - 2026-09-08

### Added
- **Settings → Design & Appearance**: global background colour, text colour, link colour, font size, font weight, line height, border (width / style / colour / radius), and padding. Applied as CSS custom properties (`--tocguide-bg`, `--tocguide-color`, `--tocguide-link-color`, `--tocguide-font-size`, etc.) so per-block editor overrides still cascade correctly.
- **Settings → Reading Guide & Study Tools**: global defaults for hover preview, guide mode, section previews, density bars, per-section read time, progress fade, emoji reactions, academic citations, and citation format.
- **Settings → Study Tools & Export**: global defaults for reading progress bar, resume bookmark, reader note pads, and export toolbar.
- **Settings → Accessibility**: focus ring style picker — Default (underline), Bold (3 px outline, WCAG 2.1 AA), or High-contrast (yellow background + black outline, WCAG 2.1 AAA). Rendered as `data-tocguide-focus` attribute on the `<nav>`.
- `admin/js/admin.js`: vanilla JS that syncs hex text inputs with companion `<input type=color>` swatches and toggles guide-mode sub-options when the guide-mode checkbox is toggled.
- `admin/css/admin.css`: styles for color-picker widget, section badge chips, and indented guide sub-options panel.

### Changed
- CSS custom properties now drive the root border, padding, background, font-size, and link colour so global design settings layer cleanly beneath block-level inline styles from Gutenberg's block supports.
- `is-style-minimal` preset explicitly resets design custom props to ensure the preset always wins over any global colour/padding setting.
- `block_attributes()` now passes Reading Guide, Study Tools, and Export defaults to auto-inserted blocks.

## [1.2.2] - 2026-09-08

### Fixed
- `TOCGUIDE_VERSION` constant was `'1.2.0'` while the plugin header read `1.2.1` / `1.2.2`; in-admin version badge now matches.
- `Tested up to` updated from `6.7` to `7.1` (WordPress 7.1 "Mary Lou" released 2026-08-19).
- Removed third-party trademark tag `elementor`; replaced with descriptive `study tools`.
- PHP WPCS: fixed 3-tab indentation on Study-tool shortcode attributes (should be 4-tab like surrounding code).
- Admin view: fixed indentation on shortcode-docs `<p>` tags in `admin/views/settings.php`.

## [1.2.1] - 2026-09-08

### Changed
- Debounced bookmark `localStorage` writes to 500 ms (previously wrote on every `IntersectionObserver` callback during scroll — could fire once per heading during fast scrolling).

### Documentation
- `CHANGELOG.md`: completed v1.1.0 entry with hover preview, export/print toolbar, accessibility, and full page-builder compatibility details.
- `README.md`: added "What's new" feature tables for v1.1.0 and v1.2.0 for WordPress.org reviewers; added "Performance" section documenting asset-loading gates, `IntersectionObserver` use, no remote calls, debounced storage writes, and PHP static caches.

## [1.2.0] - 2026-09-08

### Added
- **Reader note pads** — a 📝 button per section lets readers jot personal notes stored privately in localStorage. No account, no server. Enable with `rnotes="1"` in the shortcode or the "Reader note pads" toggle in the block sidebar.
- **Reading progress bar** — a thin animated bar shows 0–100% of the document read (headings scrolled past via IntersectionObserver). Enable with `rprogress="1"`.
- **Resume reading bookmark** — automatically bookmarks the last-read heading in localStorage and shows a "↩ Resume" button on return visits. Enable with `bookmark="1"`.
- **Section Planner** — the "Section Notes" sidebar panel is now "Section Planner" with per-heading writing status (✏️ Draft / 🔄 In progress / ✅ Done) tracked in the editor canvas and a reader-facing teaser note.
- **Total read-time badge** — when Reading Guide + read-time are active, the TOC header shows the aggregated total read time for the entire post.
- New shortcode attributes: `rprogress`, `bookmark`, `rnotes`.
- New block attributes: `showReaderNotes`, `showReadingProgress`, `showBookmark`, `sectionStatus`.

## [1.1.0] - 2026-09-08

### Added
- **Reading Guide mode** — opt-in block feature that enriches the TOC into a full reading companion:
  - **Section content previews** — opening ~20 words of each section, extracted server-side from parsed block content via `TOCguide_Headings::get_sections()` (no JS fetch, no external API).
  - **Content density bars** — thin bar showing each section's word count relative to the longest section, computed at render time.
  - **Per-section read-time estimates** — `~N min` badge based on word count ÷ 200 wpm.
  - **Reading progress** — `IntersectionObserver` fades each TOC item as the reader scrolls past its heading (no scroll event handlers; passive main thread).
- **Author section notes** — block attribute `sectionNotes` (object keyed by heading slug). Writers type a per-section teaser or hook in the "Section Notes" sidebar panel; readers reveal it with a ✍ toggle button.
- **Emoji reactions** — readers react per section (💡 ⭐ 🤔 ✅); state stored in `localStorage`, zero server calls, zero accounts.
- **Per-section academic citations** — § button copies a formatted citation (APA / MLA / Chicago / Harvard / plain link) built entirely from WordPress post meta. No external API.
- **Hover section preview** — floating tooltip on TOC link hover/focus shows the section's opening sentence. Viewport-aware positioning (left or right). `aria-describedby` links the tooltip to the anchor for screen readers. `previewOnHover` attribute; works without full Reading Guide mode.
- **Export / print toolbar** — `showExport` attribute adds Copy (.md), Download (.md), Download (.doc), and Print buttons below the outline using only the Blob API and `navigator.clipboard`; zero server round-trips. Screen reader live region announces success.
- **Accessibility** — `aria-live="polite"` region for clipboard and export announcements; `focus-visible` outlines on all interactive elements; all buttons labeled; note and export toggles use `aria-expanded` / `aria-controls` / `hidden`.
- **Page-builder compatibility** — `TOCguide_Headings::get_all()` now falls back to builder-specific parsers when `parse_blocks()` finds no headings:
  - **Elementor** — walks `_elementor_data` widget JSON to find heading widgets and Text Editor blocks.
  - **Bricks Builder** — reads `_bricks_page_content_2` element JSON.
  - **Divi, WPBakery, Oxygen, Beaver Builder, Breakdance, Classic Editor** — generic `<h*>` regex scan of `post_content`.
  - `inject_builder_heading_ids()` runs as `the_content` filter at priority 999 so headings inside builder output receive matching `id` attributes.
  - `should_inject_ids()` now scans builder meta for `[tocguide]` shortcodes so assets load correctly even when the shortcode lives inside a builder widget.
  - `get_sections()` extended with `builder_raw_html()` and `flatten_html_to_sequence()` fallbacks so Reading Guide previews and read-time estimates work on builder-built pages.
- New block attributes: `guideMode`, `showPreviews`, `showDensity`, `showReadTime`, `trackProgress`, `showReactions`, `showCitations`, `citationStyle`, `sectionNotes`, `previewOnHover`, `showExport`.
- New PHP methods: `TOCguide_Headings::flatten_content()`, `get_sections()`, `citation_meta()`, `get_all_from_html()`, `get_all_from_elementor()`, `collect_elementor_headings()`, `get_all_from_bricks()`, `inject_ids_in_html()`, `builder_raw_html()`, `flatten_html_to_sequence()`, `builder_has_tocguide()`.
- New editor panels: "Reading Guide", "Section Notes", and "Behavior" enhancements in the block sidebar.
- Print CSS: expanded collapsed TOC, hidden all interactive controls, author notes shown expanded.

### Changed
- `render_list()` accepts an optional `$guide` array; fully backward-compatible.
- `render_nav()` merges section data and embeds citation meta on the `<nav>` when guide mode is active.
- `TOCguide_Plugin::inject_builder_heading_ids()` added as `the_content` hook (priority 999).
- `enqueue_front_assets()` broadened to cover page-builder pages via `should_inject_ids()`.

## [1.0.2] - 2026-09-01

WordPress.org Plugin Check cleanup for the directory review (v1.0.1 is Awaiting Review). Behavior is unchanged.

### Changed
- Removed `load_plugin_textdomain()` so Plugin Check is clean. WordPress.org auto-loads translations from the `tocguide` text domain (WP 4.6+).
- Prefixed uninstall and admin/render template variables with `tocguide_` (`PrefixAllGlobals`).
- `Tested up to` 7.1; zip-facing license copy is GPLv2 or later with no Envato split-license language; readme documents `src/` + `npm run build`.

## [1.0.1] - 2026-08-31

First **installable** GitHub Release of the 1.0 plugin. The June `v1.0.0` tag had no working zip; `v0.1.0` was still marked Latest. Download `tocguide.zip` from this release.

### Added
- GitHub Actions release workflow: tagging `vX.Y.Z` builds and attaches `tocguide.zip`.
- `languages/tocguide.pot`, PHPCS (`composer.json` / `phpcs.xml.dist`), Dependabot.
- WordPress.org banner and icon assets in `.wordpress-org/`.
- Privacy policy, code of conduct contact (`matt@matthummel.com`), 100% GPLv2-or-later grant with copyright.
- Docs site: bold sans-serif (Outfit), consistent footers, social preview.

### Changed
- Plugin Documentation and Settings links point at the GitHub Pages site.

## [1.0.0] - 2026-08-31

Marketplace-ready release: WordPress.org / CodeCanyon coding standards, a complete free feature set, and listing-quality docs.

### Added
- Live outline preview in the block editor (updates as you type headings).
- Style presets: Default, Minimal, Boxed, Underline, Card.
- Collapsible list, sticky positioning, and scroll-spy highlighting.
- Per-block layout: nested 1.1.1 numbering, hide markers, two columns, compact spacing, max height, title element, H1, and smooth-scroll override.
- Smooth-scroll with a configurable offset for sticky headers (honors `prefers-reduced-motion`).
- H5 / H6 heading support.
- Site-wide settings: auto-generate the Gutenberg block (with layout options), minimum heading count, schema JSON-LD, uninstall cleanup.
- `[tocguide]` shortcode for classic content and theme templates.
- Skip headings with the CSS class `no-toc` or `tocguide-skip`.
- In-plugin **Settings** and **Docs & Support** screens.
- WordPress.org / Envato-oriented documentation, support policy, and security policy.

### Fixed
- Heading IDs now prefer an author-supplied HTML anchor so TOC links match custom slugs.
- Empty headings no longer desync the ID injection pointer.
- Heading IDs are injected with `WP_HTML_Tag_Processor` when available.

### Changed
- Plugin version, text domain, GitHub slug, and WordPress.org slug remain **`tocguide`**.
- Plugin header description is plain text (no HTML) for directory compliance.
- Block follows Gutenberg handbook / `create-block` patterns: `save.js` returns `null`, presets are Block Styles (`is-style-*`), wrapper uses `useBlockProps` / `get_block_wrapper_attributes()`.

## [0.1.0] - 2026-06-23
### Added
- Initial release: core **Table of Contents** block.
- Auto-generates a linked outline from a post's H2/H3/H4 headings.
- Toggle which heading levels are included.
- Numbered or bulleted list option.
- Server-rendered output with an accessible `<nav>` landmark.
- Automatic anchor IDs injected into headings so links scroll correctly.
