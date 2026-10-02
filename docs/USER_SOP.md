# User SOP — TOCguide

A step-by-step guide for **using** the TOCguide plugin. No coding required.

TOCguide adds one block — **Table of Contents** — that builds a linked outline from the headings in your post. You can also auto-insert it on every post or place it with a shortcode.

Online version: https://matthummel-pa.github.io/tocguide/

---

## 1. Install

**Option A — ZIP**

1. Download `tocguide.zip` from [Releases](https://github.com/matthummel-pa/tocguide/releases).
2. WordPress admin: **Plugins → Add New → Upload Plugin**.
3. Install, then **Activate**.

**Option B — Folder**

Copy the `tocguide` folder into `wp-content/plugins/` and activate **TOCguide**.

Open **Settings → TOCguide** once. Set a scroll offset if your site has a sticky header (try 80–120px).

---

## 2. Add a table of contents

1. Edit a post that has **Heading** blocks (H1–H6; H1 is off by default).
2. Click **+**, search **Table of Contents**, insert it (usually after the intro).
3. The editor shows a live outline. Publish/preview to click the links.

---

## 3. Customize the block

With the block selected, use the sidebar:

| Setting | What it does |
| --- | --- |
| **Title** (on the block) | Label above the list. Sidebar: show/hide, paragraph vs H2–H4. |
| **Include H1–H6** | Which heading levels appear. H1 is off by default. |
| **Numbered list** | Toolbar: bullets vs numbers. Numbered lists can use nested 1.1.1 counters. |
| **Hide markers** | Drop bullets/numbers (nested counters still show). |
| **Two columns / compact** | Layout density. Columns stack on small screens. |
| **Always underline links** | Keep TOC links underlined, not only on hover. |
| **Max height** | Scroll the list when it is taller than this (0 = unlimited). |
| **Style** | Block Styles panel: Default, Minimal, Boxed, Underline, Card. |
| **Sticky / collapsible / highlight** | Reading behavior. Smooth scroll can inherit the site setting or override it. |
| **Fixed left** | On a single post or page, wide screens (1100px and up) keep the outline in an 18rem column of the main content. The header and the rest of the page stay put. Archives, the blog index, and smaller screens leave the outline with the content. |
| **Close button** | On by default. Hides the outline for that visit. **Show outline** brings it back. |
| **Focused reading** | Off by default. Adds Focus. Turning it on sets the post copy on a paper card (`"Segoe UI", system-ui, sans-serif`) and hides the surrounding page. **Show page**, **Bring the rest back**, or Escape restores the page. |
| **Minimum headings / scroll offset** | `-1` inherits **Settings → TOCguide**. |

Color, spacing, typography, and border are the normal block controls.

---

## 4. Auto-generate (optional)

**Settings → TOCguide → Auto-generate the block**

This prints the same **Table of Contents** Gutenberg block on the front end from the auto-insert settings.

- Off (default) — add the block yourself, or use `[tocguide]` in classic content
- Top of content
- After the first heading
- Fixed left — on a wide single post or page, an 18rem column of the main content. On a small screen the outline stays with the content. Auto-insert itself runs on singular views.

Choose post types (Posts, Pages, …). Set title, heading levels, style, columns, collapse, and the rest of the layout on that same screen.

If a post already has the block or `[tocguide]`, auto-generate is skipped so you never get two outlines.

---

## 5. Shortcode

```
[tocguide]
[tocguide title="On this page" ordered="1" numbering="nested" style="boxed" collapsible="1"]
[tocguide fixed="1" focus="1" bookmark="1" export="1"]
```

On/off attributes accept `1`, `true`, `yes`, or `on`.

Layout and behavior: `title`, `showtitle`, `titletag`, `h1`–`h6`, `ordered`, `numbering`, `markers`, `collapsible`, `collapsed`, `close` (default on), `focus` (default off), `sticky`, `fixed` (default off; `fixed="1"` docks on a wide single post or page), `compact`, `columns`, `underline`, `highlight`, `maxheight`, `min` (`-1` uses the site minimum), `smooth` (`inherit`, `on`, or `off`), `style`, `theme` (`inherit`, `exclude`, or `include`).

Reading Guide: `preview`, `guide`, `previews`, `density`, `readtime`, `progress`, `reactions`, `citations`, `citation` (`apa`, `mla`, `chicago`, `harvard`, or `plain`).

Study tools: `export="1"` (Copy, .md, .doc, Print), `rprogress="1"`, `bookmark="1"` (resume; the button stays hidden until `tocguide-bm-{postId}` holds a heading in this post), `rnotes="1"`.

---

## 6. Design settings

**Settings → TOCguide → Design** sets the style, colours, type size, and spacing. The preview updates as you edit. A whole number such as `15` is saved as `15px`. A small decimal such as `0.95` is saved as `0.95rem`. Font choices are sans-serif or monospace already on the device. Save publishes that look on every outline.

---

## 7. Skip a heading

On the Heading block: **Advanced → Additional CSS class(es)** → `no-toc` (or `tocguide-skip`).

---

## 8. Troubleshooting

**Empty TOC**
- Use real Heading blocks, not bold paragraphs.
- Enable that heading level in the sidebar.
- Raise/lower **Minimum headings** in settings.

**Links miss the heading**
- Sticky headers need a larger **Scroll offset**.
- Custom HTML anchors on the heading are kept; keep them unique.

**Block missing from the inserter**
- Plugin activated? Using the block editor (not Classic unless you use the shortcode)?

**Styles clash with the theme**
- Try another preset, or CSS on `.tocguide`, `.tocguide__link`, `.tocguide__link.is-active`.
- **Settings → TOCguide → Design** can exclude theme list styles. The preview updates as you edit.

**Fixed left stays in the post**
- The left column applies on a single post or page at 1100px and wider. Archives, the blog index, and smaller screens keep the outline with the content.

---

## 9. FAQ

**Classic Editor?** Use `[tocguide]`.

**Slow site?** No. The outline is PHP-rendered HTML. A small script loads only on pages that have a TOC (smooth scroll / collapse / highlight).

**Multiple TOCs?** They all list the same headings. Prefer one.

**Data leaving the site?** No. Independent plugin by Matt Hummel (slug `tocguide`); not affiliated with any other product.

---

## 10. Getting help

[GitHub Issues](https://github.com/matthummel-pa/tocguide/issues) — include WordPress version, theme, and a screenshot. Policy: [SUPPORT.md](../SUPPORT.md).
