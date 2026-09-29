<?php
/**
 * Plugin bootstrap, hooks, shortcode, and auto-insert.
 *
 * @package TOCguide
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main plugin controller.
 */
class TOCguide_Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var TOCguide_Plugin|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton.
	 *
	 * @return TOCguide_Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Hook everything. Safe to call once.
	 */
	public function boot() {
		add_action( 'init', array( $this, 'register_block' ) );
		add_action( 'init', array( $this, 'register_shortcode' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'editor_settings' ), 20 );
		add_action( 'update_option_' . TOCguide_Settings::OPTION, array( $this, 'purge_page_cache' ) );
		add_action( 'add_option_' . TOCguide_Settings::OPTION, array( $this, 'purge_page_cache' ) );
		add_action( 'admin_init', array( 'TOCguide_Settings', 'register' ) );

		// Gutenberg: ID injection via block rendering pipeline.
		add_filter( 'render_block', array( 'TOCguide_Headings', 'add_heading_ids' ), 10, 2 );

		// Page builders: ID injection via rendered HTML (runs after builder output).
		add_filter( 'the_content', array( $this, 'inject_builder_heading_ids' ), 999 );

		add_filter( 'the_content', array( $this, 'auto_insert' ), 12 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_front_assets' ), 20 );
		add_filter( 'plugin_action_links_' . TOCGUIDE_BASENAME, array( $this, 'action_links' ) );
		add_filter( 'plugin_row_meta', array( $this, 'row_meta' ), 10, 2 );

		if ( is_admin() ) {
			require_once TOCGUIDE_DIR . 'includes/class-tocguide-admin.php';
			TOCguide_Admin::instance()->boot();
		}
	}

	/**
	 * Register the dynamic block from compiled metadata.
	 */
	public function register_block() {
		$build = TOCGUIDE_DIR . 'build';
		if ( ! file_exists( $build . '/block.json' ) ) {
			return;
		}
		register_block_type( $build );
	}

	/**
	 * Pass design settings into the block editor so the canvas matches the front end.
	 */
	public function editor_settings() {
		$handle = 'tocguide-table-of-contents-editor-script';
		if ( ! wp_script_is( $handle, 'registered' ) ) {
			return;
		}

		$design = TOCguide_Settings::get();
		$config = array(
			'excludeThemeStyles' => ! empty( $design['exclude_theme_styles'] ),
			'markerStyle'        => isset( $design['design_marker_style'] ) ? $design['design_marker_style'] : '',
			'shadow'             => isset( $design['design_shadow'] ) ? $design['design_shadow'] : '',
			'designVars'         => TOCguide_Settings::design_css_vars(),
		);

		wp_add_inline_script(
			$handle,
			'window.tocguideEditor = ' . wp_json_encode( $config ) . ';',
			'before'
		);
	}

	/**
	 * Drop full-page caches after design or layout settings change.
	 *
	 * Saved colours and presets are printed into the post HTML. A page cache
	 * would keep showing the previous outline until it expired.
	 */
	public function purge_page_cache() {
		if ( has_action( 'litespeed_purge_all' ) ) {
			do_action( 'litespeed_purge_all' );
		}
		if ( function_exists( 'rocket_clean_domain' ) ) {
			rocket_clean_domain();
		}
		if ( function_exists( 'w3tc_flush_posts' ) ) {
			w3tc_flush_posts();
		}
		if ( function_exists( 'wp_cache_clear_cache' ) ) {
			wp_cache_clear_cache();
		}
	}

	/**
	 * Inject heading IDs into page-builder-rendered HTML via the_content filter.
	 *
	 * This runs at priority 999 — after Elementor, Divi, Beaver Builder, Bricks,
	 * WPBakery, and Oxygen have all generated their output — so headings that exist
	 * only inside builder widgets receive the same ID slugs as the TOC links.
	 * Skips gracefully on pure Gutenberg posts (render_block already handled those).
	 *
	 * @param string $content Rendered post content.
	 * @return string
	 */
	public function inject_builder_heading_ids( $content ) {
		if ( is_admin() || ! is_singular() || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}

		if ( ! TOCguide_Headings::should_inject_ids() ) {
			return $content;
		}

		$post_id = get_the_ID();
		if ( ! $post_id ) {
			return $content;
		}

		// Pure Gutenberg posts: render_block already injected the IDs.
		$post = get_post( $post_id );
		if ( $post && has_blocks( $post->post_content ) ) {
			return $content;
		}

		$headings = TOCguide_Headings::get_all( $post_id );
		if ( empty( $headings ) ) {
			return $content;
		}

		return TOCguide_Headings::inject_ids_in_html( $content, $headings );
	}

	/**
	 * [tocguide] shortcode — same output as the block, for classic content and theme templates.
	 *
	 * @param array|string $atts Shortcode attributes.
	 * @return string
	 */
	public function shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'title'       => __( 'Table of Contents', 'tocguide' ),
				'showtitle'   => '1',
				'titletag'    => 'p',
				'h1'          => '0',
				'h2'          => '1',
				'h3'          => '1',
				'h4'          => '0',
				'h5'          => '0',
				'h6'          => '0',
				'ordered'     => '0',
				'numbering'   => 'default',
				'markers'     => '1',
				'collapsible' => '0',
				'collapsed'   => '0',
				'close'       => '1',
				'focus'       => '0',
				'sticky'      => '0',
				'compact'     => '0',
				'columns'     => '1',
				'underline'   => '0',
				'highlight'   => '',
				'maxheight'   => '0',
				'min'         => '-1',
				'smooth'      => 'inherit',
				'style'       => 'default',
				'theme'       => 'inherit',
				// Reading Guide shortcode attributes.
				'preview'     => '0',   // hover-preview tooltip on TOC links.
				'guide'       => '0',   // full Reading Guide mode.
				'previews'    => '0',   // inline section content previews.
				'density'     => '0',   // section density bars.
				'readtime'    => '0',   // per-section read-time estimates.
				'progress'    => '0',   // reading progress fade.
				'reactions'   => '0',   // emoji reactions per section.
				'citations'   => '0',   // one-click academic citations.
				'citation'    => 'apa', // citation format (apa|mla|chicago|harvard|plain).
				'export'      => '0',   // show copy/download/print toolbar.
			// Study tool shortcode attributes.
				'rprogress'   => '0',   // reading progress bar.
				'bookmark'    => '0',   // resume reading bookmark.
				'rnotes'      => '0',   // reader note pads per section.
			),
			$atts,
			'tocguide'
		);

		$post_id = get_the_ID();
		if ( ! $post_id ) {
			return '';
		}

		$style = sanitize_key( $atts['style'] );
		if ( ! in_array( $style, TOCguide_Headings::allowed_style_slugs(), true ) ) {
			$style = 'default';
		}

		$numbering = sanitize_key( $atts['numbering'] );
		if ( ! in_array( $numbering, array( 'default', 'nested' ), true ) ) {
			$numbering = 'default';
		}

		$smooth = sanitize_key( $atts['smooth'] );
		if ( ! in_array( $smooth, array( 'inherit', 'on', 'off' ), true ) ) {
			$smooth = 'inherit';
		}

		$title_tag = strtolower( sanitize_html_class( $atts['titletag'] ) );
		if ( ! in_array( $title_tag, array( 'p', 'h2', 'h3', 'h4' ), true ) ) {
			$title_tag = 'p';
		}

		$highlight = '' === $atts['highlight']
			? (bool) TOCguide_Settings::get_value( 'highlight_active' )
			: $this->is_truthy( $atts['highlight'] );

		$guide_mode    = $this->is_truthy( $atts['guide'] );
		$preview_hover = $this->is_truthy( $atts['preview'] );

		$allowed_citation = array( 'apa', 'mla', 'chicago', 'harvard', 'plain' );
		$citation_style   = sanitize_key( $atts['citation'] );
		if ( ! in_array( $citation_style, $allowed_citation, true ) ) {
			$citation_style = 'apa';
		}

		$theme = sanitize_key( $atts['theme'] );
		if ( ! in_array( $theme, array( 'inherit', 'exclude', 'include' ), true ) ) {
			$theme = 'inherit';
		}
		$exclude_theme = 'inherit';
		if ( 'exclude' === $theme ) {
			$exclude_theme = 'yes';
		} elseif ( 'include' === $theme ) {
			$exclude_theme = 'no';
		}

		$attributes = array(
			'title'               => sanitize_text_field( $atts['title'] ),
			'showTitle'           => $this->is_truthy( $atts['showtitle'] ),
			'titleTag'            => $title_tag,
			'showH1'              => $this->is_truthy( $atts['h1'] ),
			'showH2'              => $this->is_truthy( $atts['h2'] ),
			'showH3'              => $this->is_truthy( $atts['h3'] ),
			'showH4'              => $this->is_truthy( $atts['h4'] ),
			'showH5'              => $this->is_truthy( $atts['h5'] ),
			'showH6'              => $this->is_truthy( $atts['h6'] ),
			'ordered'             => $this->is_truthy( $atts['ordered'] ) || 'nested' === $numbering,
			'numbering'           => $numbering,
			'hideMarkers'         => ! $this->is_truthy( $atts['markers'] ),
			'collapsible'         => $this->is_truthy( $atts['collapsible'] ),
			'collapsedDefault'    => $this->is_truthy( $atts['collapsed'] ),
			'showClose'           => $this->is_truthy( $atts['close'] ),
			'focusMode'           => $this->is_truthy( $atts['focus'] ),
			'sticky'              => $this->is_truthy( $atts['sticky'] ),
			'compact'             => $this->is_truthy( $atts['compact'] ),
			'twoColumns'          => (int) $atts['columns'] >= 2,
			'underlineLinks'      => $this->is_truthy( $atts['underline'] ),
			'excludeThemeStyles'  => $exclude_theme,
			'stylePreset'         => $style,
			'className'           => 'is-style-' . $style,
			'highlightActive'     => $highlight,
			'scrollOffset'        => -1,
			'maxHeight'           => max( 0, (int) $atts['maxheight'] ),
			'minHeadings'         => (int) $atts['min'],
			'smoothScroll'        => $smooth,
			// Reading Guide attributes.
			'previewOnHover'      => $preview_hover,
			'guideMode'           => $guide_mode,
			'showPreviews'        => $guide_mode && $this->is_truthy( $atts['previews'] ),
			'showDensity'         => $guide_mode && $this->is_truthy( $atts['density'] ),
			'showReadTime'        => $guide_mode && $this->is_truthy( $atts['readtime'] ),
			'trackProgress'       => $guide_mode && $this->is_truthy( $atts['progress'] ),
			'showReactions'       => $guide_mode && $this->is_truthy( $atts['reactions'] ),
			'showCitations'       => $guide_mode && $this->is_truthy( $atts['citations'] ),
			'citationStyle'       => $citation_style,
			'sectionNotes'        => array(),
			'sectionStatus'       => array(),
			'showExport'          => $this->is_truthy( $atts['export'] ),
			'showReadingProgress' => $this->is_truthy( $atts['rprogress'] ),
			'showBookmark'        => $this->is_truthy( $atts['bookmark'] ),
			'showReaderNotes'     => $this->is_truthy( $atts['rnotes'] ),
		);

		return TOCguide_Headings::render_nav( $attributes, $post_id, false );
	}

	/**
	 * Register the shortcode.
	 */
	public function register_shortcode() {
		add_shortcode( 'tocguide', array( $this, 'shortcode' ) );
	}

	/**
	 * Ensure view script + block CSS load for shortcode and auto-insert
	 * (block.json assets only auto-enqueue when the block is in content).
	 */
	public function enqueue_front_assets() {
		if ( ! is_singular() ) {
			return;
		}
		$post = get_post();
		if ( ! $post ) {
			return;
		}
		$settings = TOCguide_Settings::get();
		$needed   = has_block( 'tocguide/table-of-contents', $post )
			|| TOCguide_Headings::content_has_shortcode( $post->post_content )
			|| ( 'none' !== $settings['auto_insert'] && in_array( $post->post_type, $settings['auto_insert_types'], true ) )
			|| TOCguide_Headings::should_inject_ids(); // Covers page-builder shortcode placements.
		if ( ! $needed ) {
			return;
		}

		$script = 'tocguide-table-of-contents-view-script';
		$style  = 'tocguide-table-of-contents-style';
		if ( wp_script_is( $script, 'registered' ) ) {
			wp_enqueue_script( $script );
		}
		if ( wp_style_is( $style, 'registered' ) ) {
			wp_enqueue_style( $style );
		}
	}

	/**
	 * Auto-insert a TOC when no block/shortcode is present.
	 *
	 * @param string $content Post content.
	 * @return string
	 */
	public function auto_insert( $content ) {
		if ( is_admin() || ! is_singular() || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}

		$settings = TOCguide_Settings::get();
		if ( 'none' === $settings['auto_insert'] ) {
			return $content;
		}

		$post = get_post();
		if ( ! $post ) {
			return $content;
		}

		$types = $settings['auto_insert_types'];
		if ( empty( $types ) || ! in_array( $post->post_type, $types, true ) ) {
			return $content;
		}

		if ( has_block( 'tocguide/table-of-contents', $post ) ) {
			return $content;
		}
		if ( TOCguide_Headings::content_has_shortcode( $post->post_content ) ) {
			return $content;
		}

		$toc = self::render_auto_block( (int) $post->ID );
		if ( '' === $toc ) {
			return $content;
		}

		if ( 'after_first_heading' === $settings['auto_insert'] ) {
			$updated = preg_replace( '/(<\/h[1-6]>)/i', '$1' . $toc, $content, 1 );
			return is_string( $updated ) ? $updated : $content . $toc;
		}

		return $toc . $content;
	}

	/**
	 * Render the Gutenberg Table of Contents block using auto-generate settings.
	 *
	 * @param int $post_id Post ID (passed through block context).
	 * @return string
	 */
	public static function render_auto_block( $post_id ) {
		$attributes = TOCguide_Settings::block_attributes();
		$registry   = WP_Block_Type_Registry::get_instance();

		if ( class_exists( 'WP_Block' ) && $registry->is_registered( 'tocguide/table-of-contents' ) ) {
			$block = new WP_Block(
				array(
					'blockName'    => 'tocguide/table-of-contents',
					'attrs'        => $attributes,
					'innerBlocks'  => array(),
					'innerHTML'    => '',
					'innerContent' => array(),
				),
				array(
					'postId'   => (int) $post_id,
					'postType' => get_post_type( $post_id ),
				)
			);
			$html  = $block->render( array( 'dynamic' => true ) );
			if ( is_string( $html ) && '' !== $html ) {
				return $html;
			}
		}

		return TOCguide_Headings::render_nav( $attributes, $post_id, false );
	}

	/**
	 * Settings + Docs links on the Plugins screen.
	 *
	 * @param array $links Existing action links.
	 * @return array
	 */
	public function action_links( $links ) {
		$settings = '<a href="' . esc_url( admin_url( 'options-general.php?page=tocguide' ) ) . '">' . esc_html__( 'Settings', 'tocguide' ) . '</a>';
		$docs     = '<a href="' . esc_url( admin_url( 'options-general.php?page=tocguide&tab=support' ) ) . '">' . esc_html__( 'Docs & Support', 'tocguide' ) . '</a>';
		array_unshift( $links, $settings, $docs );
		return $links;
	}

	/**
	 * Extra meta links.
	 *
	 * @param array  $links Already-formed links.
	 * @param string $file  Plugin basename.
	 * @return array
	 */
	public function row_meta( $links, $file ) {
		if ( TOCGUIDE_BASENAME !== $file ) {
			return $links;
		}
		$links[] = '<a href="https://matthummel-pa.github.io/tocguide/" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Documentation', 'tocguide' ) . '</a>';
		$links[] = '<a href="https://github.com/matthummel-pa/tocguide/issues" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Support', 'tocguide' ) . '</a>';
		return $links;
	}

	/**
	 * Truthy shortcode values.
	 *
	 * @param mixed $value Raw attribute.
	 * @return bool
	 */
	private function is_truthy( $value ) {
		return in_array( strtolower( (string) $value ), array( '1', 'true', 'yes', 'on' ), true );
	}

	/**
	 * Activation: store a one-time welcome flag. Do not delete data on deactivate.
	 */
	public static function activate() {
		if ( false === get_option( TOCguide_Settings::OPTION ) ) {
			add_option( TOCguide_Settings::OPTION, TOCguide_Settings::defaults(), '', false );
		}
		set_transient( 'tocguide_activation_redirect', 1, 30 );
	}

	/**
	 * Deactivation must not delete settings (Envato + WP.org).
	 */
	public static function deactivate() {
		delete_transient( 'tocguide_activation_redirect' );
	}
}
