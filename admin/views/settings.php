<?php
/**
 * Settings and support page markup.
 *
 * @package TOCguide
 *
 * @var string $tab      Active tab.
 * @var array  $settings Current settings.
 * @var array  $types    Public post type objects.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tocguide_docs_url    = 'https://matthummel-pa.github.io/tocguide/';
$tocguide_github_url  = 'https://github.com/matthummel-pa/tocguide';
$tocguide_support_url = 'https://github.com/matthummel-pa/tocguide/issues';
?>
<div class="wrap tocguide-admin">
	<div class="tocguide-admin__hero">
		<div class="tocguide-admin__brand">
			<img src="<?php echo esc_url( TOCGUIDE_URL . 'assets/brand/tocguide-mark.svg' ); ?>" alt="" width="48" height="48">
			<div>
				<h1><?php esc_html_e( 'TOCguide', 'tocguide' ); ?></h1>
				<p><?php esc_html_e( 'Server-rendered table of contents for the WordPress block editor.', 'tocguide' ); ?></p>
			</div>
		</div>
		<p class="tocguide-admin__version"><?php echo esc_html( sprintf( /* translators: %s: plugin version */ __( 'Version %s', 'tocguide' ), TOCGUIDE_VERSION ) ); ?></p>
	</div>

	<nav class="nav-tab-wrapper tocguide-admin__tabs" aria-label="<?php esc_attr_e( 'TOCguide sections', 'tocguide' ); ?>">
		<a href="<?php echo esc_url( admin_url( 'options-general.php?page=tocguide' ) ); ?>" class="nav-tab <?php echo 'settings' === $tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Settings', 'tocguide' ); ?></a>
		<a href="<?php echo esc_url( admin_url( 'options-general.php?page=tocguide&tab=support' ) ); ?>" class="nav-tab <?php echo 'support' === $tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Docs &amp; Support', 'tocguide' ); ?></a>
	</nav>

	<?php if ( 'support' === $tab ) : ?>
		<div class="tocguide-admin__grid">
			<section class="tocguide-card">
				<h2><?php esc_html_e( 'Get started', 'tocguide' ); ?></h2>
				<ol>
					<li><?php esc_html_e( 'Edit a post that has Heading blocks (H1-H6; H1 is off by default).', 'tocguide' ); ?></li>
					<li><?php esc_html_e( 'Click + and search for "Table of Contents".', 'tocguide' ); ?></li>
					<li><?php esc_html_e( 'Optional: pick a style, numbered list, collapse, or sticky in the block sidebar.', 'tocguide' ); ?></li>
					<li><?php esc_html_e( 'Preview the post and click a link - it should jump to that heading.', 'tocguide' ); ?></li>
				</ol>
				<p>
					<a class="button button-primary" href="<?php echo esc_url( $tocguide_docs_url . 'documentation.html' ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Full documentation', 'tocguide' ); ?></a>
					<a class="button" href="<?php echo esc_url( $tocguide_github_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'GitHub repository', 'tocguide' ); ?></a>
				</p>
			</section>

			<section class="tocguide-card">
				<h2><?php esc_html_e( 'Need help?', 'tocguide' ); ?></h2>
				<p><?php esc_html_e( 'Support is provided through GitHub issues. Include your WordPress version, PHP version, theme, and steps to reproduce.', 'tocguide' ); ?></p>
				<p><a class="button" href="<?php echo esc_url( $tocguide_support_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Report a bug or request a feature', 'tocguide' ); ?></a></p>
				<ul class="tocguide-admin__links">
					<li><a href="<?php echo esc_url( $tocguide_docs_url . 'documentation.html' ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Full documentation', 'tocguide' ); ?></a></li>
					<li><a href="<?php echo esc_url( $tocguide_docs_url . 'documentation.html#reading-guide' ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Reading Guide docs', 'tocguide' ); ?></a></li>
					<li><a href="<?php echo esc_url( $tocguide_docs_url . 'documentation.html#compatibility' ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Compatibility notes', 'tocguide' ); ?></a></li>
					<li><a href="<?php echo esc_url( $tocguide_github_url . '/blob/main/CHANGELOG.md' ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Changelog', 'tocguide' ); ?></a></li>
					<li><a href="<?php echo esc_url( $tocguide_github_url . '/blob/main/SECURITY.md' ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Security policy', 'tocguide' ); ?></a></li>
				</ul>
			</section>

			<section class="tocguide-card tocguide-card--wide tocguide-compat-card">
				<h2><?php esc_html_e( 'Compatibility', 'tocguide' ); ?></h2>
				<p class="description"><?php esc_html_e( 'TOCguide works with every major theme, SEO plugin, and multilingual plugin. Exclude theme styles (on by default) keeps list numbers and fonts independent of the theme. It makes zero remote calls and loads no third-party assets.', 'tocguide' ); ?></p>
				<div class="tocguide-compat-grid">
					<div class="tocguide-compat-group">
						<strong><?php esc_html_e( 'WordPress &amp; PHP', 'tocguide' ); ?></strong>
						<ul>
							<li><?php esc_html_e( 'WordPress 6.4 through 7.1', 'tocguide' ); ?></li>
							<li><?php esc_html_e( 'PHP 7.4, 8.0, 8.1, 8.2, 8.3', 'tocguide' ); ?></li>
							<li><?php esc_html_e( 'Block editor + Classic Editor (shortcode)', 'tocguide' ); ?></li>
						</ul>
					</div>
					<div class="tocguide-compat-group">
						<strong><?php esc_html_e( 'SEO plugins', 'tocguide' ); ?></strong>
						<ul>
							<li><?php esc_html_e( 'Yoast SEO - no schema conflicts', 'tocguide' ); ?></li>
							<li><?php esc_html_e( 'Rank Math - schema opt-in only', 'tocguide' ); ?></li>
							<li><?php esc_html_e( 'All in One SEO, SEOPress', 'tocguide' ); ?></li>
						</ul>
					</div>
					<div class="tocguide-compat-group">
						<strong><?php esc_html_e( 'Themes', 'tocguide' ); ?></strong>
						<ul>
							<li><?php esc_html_e( 'Twenty Twenty-Four / Twenty Twenty-Five', 'tocguide' ); ?></li>
							<li><?php esc_html_e( 'Astra, Kadence, GeneratePress, Blocksy', 'tocguide' ); ?></li>
							<li><?php esc_html_e( 'Any theme - inherits your colors &amp; fonts', 'tocguide' ); ?></li>
						</ul>
					</div>
					<div class="tocguide-compat-group">
						<strong><?php esc_html_e( 'Page builders', 'tocguide' ); ?></strong>
						<ul>
							<li><?php esc_html_e( 'Elementor - heading JSON parsed automatically', 'tocguide' ); ?></li>
							<li><?php esc_html_e( 'Bricks, Divi, Beaver, WPBakery, Oxygen', 'tocguide' ); ?></li>
							<li><?php esc_html_e( 'Any builder - HTML scan fallback', 'tocguide' ); ?></li>
						</ul>
					</div>
				</div>
				<p><a href="<?php echo esc_url( $tocguide_docs_url . 'documentation.html#compatibility' ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Full compatibility notes', 'tocguide' ); ?> &rarr;</a></p>
			</section>

			<section class="tocguide-card tocguide-card--wide">
				<h2><?php esc_html_e( 'Reading Guide', 'tocguide' ); ?> <span class="tocguide-badge-new">v1.1</span></h2>
				<p class="description"><?php esc_html_e( 'Enable in the block sidebar. Adds hover section previews, read-time estimates, content density bars, reading progress, emoji reactions, author notes, and one-click academic citations. Everything is extracted server-side - zero external APIs.', 'tocguide' ); ?></p>
				<p><a href="<?php echo esc_url( $tocguide_docs_url . 'documentation.html#reading-guide' ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Reading Guide documentation', 'tocguide' ); ?> &rarr;</a></p>
			</section>

			<section class="tocguide-card tocguide-card--wide">
				<h2><?php esc_html_e( 'Shortcode', 'tocguide' ); ?></h2>
				<p><?php esc_html_e( 'Use this in classic content, page-builder text widgets, or a theme template (via do_shortcode):', 'tocguide' ); ?></p>
				<p><code>[tocguide]</code></p>
				<p><?php esc_html_e( 'Layout &amp; behavior:', 'tocguide' ); ?> <code>title</code>, <code>showtitle</code>, <code>titletag</code>, <code>h1</code>&ndash;<code>h6</code>, <code>ordered</code>, <code>numbering</code>, <code>markers</code>, <code>collapsible</code>, <code>collapsed</code>, <code>close="0"</code>, <code>focus="1"</code>, <code>sticky</code>, <code>fixed="1"</code>, <code>compact</code>, <code>columns</code>, <code>underline</code>, <code>highlight</code>, <code>maxheight</code>, <code>min</code>, <code>smooth</code>, <code>style</code>, <code>theme="exclude|include"</code></p>
			<p><?php esc_html_e( 'Reading Guide:', 'tocguide' ); ?> <code>preview="1"</code> <?php esc_html_e( '(hover tooltip)', 'tocguide' ); ?>, <code>guide="1"</code> <?php esc_html_e( '(full guide mode)', 'tocguide' ); ?>, <code>previews="1"</code>, <code>density="1"</code>, <code>readtime="1"</code>, <code>progress="1"</code>, <code>reactions="1"</code>, <code>citations="1"</code>, <code>citation="apa|mla|chicago|harvard|plain"</code></p>
			<p><?php esc_html_e( 'Study tools:', 'tocguide' ); ?> <code>rprogress="1"</code> <?php esc_html_e( '(reading progress bar)', 'tocguide' ); ?>, <code>bookmark="1"</code> <?php esc_html_e( '(resume reading)', 'tocguide' ); ?>, <code>rnotes="1"</code> <?php esc_html_e( '(reader note pads per section)', 'tocguide' ); ?></p>
			<p><?php esc_html_e( 'Export &amp; print:', 'tocguide' ); ?> <code>export="1"</code> <?php esc_html_e( '(adds Copy / .md / .doc / Print buttons)', 'tocguide' ); ?></p>
			<p><code>[tocguide title="On this page" style="boxed" preview="1" export="1"]</code></p>
			<p><code>[tocguide guide="1" previews="1" readtime="1" reactions="1" citations="1" export="1"]</code></p>
			<p><code>[tocguide rprogress="1" bookmark="1" rnotes="1"]</code></p>
		</section>

			<section class="tocguide-card tocguide-card--wide">
				<h2><?php esc_html_e( 'Skip a heading', 'tocguide' ); ?></h2>
				<p><?php esc_html_e( 'Add the CSS class no-toc or tocguide-skip to a Heading block (Advanced > Additional CSS class(es)) to keep it out of the outline.', 'tocguide' ); ?></p>
			</section>
		</div>
	<?php else : ?>
		<?php
		$tocguide_opt = TOCguide_Settings::OPTION;
		/**
		 * Helper: render a colour-picker field (text + swatch + clear link).
		 *
		 * @param string $name    Form field name (without option prefix).
		 * @param string $value   Current saved value.
		 * @param string $label   Accessible label for the text input.
		 */
		$tocguide_color_field = function ( $name, $value, $label ) use ( $tocguide_opt ) {
			$swatch_val = '' !== $value ? $value : '#ffffff';
			printf(
				'<span class="tocguide-color-field">'
				. '<input type="text" name="%1$s[%2$s]" value="%3$s" placeholder="#rrggbb" maxlength="7" aria-label="%4$s">'
				. '<input type="color" value="%5$s" aria-hidden="true" tabindex="-1">'
				. '<a href="#" class="tocguide-color-clear" aria-label="%6$s">%7$s</a>'
				. '</span>',
				esc_attr( $tocguide_opt ),
				esc_attr( $name ),
				esc_attr( $value ),
				esc_attr( $label ),
				esc_attr( $swatch_val ),
				esc_attr__( 'Clear colour', 'tocguide' ),
				esc_html__( 'Clear', 'tocguide' )
			);
		};
	?>
		<?php
		$tocguide_sections = array(
			'reading'       => __( 'Reading', 'tocguide' ),
			'auto'          => __( 'Auto-insert', 'tocguide' ),
			'design'        => __( 'Design', 'tocguide' ),
			'guide'         => __( 'Reading Guide', 'tocguide' ),
			'study'         => __( 'Study tools', 'tocguide' ),
			'seo'           => __( 'SEO & data', 'tocguide' ),
			'accessibility' => __( 'Accessibility', 'tocguide' ),
		);
		$tocguide_section  = isset( $_GET['section'] ) ? sanitize_key( wp_unslash( $_GET['section'] ) ) : 'reading'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $tocguide_sections[ $tocguide_section ] ) ) {
			$tocguide_section = 'reading';
		}
		$tocguide_preview_style = '';
		foreach ( TOCguide_Settings::design_css_vars() as $tocguide_prop => $tocguide_val ) {
			$tocguide_preview_style .= $tocguide_prop . ':' . $tocguide_val . ';';
		}
		$tocguide_preview_classes = array(
			'tocguide',
			'wp-block-tocguide-table-of-contents',
			'tocguide--' . $settings['auto_style'],
			'is-style-' . $settings['auto_style'],
		);
		if ( ! empty( $settings['exclude_theme_styles'] ) ) {
			$tocguide_preview_classes[] = 'is-theme-isolated';
		}
		if ( ! empty( $settings['auto_compact'] ) ) {
			$tocguide_preview_classes[] = 'is-compact';
		}
		if ( ! empty( $settings['auto_hide_markers'] ) ) {
			$tocguide_preview_classes[] = 'is-no-markers';
		}
		$tocguide_preview_classes = array_merge( $tocguide_preview_classes, TOCguide_Settings::appearance_classes() );
		?>
		<form action="options.php" method="post" class="tocguide-admin__form">
			<?php settings_fields( 'tocguide_settings_group' ); ?>

			<nav class="tocguide-admin__sections" aria-label="<?php esc_attr_e( 'Settings sections', 'tocguide' ); ?>">
				<?php foreach ( $tocguide_sections as $tocguide_section_id => $tocguide_section_label ) : ?>
					<a
						class="tocguide-admin__section<?php echo $tocguide_section_id === $tocguide_section ? ' is-active' : ''; ?>"
						href="<?php echo esc_url( admin_url( 'options-general.php?page=tocguide&section=' . $tocguide_section_id ) ); ?>"
						<?php echo $tocguide_section_id === $tocguide_section ? 'aria-current="page"' : ''; ?>
					><?php echo esc_html( $tocguide_section_label ); ?></a>
				<?php endforeach; ?>
			</nav>

			<div class="tocguide-preview">
				<p class="tocguide-preview__label"><?php esc_html_e( 'Outline preview', 'tocguide' ); ?></p>
				<p class="description"><?php esc_html_e( 'Updates as you change style, compact, colours, and type. Save to apply this look to auto-inserted outlines. A block you placed by hand uses its own Styles panel (Default, Minimal, Boxed, Underline, Card) and the Compact toggle in the block sidebar.', 'tocguide' ); ?></p>
				<nav id="tocguide-preview-nav" class="<?php echo esc_attr( implode( ' ', $tocguide_preview_classes ) ); ?>" style="<?php echo esc_attr( $tocguide_preview_style ); ?>" aria-hidden="true">
					<p class="tocguide__title"><?php esc_html_e( 'Table of Contents', 'tocguide' ); ?></p>
					<div class="tocguide__list" role="list">
						<div class="tocguide__item" role="listitem">
							<div class="tocguide__item-row">
								<span class="tocguide__marker" aria-hidden="true">1</span>
								<a class="tocguide__link"><?php esc_html_e( 'Getting started', 'tocguide' ); ?></a>
							</div>
						</div>
						<div class="tocguide__item" role="listitem">
							<div class="tocguide__item-row">
								<span class="tocguide__marker" aria-hidden="true">2</span>
								<a class="tocguide__link"><?php esc_html_e( 'How the styles differ', 'tocguide' ); ?></a>
							</div>
						</div>
						<div class="tocguide__item" role="listitem">
							<div class="tocguide__item-row">
								<span class="tocguide__marker" aria-hidden="true">3</span>
								<a class="tocguide__link"><?php esc_html_e( 'Colours, type, and spacing', 'tocguide' ); ?></a>
							</div>
						</div>
					</div>
				</nav>
			</div>

			<section class="tocguide-card" id="tocguide-section-reading"<?php echo 'reading' === $tocguide_section ? '' : ' hidden'; ?>>
				<h2><?php esc_html_e( 'Reading experience', 'tocguide' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Smooth scroll', 'tocguide' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( $tocguide_opt ); ?>[smooth_scroll]" value="1" <?php checked( $settings['smooth_scroll'], 1 ); ?>>
								<?php esc_html_e( 'Animate jumps to headings (respects reduced-motion preferences).', 'tocguide' ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="tocguide-scroll-offset"><?php esc_html_e( 'Scroll offset (px)', 'tocguide' ); ?></label></th>
						<td>
							<input name="<?php echo esc_attr( $tocguide_opt ); ?>[scroll_offset]" id="tocguide-scroll-offset" type="number" min="0" max="400" class="small-text" value="<?php echo esc_attr( (string) $settings['scroll_offset'] ); ?>">
							<p class="description"><?php esc_html_e( 'Space to leave under a sticky admin bar or site header so headings are not covered.', 'tocguide' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Highlight active heading', 'tocguide' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( $tocguide_opt ); ?>[highlight_active]" value="1" <?php checked( $settings['highlight_active'], 1 ); ?>>
								<?php esc_html_e( 'Mark the section currently in view in the table of contents.', 'tocguide' ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="tocguide-min-headings"><?php esc_html_e( 'Minimum headings', 'tocguide' ); ?></label></th>
						<td>
							<input name="<?php echo esc_attr( $tocguide_opt ); ?>[min_headings]" id="tocguide-min-headings" type="number" min="1" max="10" class="small-text" value="<?php echo esc_attr( (string) $settings['min_headings'] ); ?>">
							<p class="description"><?php esc_html_e( 'Hide the TOC when a post has fewer matching headings than this.', 'tocguide' ); ?></p>
						</td>
					</tr>
				</table>
			</section>

			<section class="tocguide-card" id="tocguide-section-auto"<?php echo 'auto' === $tocguide_section ? '' : ' hidden'; ?>>
				<h2><?php esc_html_e( 'Auto-generate the block', 'tocguide' ); ?></h2>
				<p class="description"><?php esc_html_e( 'Prints the Table of Contents Gutenberg block on the front end. Posts that already have the block (or the [tocguide] shortcode) are left alone. You can still insert the block by hand in the editor.', 'tocguide' ); ?></p>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Position', 'tocguide' ); ?></th>
						<td>
							<fieldset>
								<label><input type="radio" name="<?php echo esc_attr( $tocguide_opt ); ?>[auto_insert]" value="none" <?php checked( $settings['auto_insert'], 'none' ); ?>> <?php esc_html_e( 'Off - only show when the block or shortcode is added', 'tocguide' ); ?></label><br>
								<label><input type="radio" name="<?php echo esc_attr( $tocguide_opt ); ?>[auto_insert]" value="before" <?php checked( $settings['auto_insert'], 'before' ); ?>> <?php esc_html_e( 'Top of content', 'tocguide' ); ?></label><br>
								<label><input type="radio" name="<?php echo esc_attr( $tocguide_opt ); ?>[auto_insert]" value="after_first_heading" <?php checked( $settings['auto_insert'], 'after_first_heading' ); ?>> <?php esc_html_e( 'After the first heading', 'tocguide' ); ?></label><br>
								<label><input type="radio" name="<?php echo esc_attr( $tocguide_opt ); ?>[auto_insert]" value="fixed-left" <?php checked( $settings['auto_insert'], 'fixed-left' ); ?>> <?php esc_html_e( 'Fixed left', 'tocguide' ); ?></label>
							</fieldset>
							<p class="description"><?php esc_html_e( 'Fixed left pins the outline to the left edge on wide screens and starts the article beside it. On small screens it stays at the top of the content.', 'tocguide' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Post types', 'tocguide' ); ?></th>
						<td>
							<fieldset class="tocguide-admin__checks">
								<?php foreach ( $types as $tocguide_post_type_obj ) : ?>
									<label>
										<input type="checkbox" name="<?php echo esc_attr( $tocguide_opt ); ?>[auto_insert_types][]" value="<?php echo esc_attr( $tocguide_post_type_obj->name ); ?>" <?php checked( in_array( $tocguide_post_type_obj->name, $settings['auto_insert_types'], true ) ); ?>>
										<?php echo esc_html( $tocguide_post_type_obj->labels->singular_name ); ?>
									</label>
								<?php endforeach; ?>
							</fieldset>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="tocguide-auto-title"><?php esc_html_e( 'Title', 'tocguide' ); ?></label></th>
						<td>
							<input name="<?php echo esc_attr( $tocguide_opt ); ?>[auto_title]" id="tocguide-auto-title" type="text" class="regular-text" value="<?php echo esc_attr( $settings['auto_title'] ); ?>">
							<p>
								<label>
									<input type="checkbox" name="<?php echo esc_attr( $tocguide_opt ); ?>[auto_show_title]" value="1" <?php checked( $settings['auto_show_title'], 1 ); ?>>
									<?php esc_html_e( 'Show the title above the list', 'tocguide' ); ?>
								</label>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="tocguide-auto-title-tag"><?php esc_html_e( 'Title element', 'tocguide' ); ?></label></th>
						<td>
							<select name="<?php echo esc_attr( $tocguide_opt ); ?>[auto_title_tag]" id="tocguide-auto-title-tag">
								<option value="p" <?php selected( $settings['auto_title_tag'], 'p' ); ?>><?php esc_html_e( 'Paragraph', 'tocguide' ); ?></option>
								<option value="h2" <?php selected( $settings['auto_title_tag'], 'h2' ); ?>>H2</option>
								<option value="h3" <?php selected( $settings['auto_title_tag'], 'h3' ); ?>>H3</option>
								<option value="h4" <?php selected( $settings['auto_title_tag'], 'h4' ); ?>>H4</option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Heading levels', 'tocguide' ); ?></th>
						<td>
							<fieldset class="tocguide-admin__checks">
								<?php
								$tocguide_level_keys = array(
									'auto_show_h1' => 'H1',
									'auto_show_h2' => 'H2',
									'auto_show_h3' => 'H3',
									'auto_show_h4' => 'H4',
									'auto_show_h5' => 'H5',
									'auto_show_h6' => 'H6',
								);
								foreach ( $tocguide_level_keys as $tocguide_key => $tocguide_label ) :
									?>
									<label>
										<input type="checkbox" name="<?php echo esc_attr( $tocguide_opt ); ?>[<?php echo esc_attr( $tocguide_key ); ?>]" value="1" <?php checked( $settings[ $tocguide_key ], 1 ); ?>>
										<?php echo esc_html( $tocguide_label ); ?>
									</label>
								<?php endforeach; ?>
							</fieldset>
							<p class="description"><?php esc_html_e( 'H1 is usually the post title. Leave it off unless headings inside the content use H1.', 'tocguide' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'List', 'tocguide' ); ?></th>
						<td>
							<fieldset>
								<label>
									<input type="checkbox" name="<?php echo esc_attr( $tocguide_opt ); ?>[auto_ordered]" value="1" <?php checked( $settings['auto_ordered'], 1 ); ?>>
									<?php esc_html_e( 'Numbered list', 'tocguide' ); ?>
								</label><br>
								<label for="tocguide-auto-numbering"><?php esc_html_e( 'Numbering', 'tocguide' ); ?></label>
								<select name="<?php echo esc_attr( $tocguide_opt ); ?>[auto_numbering]" id="tocguide-auto-numbering">
									<option value="default" <?php selected( $settings['auto_numbering'], 'default' ); ?>><?php esc_html_e( 'Sequential (1, 2, 3)', 'tocguide' ); ?></option>
									<option value="nested" <?php selected( $settings['auto_numbering'], 'nested' ); ?>><?php esc_html_e( 'Nested (1, 1.1, 1.1.1)', 'tocguide' ); ?></option>
								</select>
								<p class="description"><?php esc_html_e( 'Nested numbering applies when the list is numbered.', 'tocguide' ); ?></p>
								<label>
									<input type="checkbox" name="<?php echo esc_attr( $tocguide_opt ); ?>[auto_hide_markers]" value="1" <?php checked( $settings['auto_hide_markers'], 1 ); ?>>
									<?php esc_html_e( 'Hide bullets and browser numbers', 'tocguide' ); ?>
								</label>
							</fieldset>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Style', 'tocguide' ); ?></th>
						<td>
							<p class="description"><?php esc_html_e( 'Default, Minimal, Boxed, Underline, and Card are on the Design tab. Compact, below, tightens padding and type on any of them.', 'tocguide' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Layout', 'tocguide' ); ?></th>
						<td>
							<fieldset>
								<label>
									<input type="checkbox" name="<?php echo esc_attr( $tocguide_opt ); ?>[auto_compact]" value="1" <?php checked( $settings['auto_compact'], 1 ); ?>>
									<?php esc_html_e( 'Compact spacing', 'tocguide' ); ?>
								</label><br>
								<label>
									<input type="checkbox" name="<?php echo esc_attr( $tocguide_opt ); ?>[auto_two_columns]" value="1" <?php checked( $settings['auto_two_columns'], 1 ); ?>>
									<?php esc_html_e( 'Two columns (stacks on small screens)', 'tocguide' ); ?>
								</label><br>
								<label>
									<input type="checkbox" name="<?php echo esc_attr( $tocguide_opt ); ?>[auto_underline]" value="1" <?php checked( $settings['auto_underline'], 1 ); ?>>
									<?php esc_html_e( 'Always underline links', 'tocguide' ); ?>
								</label><br>
								<label for="tocguide-auto-max-height"><?php esc_html_e( 'Max height (px)', 'tocguide' ); ?></label>
								<input name="<?php echo esc_attr( $tocguide_opt ); ?>[auto_max_height]" id="tocguide-auto-max-height" type="number" min="0" max="800" step="40" class="small-text" value="<?php echo esc_attr( (string) $settings['auto_max_height'] ); ?>">
								<p class="description"><?php esc_html_e( '0 is unlimited. A max height makes long outlines scroll.', 'tocguide' ); ?></p>
							</fieldset>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Behavior', 'tocguide' ); ?></th>
						<td>
							<fieldset>
								<label>
									<input type="checkbox" name="<?php echo esc_attr( $tocguide_opt ); ?>[auto_sticky]" value="1" <?php checked( $settings['auto_sticky'], 1 ); ?>>
									<?php esc_html_e( 'Sticky while scrolling', 'tocguide' ); ?>
								</label><br>
								<label>
									<input type="checkbox" name="<?php echo esc_attr( $tocguide_opt ); ?>[auto_collapsible]" value="1" <?php checked( $settings['auto_collapsible'], 1 ); ?>>
									<?php esc_html_e( 'Collapsible', 'tocguide' ); ?>
								</label><br>
								<label>
									<input type="checkbox" name="<?php echo esc_attr( $tocguide_opt ); ?>[auto_collapsed]" value="1" <?php checked( $settings['auto_collapsed'], 1 ); ?>>
									<?php esc_html_e( 'Start collapsed. The left-edge outline stays open so the headings stay on screen.', 'tocguide' ); ?>
								</label><br>
								<label>
									<input type="checkbox" name="<?php echo esc_attr( $tocguide_opt ); ?>[auto_show_close]" value="1" <?php checked( $settings['auto_show_close'], 1 ); ?>>
									<?php esc_html_e( 'Close button (hides the outline; Show outline brings it back)', 'tocguide' ); ?>
								</label><br>
								<label>
									<input type="checkbox" name="<?php echo esc_attr( $tocguide_opt ); ?>[auto_focus_mode]" value="1" <?php checked( $settings['auto_focus_mode'], 1 ); ?>>
									<?php esc_html_e( 'Focused reading. Focus clears the page so only the post copy remains, on a plain sheet.', 'tocguide' ); ?>
								</label>
							</fieldset>
						</td>
					</tr>
				</table>
			</section>

			<section class="tocguide-card" id="tocguide-section-seo"<?php echo 'seo' === $tocguide_section ? '' : ' hidden'; ?>>
				<h2><?php esc_html_e( 'SEO &amp; data', 'tocguide' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Schema markup', 'tocguide' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( TOCguide_Settings::OPTION ); ?>[schema_markup]" value="1" <?php checked( $settings['schema_markup'], 1 ); ?>>
								<?php esc_html_e( 'Output ItemList JSON-LD for the outline. Leave off if your SEO plugin already outputs a TOC schema.', 'tocguide' ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Uninstall', 'tocguide' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( TOCguide_Settings::OPTION ); ?>[delete_data]" value="1" <?php checked( $settings['delete_data'], 1 ); ?>>
								<?php esc_html_e( 'Delete TOCguide settings when the plugin is deleted. Deactivating never deletes data.', 'tocguide' ); ?>
							</label>
						</td>
					</tr>
				</table>
			</section>

			<!-- ── Design & Appearance ───────────────────────────────────────── -->
			<section class="tocguide-card" id="tocguide-section-design"<?php echo 'design' === $tocguide_section ? '' : ' hidden'; ?>>
				<h2><?php esc_html_e( 'Design &amp; Appearance', 'tocguide' ); ?></h2>
				<p class="description">
					<?php esc_html_e( 'These choices apply on desktop, tablet, and mobile, including the left-edge outline. A size or colour you enter is the one that prints. Leave a size empty to keep the built-in value. Per-block color, type, and spacing in the editor still win.', 'tocguide' ); ?>
				</p>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="tocguide-auto-style"><?php esc_html_e( 'Style', 'tocguide' ); ?></label></th>
						<td>
							<select name="<?php echo esc_attr( $tocguide_opt ); ?>[auto_style]" id="tocguide-auto-style">
								<?php
								$tocguide_styles = array(
									'default'   => __( 'Default', 'tocguide' ),
									'minimal'   => __( 'Minimal', 'tocguide' ),
									'boxed'     => __( 'Boxed', 'tocguide' ),
									'underline' => __( 'Underline', 'tocguide' ),
									'card'      => __( 'Card', 'tocguide' ),
								);
								foreach ( $tocguide_styles as $tocguide_slug => $tocguide_label ) :
									?>
									<option value="<?php echo esc_attr( $tocguide_slug ); ?>" <?php selected( $settings['auto_style'], $tocguide_slug ); ?>><?php echo esc_html( $tocguide_label ); ?></option>
								<?php endforeach; ?>
							</select>
							<p class="description"><?php esc_html_e( 'Default keeps the bordered box. Minimal drops the border and shadow. Boxed adds an inner frame. Underline is a top rule. Card is a floating panel with a shadow.', 'tocguide' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Exclude theme styles', 'tocguide' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( $tocguide_opt ); ?>[exclude_theme_styles]" value="1" <?php checked( $settings['exclude_theme_styles'], 1 ); ?>>
								<?php esc_html_e( 'Keep the outline independent of the theme. The list is drawn with TOCguide badges instead of the theme’s numbers, so a stray “0.” cannot appear in front of a heading. Turn off to let the theme style the list.', 'tocguide' ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="tocguide-design-font"><?php esc_html_e( 'Font', 'tocguide' ); ?></label></th>
						<td>
							<select id="tocguide-design-font" name="<?php echo esc_attr( $tocguide_opt ); ?>[design_font_family]">
								<option value="" <?php selected( $settings['design_font_family'], '' ); ?>><?php esc_html_e( 'System sans when theme styles are excluded, otherwise the theme font', 'tocguide' ); ?></option>
								<option value="system" <?php selected( $settings['design_font_family'], 'system' ); ?>><?php esc_html_e( 'System UI', 'tocguide' ); ?></option>
								<option value="geometric" <?php selected( $settings['design_font_family'], 'geometric' ); ?>><?php esc_html_e( 'Geometric sans', 'tocguide' ); ?></option>
								<option value="neutral" <?php selected( $settings['design_font_family'], 'neutral' ); ?>><?php esc_html_e( 'Neutral sans (Arial)', 'tocguide' ); ?></option>
								<option value="mono" <?php selected( $settings['design_font_family'], 'mono' ); ?>><?php esc_html_e( 'Monospace', 'tocguide' ); ?></option>
							</select>
							<p class="description"><?php esc_html_e( 'Sans-serif and monospace only, using fonts already on the device. No remote font files.', 'tocguide' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="tocguide-design-transform"><?php esc_html_e( 'Link case', 'tocguide' ); ?></label></th>
						<td>
							<select id="tocguide-design-transform" name="<?php echo esc_attr( $tocguide_opt ); ?>[design_text_transform]">
								<option value="" <?php selected( $settings['design_text_transform'], '' ); ?>><?php esc_html_e( 'As written', 'tocguide' ); ?></option>
								<option value="capitalize" <?php selected( $settings['design_text_transform'], 'capitalize' ); ?>><?php esc_html_e( 'Capitalize words', 'tocguide' ); ?></option>
								<option value="uppercase" <?php selected( $settings['design_text_transform'], 'uppercase' ); ?>><?php esc_html_e( 'Uppercase', 'tocguide' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Background colour', 'tocguide' ); ?></th>
						<td>
							<?php $tocguide_color_field( 'design_bg_color', $settings['design_bg_color'], __( 'Background colour (hex)', 'tocguide' ) ); ?>
							<p class="description"><?php esc_html_e( 'e.g. #f8fafc — overrides the preset background.', 'tocguide' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Text colour', 'tocguide' ); ?></th>
						<td>
							<?php $tocguide_color_field( 'design_text_color', $settings['design_text_color'], __( 'Text colour (hex)', 'tocguide' ) ); ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Link colour', 'tocguide' ); ?></th>
						<td>
							<?php $tocguide_color_field( 'design_link_color', $settings['design_link_color'], __( 'Link colour (hex)', 'tocguide' ) ); ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Link hover colour', 'tocguide' ); ?></th>
						<td>
							<?php $tocguide_color_field( 'design_link_hover', $settings['design_link_hover'], __( 'Link hover colour (hex)', 'tocguide' ) ); ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Accent colour', 'tocguide' ); ?></th>
						<td>
							<?php $tocguide_color_field( 'design_accent_color', $settings['design_accent_color'], __( 'Accent colour (hex)', 'tocguide' ) ); ?>
							<p class="description"><?php esc_html_e( 'Active link, progress bar, and the default number badge.', 'tocguide' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Title colour', 'tocguide' ); ?></th>
						<td>
							<?php $tocguide_color_field( 'design_title_color', $settings['design_title_color'], __( 'Title colour (hex)', 'tocguide' ) ); ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Icon colour', 'tocguide' ); ?></th>
						<td>
							<?php $tocguide_color_field( 'design_icon_color', $settings['design_icon_color'], __( 'Icon colour (hex)', 'tocguide' ) ); ?>
							<p class="description"><?php esc_html_e( 'Note, citation, collapse, and resume buttons.', 'tocguide' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Number badge', 'tocguide' ); ?></th>
						<td>
							<?php $tocguide_color_field( 'design_marker_color', $settings['design_marker_color'], __( 'Badge background (hex)', 'tocguide' ) ); ?>
							<?php $tocguide_color_field( 'design_marker_text', $settings['design_marker_text'], __( 'Badge text (hex)', 'tocguide' ) ); ?>
							<p class="description"><?php esc_html_e( 'Shown when Exclude theme styles is on and the list is numbered.', 'tocguide' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="tocguide-design-marker"><?php esc_html_e( 'Number style', 'tocguide' ); ?></label></th>
						<td>
							<select id="tocguide-design-marker" name="<?php echo esc_attr( $tocguide_opt ); ?>[design_marker_style]">
								<option value="" <?php selected( $settings['design_marker_style'], '' ); ?>><?php esc_html_e( 'Circle badge', 'tocguide' ); ?></option>
								<option value="square" <?php selected( $settings['design_marker_style'], 'square' ); ?>><?php esc_html_e( 'Rounded square', 'tocguide' ); ?></option>
								<option value="plain" <?php selected( $settings['design_marker_style'], 'plain' ); ?>><?php esc_html_e( 'Plain number', 'tocguide' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="tocguide-design-shadow"><?php esc_html_e( 'Shadow', 'tocguide' ); ?></label></th>
						<td>
							<select id="tocguide-design-shadow" name="<?php echo esc_attr( $tocguide_opt ); ?>[design_shadow]">
								<option value="" <?php selected( $settings['design_shadow'], '' ); ?>><?php esc_html_e( 'None', 'tocguide' ); ?></option>
								<option value="soft" <?php selected( $settings['design_shadow'], 'soft' ); ?>><?php esc_html_e( 'Soft', 'tocguide' ); ?></option>
								<option value="medium" <?php selected( $settings['design_shadow'], 'medium' ); ?>><?php esc_html_e( 'Medium', 'tocguide' ); ?></option>
							</select>
							<p class="description"><?php esc_html_e( 'The Minimal style stays flat.', 'tocguide' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Font size', 'tocguide' ); ?></th>
						<td>
							<input type="text" name="<?php echo esc_attr( $tocguide_opt ); ?>[design_font_size]" value="<?php echo esc_attr( $settings['design_font_size'] ); ?>" placeholder="<?php esc_attr_e( 'e.g. 15px or 0.9rem', 'tocguide' ); ?>" class="regular-text">
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Link font weight', 'tocguide' ); ?></th>
						<td>
							<select name="<?php echo esc_attr( $tocguide_opt ); ?>[design_font_weight]">
								<option value="" <?php selected( $settings['design_font_weight'], '' ); ?>><?php esc_html_e( '— inherit from theme —', 'tocguide' ); ?></option>
								<?php
								foreach ( array( '300', '400', '500', '600', '700', '800' ) as $tocguide_fw ) :
									?>
									<option value="<?php echo esc_attr( $tocguide_fw ); ?>" <?php selected( $settings['design_font_weight'], $tocguide_fw ); ?>><?php echo esc_html( $tocguide_fw ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="tocguide-design-title-size"><?php esc_html_e( 'Title size', 'tocguide' ); ?></label></th>
						<td>
							<input id="tocguide-design-title-size" type="text" name="<?php echo esc_attr( $tocguide_opt ); ?>[design_title_size]" value="<?php echo esc_attr( $settings['design_title_size'] ); ?>" placeholder="<?php esc_attr_e( 'e.g. 1.15rem', 'tocguide' ); ?>" class="regular-text">
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="tocguide-design-title-weight"><?php esc_html_e( 'Title weight', 'tocguide' ); ?></label></th>
						<td>
							<select id="tocguide-design-title-weight" name="<?php echo esc_attr( $tocguide_opt ); ?>[design_title_weight]">
								<option value="" <?php selected( $settings['design_title_weight'], '' ); ?>><?php esc_html_e( '— 700 —', 'tocguide' ); ?></option>
								<?php
								foreach ( array( '400', '500', '600', '700', '800' ) as $tocguide_tw ) :
									?>
									<option value="<?php echo esc_attr( $tocguide_tw ); ?>" <?php selected( $settings['design_title_weight'], $tocguide_tw ); ?>><?php echo esc_html( $tocguide_tw ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Line height', 'tocguide' ); ?></th>
						<td>
							<input type="text" name="<?php echo esc_attr( $tocguide_opt ); ?>[design_line_height]" value="<?php echo esc_attr( $settings['design_line_height'] ); ?>" placeholder="<?php esc_attr_e( 'e.g. 1.6', 'tocguide' ); ?>" class="small-text">
							<p class="description"><?php esc_html_e( 'Unitless number, e.g. 1.6.', 'tocguide' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="tocguide-design-tracking"><?php esc_html_e( 'Letter spacing', 'tocguide' ); ?></label></th>
						<td>
							<input id="tocguide-design-tracking" type="text" name="<?php echo esc_attr( $tocguide_opt ); ?>[design_letter_spacing]" value="<?php echo esc_attr( $settings['design_letter_spacing'] ); ?>" placeholder="<?php esc_attr_e( 'e.g. -0.02em', 'tocguide' ); ?>" class="small-text">
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="tocguide-design-gap"><?php esc_html_e( 'Item spacing', 'tocguide' ); ?></label></th>
						<td>
							<input id="tocguide-design-gap" type="text" name="<?php echo esc_attr( $tocguide_opt ); ?>[design_item_gap]" value="<?php echo esc_attr( $settings['design_item_gap'] ); ?>" placeholder="<?php esc_attr_e( 'e.g. 0.35rem', 'tocguide' ); ?>" class="small-text">
							<p class="description"><?php esc_html_e( 'Space above and below each section row.', 'tocguide' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Border', 'tocguide' ); ?></th>
						<td>
							<fieldset>
								<div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;margin-bottom:6px;">
									<input type="text" name="<?php echo esc_attr( $tocguide_opt ); ?>[design_border_width]" value="<?php echo esc_attr( $settings['design_border_width'] ); ?>" placeholder="<?php esc_attr_e( 'width e.g. 1px', 'tocguide' ); ?>" class="small-text" style="width:90px;" aria-label="<?php esc_attr_e( 'Border width', 'tocguide' ); ?>">
									<select name="<?php echo esc_attr( $tocguide_opt ); ?>[design_border_style]" aria-label="<?php esc_attr_e( 'Border style', 'tocguide' ); ?>">
										<option value="" <?php selected( $settings['design_border_style'], '' ); ?>><?php esc_html_e( '— style —', 'tocguide' ); ?></option>
										<?php foreach ( array( 'solid', 'dashed', 'dotted', 'double', 'none' ) as $tocguide_bs ) : ?>
											<option value="<?php echo esc_attr( $tocguide_bs ); ?>" <?php selected( $settings['design_border_style'], $tocguide_bs ); ?>><?php echo esc_html( $tocguide_bs ); ?></option>
										<?php endforeach; ?>
									</select>
									<?php $tocguide_color_field( 'design_border_color', $settings['design_border_color'], __( 'Border colour (hex)', 'tocguide' ) ); ?>
								</div>
								<div style="display:flex;gap:8px;align-items:center;">
									<label for="tocguide-design-radius"><?php esc_html_e( 'Border radius:', 'tocguide' ); ?></label>
									<input id="tocguide-design-radius" type="text" name="<?php echo esc_attr( $tocguide_opt ); ?>[design_border_radius]" value="<?php echo esc_attr( $settings['design_border_radius'] ); ?>" placeholder="<?php esc_attr_e( 'e.g. 8px', 'tocguide' ); ?>" class="small-text" style="width:80px;">
								</div>
							</fieldset>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="tocguide-design-padding"><?php esc_html_e( 'Padding', 'tocguide' ); ?></label></th>
						<td>
							<input id="tocguide-design-padding" type="text" name="<?php echo esc_attr( $tocguide_opt ); ?>[design_padding]" value="<?php echo esc_attr( $settings['design_padding'] ); ?>" placeholder="<?php esc_attr_e( 'e.g. 1.25rem', 'tocguide' ); ?>" class="small-text">
							<p class="description"><?php esc_html_e( 'One to four lengths, such as 1rem or 1.25rem 1.5rem. Leave blank for the preset default.', 'tocguide' ); ?></p>
						</td>
					</tr>
				</table>
			</section>

			<!-- ── Reading Guide & Study Tools global defaults ────────────────── -->
			<section class="tocguide-card" id="tocguide-section-guide"<?php echo 'guide' === $tocguide_section ? '' : ' hidden'; ?>>
				<h2>
					<?php esc_html_e( 'Reading Guide &amp; Study Tools', 'tocguide' ); ?>
					<span class="tocguide-section-badge tocguide-section-badge--guide">v1.1</span>
				</h2>
				<p class="description">
					<?php esc_html_e( 'Global defaults for auto-inserted blocks and shortcodes. Manual blocks keep their own per-block settings from the editor sidebar.', 'tocguide' ); ?>
				</p>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Hover section preview', 'tocguide' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( $tocguide_opt ); ?>[auto_preview_hover]" value="1" <?php checked( $settings['auto_preview_hover'], 1 ); ?>>
								<?php esc_html_e( 'Show the opening sentence of each section in a tooltip when hovering a TOC link.', 'tocguide' ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Reading Guide mode', 'tocguide' ); ?></th>
						<td>
							<label>
								<input id="tocguide-auto-guide-mode" type="checkbox" name="<?php echo esc_attr( $tocguide_opt ); ?>[auto_guide_mode]" value="1" <?php checked( $settings['auto_guide_mode'], 1 ); ?>>
								<?php esc_html_e( 'Enable the full Reading Guide (inline previews, density bars, read time, progress fade).', 'tocguide' ); ?>
							</label>
							<div id="tocguide-guide-subopts">
								<table class="form-table" role="presentation">
									<tr>
										<th scope="row"><?php esc_html_e( 'Section content previews', 'tocguide' ); ?></th>
										<td>
											<label>
												<input type="checkbox" name="<?php echo esc_attr( $tocguide_opt ); ?>[auto_show_previews]" value="1" <?php checked( $settings['auto_show_previews'], 1 ); ?>>
												<?php esc_html_e( 'Show the first ~20 words of each section under its TOC link.', 'tocguide' ); ?>
											</label>
										</td>
									</tr>
									<tr>
										<th scope="row"><?php esc_html_e( 'Content density bars', 'tocguide' ); ?></th>
										<td>
											<label>
												<input type="checkbox" name="<?php echo esc_attr( $tocguide_opt ); ?>[auto_show_density]" value="1" <?php checked( $settings['auto_show_density'], 1 ); ?>>
												<?php esc_html_e( 'Show a proportional bar indicating each section\'s word count.', 'tocguide' ); ?>
											</label>
										</td>
									</tr>
									<tr>
										<th scope="row"><?php esc_html_e( 'Per-section read time', 'tocguide' ); ?></th>
										<td>
											<label>
												<input type="checkbox" name="<?php echo esc_attr( $tocguide_opt ); ?>[auto_show_read_time]" value="1" <?php checked( $settings['auto_show_read_time'], 1 ); ?>>
												<?php esc_html_e( 'Show an estimated read time next to each TOC link.', 'tocguide' ); ?>
											</label>
										</td>
									</tr>
									<tr>
										<th scope="row"><?php esc_html_e( 'Reading progress fade', 'tocguide' ); ?></th>
										<td>
											<label>
												<input type="checkbox" name="<?php echo esc_attr( $tocguide_opt ); ?>[auto_track_progress]" value="1" <?php checked( $settings['auto_track_progress'], 1 ); ?>>
												<?php esc_html_e( 'Fade out TOC items as the reader scrolls past each section.', 'tocguide' ); ?>
											</label>
										</td>
									</tr>
									<tr>
										<th scope="row"><?php esc_html_e( 'Emoji reactions', 'tocguide' ); ?></th>
										<td>
											<label>
												<input type="checkbox" name="<?php echo esc_attr( $tocguide_opt ); ?>[auto_show_reactions]" value="1" <?php checked( $settings['auto_show_reactions'], 1 ); ?>>
												<?php esc_html_e( 'Let readers react per section (💡 ⭐ 🤔 ✅) — stored in browser localStorage, never on your server.', 'tocguide' ); ?>
											</label>
										</td>
									</tr>
									<tr>
										<th scope="row"><?php esc_html_e( 'Academic citations', 'tocguide' ); ?></th>
										<td>
											<label>
												<input id="tocguide-auto-show-citations" type="checkbox" name="<?php echo esc_attr( $tocguide_opt ); ?>[auto_show_citations]" value="1" <?php checked( $settings['auto_show_citations'], 1 ); ?>>
												<?php esc_html_e( 'Show a § button that copies a formatted citation for each section — built from post meta, no external API.', 'tocguide' ); ?>
											</label>
											<div id="tocguide-citation-style-row" style="margin-top:6px;">
												<label for="tocguide-auto-citation-style"><?php esc_html_e( 'Citation format:', 'tocguide' ); ?></label>
												<select id="tocguide-auto-citation-style" name="<?php echo esc_attr( $tocguide_opt ); ?>[auto_citation_style]">
													<?php
													$tocguide_cite_formats = array(
														'apa'     => 'APA',
														'mla'     => 'MLA',
														'chicago' => 'Chicago',
														'harvard' => 'Harvard',
														'plain'   => __( 'Plain link', 'tocguide' ),
													);
													foreach ( $tocguide_cite_formats as $tocguide_cf_key => $tocguide_cf_label ) :
														?>
														<option value="<?php echo esc_attr( $tocguide_cf_key ); ?>" <?php selected( $settings['auto_citation_style'], $tocguide_cf_key ); ?>><?php echo esc_html( $tocguide_cf_label ); ?></option>
													<?php endforeach; ?>
												</select>
											</div>
										</td>
									</tr>
								</table>
							</div>
						</td>
					</tr>
				</table>
			</section>

			<!-- ── Study Tools & Export global defaults ───────────────────────── -->
			<section class="tocguide-card" id="tocguide-section-study"<?php echo 'study' === $tocguide_section ? '' : ' hidden'; ?>>
				<h2>
					<?php esc_html_e( 'Study Tools &amp; Export', 'tocguide' ); ?>
					<span class="tocguide-section-badge tocguide-section-badge--study">v1.2</span>
				</h2>
				<p class="description">
					<?php esc_html_e( 'All features are off by default and store data only in the reader\'s browser — nothing is sent to your server.', 'tocguide' ); ?>
				</p>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Reading progress bar', 'tocguide' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( $tocguide_opt ); ?>[auto_reading_progress]" value="1" <?php checked( $settings['auto_reading_progress'], 1 ); ?>>
								<?php esc_html_e( 'Show a thin progress bar (0–100 % of headings read) above the TOC list.', 'tocguide' ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Resume reading bookmark', 'tocguide' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( $tocguide_opt ); ?>[auto_bookmark]" value="1" <?php checked( $settings['auto_bookmark'], 1 ); ?>>
								<?php esc_html_e( 'Bookmark the reader\'s last-read heading in localStorage and show a ↩ Resume button on return visits.', 'tocguide' ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Reader note pads', 'tocguide' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( $tocguide_opt ); ?>[auto_reader_notes]" value="1" <?php checked( $settings['auto_reader_notes'], 1 ); ?>>
								<?php esc_html_e( 'Add a note button to the right of each heading so readers can jot private notes (localStorage only — no account needed).', 'tocguide' ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Export &amp; print toolbar', 'tocguide' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( $tocguide_opt ); ?>[auto_export]" value="1" <?php checked( $settings['auto_export'], 1 ); ?>>
								<?php esc_html_e( 'Add Copy / Download .md / Download .doc / Print buttons under the TOC.', 'tocguide' ); ?>
							</label>
						</td>
					</tr>
				</table>
			</section>

			<!-- ── Accessibility ─────────────────────────────────────────────── -->
			<section class="tocguide-card" id="tocguide-section-accessibility"<?php echo 'accessibility' === $tocguide_section ? '' : ' hidden'; ?>>
				<h2><?php esc_html_e( 'Accessibility', 'tocguide' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="tocguide-focus-style"><?php esc_html_e( 'Focus ring style', 'tocguide' ); ?></label></th>
						<td>
							<select id="tocguide-focus-style" name="<?php echo esc_attr( $tocguide_opt ); ?>[focus_style]">
								<option value="default" <?php selected( $settings['focus_style'], 'default' ); ?>><?php esc_html_e( 'Default — underline only (follows theme)', 'tocguide' ); ?></option>
								<option value="bold" <?php selected( $settings['focus_style'], 'bold' ); ?>><?php esc_html_e( 'Bold — 3 px outline, offset 2 px (WCAG 2.1 AA)', 'tocguide' ); ?></option>
								<option value="high-contrast" <?php selected( $settings['focus_style'], 'high-contrast' ); ?>><?php esc_html_e( 'High contrast — black outline on yellow background (WCAG 2.1 AAA)', 'tocguide' ); ?></option>
							</select>
							<p class="description"><?php esc_html_e( 'Applies to keyboard focus on TOC links. Does not affect the block editor.', 'tocguide' ); ?></p>
						</td>
					</tr>
				</table>
			</section>

			<?php submit_button( __( 'Save settings', 'tocguide' ) ); ?>
		</form>
	<?php endif; ?>
</div>
