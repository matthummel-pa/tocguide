# Developer SOP — TOCguide

Standard operating procedure for contributing to **TOCguide**. Architecture: [`CLAUDE.md`](../CLAUDE.md). Naming: [`NAMING.md`](NAMING.md). WordPress.org FAQ + guidelines: [`wordpress-org/PLUGIN_DIRECTORY.md`](wordpress-org/PLUGIN_DIRECTORY.md) (must stay compliant).

---

## 1. Prerequisites

| Tool | Version | Why |
| --- | --- | --- |
| Node.js | 20 LTS (`.nvmrc`) | `@wordpress/scripts` |
| npm | 9+ | Lockfile |
| Composer | 2+ (optional) | `phpcs` via `composer.json` |
| Git | recent | VCS |
| WordPress | 6.4+ / PHP 7.4+ | `wp-env`, Local, or Docker |

---

## 2. Setup

```bash
git clone https://github.com/<your-username>/tocguide.git
cd tocguide
git remote add upstream https://github.com/matthummel-pa/tocguide.git
npm install
```

`build/` is gitignored. You must build before the plugin will register.

---

## 3. Dev loop

```bash
npm run start      # watch → build/
npm run build      # production
npx --package=@wordpress/env wp-env start   # http://localhost:8888  admin / password
```

`.wp-env.json` maps this repo as the plugin. Symlink this folder to `wp-content/plugins/tocguide` if you are not using wp-env.

---

## 4. Branches & commits

```bash
git fetch upstream && git checkout -b feat/your-change upstream/main
```

Prefixes: `feat/`, `fix/`, `docs/`, `chore/`. Imperative commit messages.

---

## 5. Code standards

**PHP** ([WordPress PHP CS](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/)):

- Tabs, Yoda where it helps, escape on output, sanitize on input.
- Prefix functions `tocguide_`, classes `TOCguide_`, option `tocguide_settings`.
- Never declare functions in `src/render.php`.
- Text domain is the literal `'tocguide'` — never a variable or constant.
- Admin classes load only when `is_admin()`.
- Heading IDs: `WP_HTML_Tag_Processor`, not a second regex parser, when available.

**JavaScript / block**

```bash
npm run lint:js
npm run lint:css
npm run format
composer phpcs     # after composer install
```

Match [Gutenberg block coding](https://developer.wordpress.org/block-editor/getting-started/tutorial/): `block.json` as metadata, `useBlockProps` / `get_block_wrapper_attributes()`, Block Styles in `block.json`, `@wordpress/*` packages only. Front-end `view.js` must not require jQuery. See `.cursor/rules/wordpress-block-coding.mdc`.

---

## 6. Test before a PR

- [ ] `npm run build`
- [ ] `npm run lint:js` and `npm run lint:css`
- [ ] `composer phpcs` if PHP tooling is installed
- [ ] Block inserts, live-previews headings, saves
- [ ] Front-end links hit the right `id` (including custom anchors)
- [ ] Level toggles, presets, collapse, sticky
- [ ] `[tocguide]` and auto-insert (and *not* duplicating when the block is present)
- [ ] Duplicate heading text → unique slugs
- [ ] `WP_DEBUG` is quiet

---

## 7. Pull requests

PR against `matthummel-pa/tocguide:main`. Fill in the template.

---

## 8. Release (maintainers)

Bump **all** of: `tocguide.php` (`Version` + `TOCGUIDE_VERSION`), `package.json`, `src/block.json`, `readme.txt` (`Stable tag`).

1. Dated `CHANGELOG.md` + `readme.txt` changelog.
2. `npm run build` && `npm run plugin-zip`
3. Push a tag `vX.Y.Z`. GitHub Actions attaches `tocguide.zip` to the release.
4. WordPress.org: `npm run package:svn`, then commit trunk, `assets/`, and `tags/<Stable tag>` (see `docs/marketplace/wordpress-org.md`). Do not upload a zip into SVN.
5. CodeCanyon: upload the same ZIP + HTML docs.

---

## 9. Help

[Issues](https://github.com/matthummel-pa/tocguide/issues) · [SECURITY.md](../SECURITY.md)
