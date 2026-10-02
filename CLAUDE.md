# CLAUDE.md — TOCguide

Context file for Claude (Cowork / Claude Code) working on this project.

Rules: `.cursor/rules/*.mdc` is the source of truth (Cursor). `sync-rules` (wp-dev-kit) mirrors them into `.claude/rules/`, which Claude Code loads automatically. Edit the `.mdc`, then run `sync-rules`.

## What this is
A single-purpose WordPress block plugin: a **Table of Contents** block that
auto-generates a linked outline from a post's headings. Built as ONE focused
block (not a block library).

Display name **TOCguide**. WordPress.org slug / folder / text domain / main file,
GitHub repo, PHP prefixes, CSS, options, block name, and shortcode are all
**`tocguide`**. See `docs/NAMING.md`.

Matt's first WordPress product — portfolio for an agency and a freemium product.
v1.0.0 is the marketplace-ready free core (WordPress.org + CodeCanyon packaging).
ThemeForest does not sell plugins; themes should *require* this slug instead.
License is **100% GPLv2 or later** (not Envato split). See
`docs/marketplace/licensing.md`.

## WordPress.org directory rules
Treat `.cursor/rules/wordpress-org-plugin-directory.mdc` and
`docs/wordpress-org/PLUGIN_DIRECTORY.md` as required product constraints
(FAQ + 18 guidelines). Official docs:
https://developer.wordpress.org/plugins/wordpress-org/plugin-developer-faq/

This plugin is **Plugin Directory** (settings, auto-insert, shortcode), not
Block Directory (those cannot have wp-admin UI).

## Tech / conventions
- `@wordpress/scripts` (`create-block` dynamic variant). `npm run build` / `npm run start`.
- Follow `.cursor/rules/wordpress-block-coding.mdc` (Gutenberg handbook APIs).
- **Dynamic block**: `block.json` + `src/save.js` (`null`) + `src/render.php`.
- Wrapper: `useBlockProps` in the editor, `get_block_wrapper_attributes()` on the front end.
- Visual presets are Gutenberg **Block Styles** (`is-style-*`), not a custom SelectControl.
- PHP: WordPress coding standards, tabs, text domain literal `tocguide`.
- Helpers live under `includes/` (loaded once from `tocguide.php`).
  `src/render.php` is output only — never declare functions there.
- Admin UI loads only when `is_admin()`.
- Front-end JS is `src/view.js` via `block.json` `viewScript`.
- **Typography:** bold sans-serif only (Outfit / system UI). Never serif.
  See `.cursor/rules/brand-typography.mdc`. The front-end TOC block
  inherits the theme. Focus paper uses `"Segoe UI", system-ui, sans-serif`.

## How it works
1. `TOCguide_Headings::get_all()` parses the post with `parse_blocks()` and builds
   ONE slug-stamped list (custom `anchor` / existing `id` wins).
2. `render.php` → `TOCguide_Headings::render_nav()` filters levels, normalizes
   depths, prints a nested list inside `<nav>`.
3. A `render_block` filter injects matching `id` attributes with
   `WP_HTML_Tag_Processor`. Both sides use the same map.
4. Settings (`tocguide_settings`) control smooth-scroll offset, auto-generate
   (top, after the first heading, or Fixed left), the Design tab, schema,
   and uninstall cleanup. The Design preview updates as you edit.
5. Auto-generate calls `WP_Block::render()` with settings as block attributes.
   `[tocguide]` still maps to the same `render_nav()` output for classic content.
   Shortcode attributes include `close`, `focus`, `fixed`, `theme`
   (`inherit` / `exclude` / `include`), and `export`.
   View assets enqueue when the block, shortcode, or auto-generate is in use.
6. Fixed left (`is-fixed-left`) docks on a singular view at `min-width: 1100px`
   into an 18rem column of `main` (or `article`). Archives and narrower
   screens leave the outline inline. The header stays put.
7. Focus sets `html.tocguide-is-focusing`, hides surrounding chrome, and
   styles the post as a paper card. `#tocguide-focus-exit` (**Show page** /
   **Bring the rest back** / Escape) restores the page.
8. Resume stores `tocguide-bm-{postId}` in `localStorage` via
   IntersectionObserver and shows the button only when that heading is in
   the current outline.

## File map
- `tocguide.php` — headers, constants, boot.
- `includes/` — settings, headings, plugin, admin.
- `admin/` — settings/support views + CSS.
- `src/block.json` — metadata, attributes, supports.
- `src/index.js` / `edit.js` / `save.js` / `view.js` / `headings.js` / `render.php`
- `uninstall.php` — deletes data only if the owner opted in.
- `languages/tocguide.pot` — translation template.
- `docs/` — GitHub Pages support site + marketplace kit.
- `.wordpress-org/` — directory banner/icon assets (PNG + SVG).

## Roadmap
**Free (this repo):** block, shortcode, auto-insert (including Fixed left on a
wide single post or page), presets, collapse, close, Focus, sticky, scroll-spy,
Design settings with a live preview, offset, schema opt-in, admin support pages.

**Later / Pro ideas:** extra numbering styles, per-heading include/exclude UI,
site-editor pattern library, premium presets. Do not cripple the free plugin
to upsell — WordPress.org and Envato both reject that.
