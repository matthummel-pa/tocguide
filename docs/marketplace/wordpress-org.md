# WordPress.org listing

## Before you submit

1. Create a wordpress.org account. Put that username in `readme.txt` → `Contributors:` (replace `matthummel` if needed).
2. Confirm the slug **`tocguide`** is still free: https://wordpress.org/plugins/tocguide/
3. `npm run build` then `npm run plugin-zip`.
4. Validate `readme.txt`: https://wordpress.org/plugins/developers/readme-validator/

## Submit / slug change during review

https://wordpress.org/plugins/developers/add/

The plugins team asked for a display name and slug that do **not** imply affiliation with another entity. Use:

- **Name:** TOCguide
- **ZIP:** `tocguide.zip` (folder inside the zip must be `tocguide/`)
- Upload via [Add your plugin](https://wordpress.org/plugins/developers/add/) and reply in the existing review thread if they still have the old slug on file.

After approval you get SVN. Trunk holds code; `.wordpress-org/` in this repo is **not** committed to trunk.

Follow the Plugin Developer FAQ and the 18 guidelines as product requirements:

- https://developer.wordpress.org/plugins/wordpress-org/plugin-developer-faq/
- Repo copy: [`docs/wordpress-org/PLUGIN_DIRECTORY.md`](../wordpress-org/PLUGIN_DIRECTORY.md)
- Agent rule: `.cursor/rules/wordpress-org-plugin-directory.mdc`

## Reply to paste into the review thread

> Hi — thanks for the note on the display name and slug.
>
> I have uploaded an updated zip. The plugin is now **TOCguide** (Plugin Name header and readme). Please change the slug to **`tocguide`**.
>
> TOCguide is an independent plugin by Matt Hummel. It is not affiliated with Webflow or any other company or plugin.
>
> Thank you.

## SVN (after approval)

WordPress.org SVN is the release repository. Develop in git. Push SVN once, when a version is ready to ship. Every commit rebuilds the download zips.

Handbook: [Using Subversion](https://developer.wordpress.org/plugins/wordpress-org/how-to-use-subversion/).

The repository is `https://plugins.svn.wordpress.org/tocguide`. Three directories matter:

| Path | What goes there |
| --- | --- |
| `trunk/` | Plugin code. `tocguide.php` and `readme.txt` sit at the trunk root. A nested `trunk/tocguide/` breaks the download. |
| `tags/<stable tag>/` | A copy of trunk for that release. Create it with `svn cp`, not a plain file copy. `Stable tag` in trunk `readme.txt` must match this folder name. |
| `assets/` | Directory page artwork only (banners, icon, screenshots). Not inside the plugin zip. |

`npm run package:svn` builds the block, then writes `dist/svn/trunk/` from the same `package.json` `files` list as `plugin-zip`, and renders `dist/svn/assets/` from `.wordpress-org/*.svg`. Screenshot files in `.wordpress-org/` or `.wordpress-org/screenshots/` are copied into `assets/`. The script refuses a version mismatch, a `Stable tag` of `trunk`, a short description over 150 characters, zip files, and `node_modules`.

`tags/1.5.0` is already published. The next commit ships the current Stable tag (1.6.2). Refresh the existing checkout. Do not delete a published tag.

```bash
svn co https://plugins.svn.wordpress.org/tocguide svn-tocguide
npm run package:svn -- --into svn-tocguide
cd svn-tocguide
svn status
svn ci -m "Tagging version 1.6.2"
```

`--into` copies trunk and assets into the checkout, `svn add`s them, sets `svn:mime-type` on images, and runs `svn cp trunk tags/<stable tag>`. It does not commit. Review `svn status` first. Running it again before that commit refreshes an uncommitted tag so it matches trunk. A tag already published in SVN is left alone. The username is the WordPress.org account name (case-sensitive). If commit returns “Access forbidden”, pass `--username` and the SVN password from https://profiles.wordpress.org/me/profile/edit/group/3/?screen=svn-password

Later releases: bump the version (see `docs/DEVELOPER_SOP.md`), set `Stable tag` to that version, run the same `--into` command, then commit.

Directory artwork comes from `.wordpress-org/` and is written only to SVN `assets/`:

- `banner-1544x500.png` / `banner-772x250.png`
- `icon-256x256.png` / `icon-128x128.png` / `icon.svg`

Screenshots named in `readme.txt` (`screenshot-1.png` …) belong in `.wordpress-org/screenshots/` so the packager can copy them to SVN `assets/`.

To regenerate the PNGs in `.wordpress-org/` without packaging: `npm install --no-save @resvg/resvg-js && node scripts/rasterize-wporg-assets.js`.

## Review hot spots this plugin already avoids

- No HTML in the plugin header `Description:`
- No remote POST of user data
- No `eval`, no obfuscation, no bundled junk
- `uninstall.php` only deletes when the owner opted in
- Deactivation does not delete settings
- Admin code loads only in `is_admin()`
- All output escaped; settings sanitized
- Text domain is the literal `tocguide`
- Independence statement in Plugin Name description + readme (guideline 17)
- Third-party services: none (disclosed in `readme.txt` / `PRIVACY.md`)
- No front-end credit links
- Source + build (`npm run build`) documented in `readme.txt`
- Screenshot captions in `readme.txt` (PNG files go in SVN `assets/` after approval: `screenshot-1.png` …)

## After listing

Point Plugin URI at the WordPress.org page (or keep GitHub). Add the plugin to your wordpress.org profile. Enable forum support and watch https://wordpress.org/support/plugin/tocguide/
