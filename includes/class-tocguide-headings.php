<?php
/**
 * Heading collection, list rendering, and heading-id injection.
 *
 * @package TOCguide
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Single source of truth for TOC headings and matching anchors.
 */
class TOCguide_Headings {

	/**
	 * Create a URL-safe, unique anchor slug from heading text.
	 *
	 * @param string $text Heading text.
	 * @param array  $used Slugs already used on this page (passed by reference).
	 * @return string
	 */
	public static function make_slug( $text, &$used ) {
		$slug = sanitize_title( $text );
		if ( '' === $slug ) {
			$slug = 'section';
		}
		$base  = $slug;
		$index = 2;
		while ( isset( $used[ $slug ] ) ) {
			$slug = $base . '-' . $index;
			++$index;
		}
		$used[ $slug ] = true;
		return $slug;
	}

	/**
	 * Whether a heading should be skipped (class no-toc / tocguide-skip).
	 *
	 * @param array  $block Parsed block.
	 * @param string $html  Inner HTML.
	 * @return bool
	 */
	public static function should_skip( $block, $html ) {
		$classes = '';
		if ( ! empty( $block['attrs']['className'] ) ) {
			$classes .= ' ' . $block['attrs']['className'];
		}
		if ( preg_match( '/class=["\']([^"\']+)["\']/', $html, $match ) ) {
			$classes .= ' ' . $match[1];
		}
		$classes = strtolower( $classes );
		return ( false !== strpos( $classes, 'no-toc' ) || false !== strpos( $classes, 'tocguide-skip' ) );
	}

	/**
	 * Recursively walk parsed blocks and collect heading data in document order.
	 *
	 * @param array $blocks    Parsed blocks from parse_blocks().
	 * @param array $used      Used slugs (by reference) for dedup.
	 * @param array $collected Growing list of headings (by reference).
	 */
	public static function collect( $blocks, &$used, &$collected ) {
		foreach ( $blocks as $block ) {
			if ( 'core/heading' === $block['blockName'] ) {
				$html = isset( $block['innerHTML'] ) ? $block['innerHTML'] : '';
				$text = trim( wp_strip_all_tags( $html ) );

				$level = 2;
				if ( isset( $block['attrs']['level'] ) ) {
					$level = (int) $block['attrs']['level'];
				} elseif ( preg_match( '/<h([1-6])/i', $html, $match ) ) {
					$level = (int) $match[1];
				}

				if ( '' !== $text && ! self::should_skip( $block, $html ) ) {
					$slug = '';
					if ( ! empty( $block['attrs']['anchor'] ) ) {
						$slug = sanitize_title( $block['attrs']['anchor'] );
					} elseif ( preg_match( '/\sid=["\']([^"\']+)["\']/i', $html, $id_match ) ) {
						$slug = $id_match[1];
					}

					if ( '' === $slug ) {
						$slug = self::make_slug( $text, $used );
					} else {
						$used[ $slug ] = true;
					}

					$collected[] = array(
						'level' => $level,
						'text'  => $text,
						'slug'  => $slug,
					);
				}
			}

			if ( ! empty( $block['innerBlocks'] ) ) {
				self::collect( $block['innerBlocks'], $used, $collected );
			}
		}
	}

	/**
	 * Get the full, slug-stamped heading map for a post.
	 *
	 * Tries Gutenberg blocks first (canonical path).  When no headings are found
	 * that way — e.g. on pages built with Elementor, Bricks, Divi, WPBakery,
	 * Oxygen, or Beaver Builder — falls back to builder-specific parsers and
	 * then to a generic HTML scan of the raw post content.
	 *
	 * @param int $post_id Post ID.
	 * @return array
	 */
	public static function get_all( $post_id ) {
		static $cache = array();
		$post_id      = (int) $post_id;

		if ( isset( $cache[ $post_id ] ) ) {
			return $cache[ $post_id ];
		}

		$post      = get_post( $post_id );
		$used      = array();
		$collected = array();

		if ( $post ) {
			// Primary: Gutenberg block parsing.
			self::collect( parse_blocks( $post->post_content ), $used, $collected );

			// Fallback for page-builder content (no core/heading blocks present).
			if ( empty( $collected ) ) {

				// 1. Elementor — parse widget JSON from post meta.
				if ( 'builder' === get_post_meta( $post_id, '_elementor_edit_mode', true ) ) {
					$collected = self::get_all_from_elementor( $post_id );
				}

				// 2. Bricks Builder — parse element JSON from post meta.
				if ( empty( $collected ) && get_post_meta( $post_id, '_bricks_page_content_2', true ) ) {
					$collected = self::get_all_from_bricks( $post_id );
				}

				// 3. Generic HTML scan of raw post_content.
				// Works for Divi, WPBakery, Oxygen, and any builder that embeds
				// <h*> tags directly in the stored content string.
				// strip_shortcodes() is intentionally NOT called so that inline
				// HTML headings inside shortcode blocks survive the scan.
				if ( empty( $collected ) ) {
					$collected = self::get_all_from_html( $post->post_content );
				}
			}
		}

		$cache[ $post_id ] = apply_filters( 'tocguide_headings', $collected, $post_id );
		return $cache[ $post_id ];
	}

	/**
	 * Filter headings to selected levels and normalize nesting depths.
	 *
	 * @param array $all    Full heading map.
	 * @param array $levels Heading levels to keep (e.g. array( 2, 3 )).
	 * @return array
	 */
	public static function filter_and_normalize( $all, $levels ) {
		$filtered = array_values(
			array_filter(
				$all,
				function ( $heading ) use ( $levels ) {
					return in_array( (int) $heading['level'], $levels, true );
				}
			)
		);

		if ( empty( $filtered ) ) {
			return array();
		}

		$present = array_values( array_unique( wp_list_pluck( $filtered, 'level' ) ) );
		sort( $present, SORT_NUMERIC );
		$depth_map = array_flip( $present );

		foreach ( $filtered as &$heading ) {
			$heading['level'] = $depth_map[ $heading['level'] ] + 1;
		}
		unset( $heading );

		return $filtered;
	}

	/**
	 * Heading levels requested by block attributes.
	 *
	 * @param array $attributes Block attributes.
	 * @return array
	 */
	public static function levels_from_attributes( $attributes ) {
		$levels = array();
		$map    = array(
			'showH1' => 1,
			'showH2' => 2,
			'showH3' => 3,
			'showH4' => 4,
			'showH5' => 5,
			'showH6' => 6,
		);
		foreach ( $map as $key => $level ) {
			if ( ! empty( $attributes[ $key ] ) ) {
				$levels[] = $level;
			}
		}
		if ( empty( $levels ) ) {
			$levels = array( 2 );
		}
		return $levels;
	}

	/**
	 * Flatten all block content into a linear sequence for text extraction.
	 *
	 * Walks the block tree in document order, emitting heading or content items.
	 * Container blocks (Group, Columns, etc.) have empty innerHTML so they are
	 * skipped naturally; their children are reached via recursion.
	 *
	 * @param array $blocks Parsed blocks.
	 * @param array $flat   Growing flat sequence, passed by reference.
	 */
	private static function flatten_content( $blocks, &$flat ) {
		foreach ( $blocks as $block ) {
			$name      = isset( $block['blockName'] ) ? (string) $block['blockName'] : '';
			$html      = isset( $block['innerHTML'] ) ? (string) $block['innerHTML'] : '';
			$has_inner = ! empty( $block['innerBlocks'] );

			if ( 'core/heading' === $name ) {
				/*
				 * Headings are always leaf blocks. Extract text and record a
				 * heading marker regardless of whether innerBlocks is set.
				 */
				$text = trim( wp_strip_all_tags( $html ) );
				if ( '' !== $text && ! self::should_skip( $block, $html ) ) {
					$flat[] = array(
						'type' => 'heading',
						'text' => $text,
					);
				}
			} elseif ( ! $has_inner ) {
				/*
				 * Only extract text content from leaf blocks (no innerBlocks).
				 * Container blocks (Group, Columns, Cover, etc.) carry their
				 * children's text in innerHTML too, so reading it here would
				 * double-count that text; recursion below handles those children.
				 */
				$text = trim( wp_strip_all_tags( $html ) );
				if ( '' !== $text ) {
					$flat[] = array(
						'type' => 'content',
						'text' => $text,
					);
				}
			}

			if ( $has_inner ) {
				self::flatten_content( $block['innerBlocks'], $flat );
			}
		}
	}

	/**
	 * Collect raw HTML from page-builder meta for text extraction.
	 *
	 * Returns a string of heading and paragraph HTML assembled from builder-specific
	 * meta fields, or an empty string when no builder is detected. Never calls
	 * apply_filters('the_content') to avoid triggering our own hooks.
	 *
	 * @param int $post_id Post ID.
	 * @return string
	 */
	private static function builder_raw_html( $post_id ) {
		$parts = array();

		// Elementor: walk the element tree, collecting heading + rich-text content.
		$el_raw = get_post_meta( (int) $post_id, '_elementor_data', true );
		if ( ! empty( $el_raw ) && is_string( $el_raw ) ) {
			$elements = json_decode( $el_raw, true );
			if ( is_array( $elements ) ) {
				self::collect_elementor_headings( $elements, $parts );
			}
		}

		if ( ! empty( $parts ) ) {
			return implode( "\n", $parts );
		}

		// Bricks Builder.
		$bricks = get_post_meta( (int) $post_id, '_bricks_page_content_2', true );
		if ( ! empty( $bricks ) ) {
			$elements = is_string( $bricks ) ? maybe_unserialize( $bricks ) : $bricks;
			if ( is_array( $elements ) ) {
				foreach ( $elements as $el ) {
					$name     = isset( $el['name'] ) ? $el['name'] : '';
					$settings = isset( $el['settings'] ) && is_array( $el['settings'] ) ? $el['settings'] : array();
					if ( 'heading' === $name && ! empty( $settings['text'] ) ) {
						$tag     = ! empty( $settings['tag'] ) && preg_match( '/^h[1-6]$/i', $settings['tag'] ) ? sanitize_html_class( $settings['tag'] ) : 'h2';
						$parts[] = '<' . $tag . '>' . wp_strip_all_tags( $settings['text'] ) . '</' . $tag . '>';
					} elseif ( 'rich-text' === $name && ! empty( $settings['content'] ) ) {
						$parts[] = wp_kses_post( $settings['content'] );
					}
				}
				if ( ! empty( $parts ) ) {
					return implode( "\n", $parts );
				}
			}
		}

		// Divi, WPBakery, Beaver Builder, Oxygen, Breakdance, Classic Editor:
		// post_content contains raw HTML (mixed with shortcodes). Strip the
		// shortcode tags but keep the HTML so headings and <p> tags are visible.
		$content = (string) get_post_field( 'post_content', $post_id );
		if ( '' !== $content ) {
			// Strip shortcode wrappers only — leave their innerHTML/HTML intact.
			$stripped = preg_replace( '/\[\/?\w[^\]]*\]/', '', $content );
			return is_string( $stripped ) ? $stripped : $content;
		}

		return '';
	}

	/**
	 * Build a flat heading/content sequence from an HTML string.
	 *
	 * Parses heading tags and paragraph tags into the same type-keyed array that
	 * flatten_content() produces from block data, so get_sections() can process
	 * both Gutenberg and builder content with the same downstream logic.
	 *
	 * @param string $html Raw HTML.
	 * @param array  $flat Growing flat sequence, passed by reference.
	 */
	private static function flatten_html_to_sequence( $html, &$flat ) {
		preg_match_all(
			'/<(h[1-6]|p)(?:\s[^>]*)?>(.+?)<\/\1>/is',
			$html,
			$matches,
			PREG_SET_ORDER
		);
		foreach ( $matches as $m ) {
			$tag  = strtolower( $m[1] );
			$text = trim( wp_strip_all_tags( $m[2] ) );
			if ( '' === $text ) {
				continue;
			}
			$flat[] = array(
				'type' => ( 'p' === $tag ) ? 'content' : 'heading',
				'text' => $text,
			);
		}
	}

	/**
	 * Get headings enriched with section-level content data for Reading Guide mode.
	 *
	 * Extracts word count, estimated reading time, and a brief content preview
	 * for each section entirely from parsed block content — no external API calls.
	 *
	 * @param int $post_id Post ID.
	 * @return array Headings with added keys: word_count, read_minutes, preview.
	 */
	public static function get_sections( $post_id ) {
		static $cache = array();
		$post_id      = (int) $post_id;

		if ( isset( $cache[ $post_id ] ) ) {
			return $cache[ $post_id ];
		}

		$post = get_post( $post_id );
		if ( ! $post ) {
			$cache[ $post_id ] = array();
			return array();
		}

		// Flatten block tree into a linear heading / content sequence.
		$flat = array();
		self::flatten_content( parse_blocks( $post->post_content ), $flat );

		/*
		 * Fallback for page-builder content: when parse_blocks() finds no items
		 * (because the post uses Elementor, Divi, Bricks, WPBakery, etc.),
		 * extract text directly from builder-specific meta or from post_content
		 * so hover previews, read-time estimates, and density bars still work.
		 *
		 * We do NOT call apply_filters('the_content') here — that would risk
		 * recursion through our own auto_insert hook.
		 */
		if ( empty( $flat ) ) {
			$builder_html = self::builder_raw_html( $post_id );
			if ( '' !== $builder_html ) {
				self::flatten_html_to_sequence( $builder_html, $flat );
			}
		}

		// Accumulate body text per section (split at every heading boundary).
		$section_texts = array();
		$heading_idx   = -1;
		foreach ( $flat as $item ) {
			if ( 'heading' === $item['type'] ) {
				++$heading_idx;
				$section_texts[ $heading_idx ] = '';
			} elseif ( $heading_idx >= 0 ) {
				$section_texts[ $heading_idx ] .= ' ' . $item['text'];
			}
		}

		// Merge section data into the base headings list.
		$headings = self::get_all( $post_id );
		foreach ( $headings as $i => &$heading ) {
			$text                    = isset( $section_texts[ $i ] ) ? trim( $section_texts[ $i ] ) : '';
			$words                   = '' !== $text ? str_word_count( $text ) : 0;
			$heading['word_count']   = $words;
			$heading['read_minutes'] = max( 1, (int) ceil( max( 1, $words ) / 200 ) );
			$heading['preview']      = '' !== $text ? wp_trim_words( $text, 20, '' ) : '';
		}
		unset( $heading );

		$cache[ $post_id ] = $headings;
		return $headings;
	}

	/**
	 * Build citation metadata from post data for client-side citation formatting.
	 *
	 * Returns only already-public WordPress data — no external calls required.
	 *
	 * @param int    $post_id       Post ID.
	 * @param string $citation_style Citation format key (apa|mla|chicago|harvard|plain).
	 * @return array
	 */
	public static function citation_meta( $post_id, $citation_style = 'apa' ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return array();
		}

		$allowed = array( 'apa', 'mla', 'chicago', 'harvard', 'plain' );
		$style   = in_array( $citation_style, $allowed, true ) ? $citation_style : 'apa';

		return array(
			'author'        => get_the_author_meta( 'display_name', (int) $post->post_author ),
			'title'         => html_entity_decode( get_the_title( $post_id ), ENT_QUOTES, 'UTF-8' ),
			'site'          => html_entity_decode( get_bloginfo( 'name' ), ENT_QUOTES, 'UTF-8' ),
			'date'          => get_the_date( 'Y-m-d', $post_id ),
			'url'           => (string) get_permalink( $post_id ),
			'citationStyle' => $style,
		);
	}

	/**
	 * Inline SVG icon. Paths are hardcoded; nothing here is user input.
	 *
	 * @param string $name Icon key.
	 * @return string
	 */
	public static function icon_svg( $name ) {
		$paths = array(
			'note'     => '<path d="M7.5 4h6l4 4v11.1a1.4 1.4 0 0 1-1.4 1.4H7.5a1.4 1.4 0 0 1-1.4-1.4V5.4A1.4 1.4 0 0 1 7.5 4z"/><path d="M13.4 4.2V8.2h4"/><path d="M8.6 12.2h3.6"/><path d="m11.7 16.7 3.2-3.2.9.9-3.2 3.2h-.9z"/>',
			'pencil'   => '<path d="M4.5 19.5h3.8L18.2 9.6a1.7 1.7 0 0 0 0-2.4l-.4-.4a1.7 1.7 0 0 0-2.4 0L5.5 16.7v2.8z"/><path d="m13.6 6.6 3.8 3.8"/>',
			'cite'     => '<path d="M9.2 7H6.4A1.4 1.4 0 0 0 5 8.4V12h3.4V7.6"/><path d="M17.2 7h-2.8A1.4 1.4 0 0 0 13 8.4V12h3.4V7.6"/><path d="M8.4 12.2c0 2.1-1.1 3.4-2.8 4"/><path d="M16.4 12.2c0 2.1-1.1 3.4-2.8 4"/>',
			'copy'     => '<rect x="8" y="8" width="11" height="11" rx="1.6"/><path d="M6.2 16H5.6A1.6 1.6 0 0 1 4 14.4v-8.8A1.6 1.6 0 0 1 5.6 4h8.8A1.6 1.6 0 0 1 16 5.6V6.2"/>',
			'download' => '<path d="M12 4v10"/><path d="m8 10 4 4 4-4"/><path d="M5 19h14"/>',
			'print'    => '<path d="M7 9V4.75h10V9"/><path d="M7 16.25H5.6A1.6 1.6 0 0 1 4 14.65v-3.9A1.6 1.6 0 0 1 5.6 9.15h12.8A1.6 1.6 0 0 1 20 10.75v3.9a1.6 1.6 0 0 1-1.6 1.6H17"/><path d="M7 14.25h10v5.5H7z"/>',
			'resume'   => '<path d="M9 14.25 4.75 10 9 5.75"/><path d="M5.25 10h8.1a4.75 4.75 0 1 1 0 9.5H12"/>',
			'chevron'  => '<path d="m6 9 6 6 6-6"/>',
			'close'    => '<path d="M6 6 18 18"/><path d="M18 6 6 18"/>',
			'focus'    => '<circle cx="12" cy="12" r="3"/><path d="M12 3v2.5"/><path d="M12 18.5V21"/><path d="M3 12h2.5"/><path d="M18.5 12H21"/>',
		);

		if ( ! isset( $paths[ $name ] ) ) {
			return '';
		}

		return '<svg class="tocguide__svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $paths[ $name ] . '</svg>';
	}

	/**
	 * Visible marker for one outline row.
	 *
	 * Numbers are real text, not CSS counters. Theme counters were printing a
	 * leading "0." because counters() includes the list's reset value.
	 *
	 * @param array  $counts    Depth => count, updated by reference.
	 * @param int    $level     Normalized depth, starting at 1.
	 * @param bool   $ordered   Numbered list.
	 * @param string $numbering 'default' or 'nested'.
	 * @param bool   $hide      Hide bullets and numbers.
	 * @return string Empty, "bullet", or a number label such as "1.2".
	 */
	private static function marker_label( &$counts, $level, $ordered, $numbering, $hide ) {
		$level = max( 1, (int) $level );
		foreach ( array_keys( $counts ) as $depth ) {
			if ( (int) $depth > $level ) {
				unset( $counts[ $depth ] );
			}
		}
		if ( ! isset( $counts[ $level ] ) ) {
			$counts[ $level ] = 0;
		}
		++$counts[ $level ];

		$nested = $ordered && 'nested' === $numbering;
		if ( $hide && ! $nested ) {
			return '';
		}
		if ( ! $ordered ) {
			return 'bullet';
		}
		if ( $nested ) {
			$parts = array();
			for ( $d = 1; $d <= $level; $d++ ) {
				$parts[] = isset( $counts[ $d ] ) ? (string) (int) $counts[ $d ] : '1';
			}
			return implode( '.', $parts );
		}
		return (string) (int) $counts[ $level ];
	}

	/**
	 * Drop a leading "0." left in heading text by a theme list counter.
	 *
	 * @param string $text Heading text.
	 * @return string
	 */
	private static function strip_stray_zero( $text ) {
		$clean = preg_replace( '/^0\.\s+/u', '', (string) $text );
		if ( is_string( $clean ) && '' !== $clean ) {
			return $clean;
		}
		return (string) $text;
	}

	/**
	 * Render nested list markup from heading data.
	 *
	 * @param array  $headings Heading data with normalized 'level'.
	 * @param string $list_tag 'ol' or 'ul'.
	 * @param array  $guide    Optional Reading Guide options.
	 * @return string
	 */
	public static function render_list( $headings, $list_tag, $guide = array() ) {
		if ( empty( $headings ) ) {
			return '';
		}

		$list_tag  = ( 'ol' === $list_tag ) ? 'ol' : 'ul';
		$isolate   = ! empty( $guide['isolate'] );
		$item_tag  = 'li';
		$list_role = '';
		$item_role = '';

		/*
		 * Theme ordered-list rules often paint a counter on li::before / ::marker.
		 * That counter is frequently 0, so every row reads "0. Heading" beside our
		 * own badge. A div list does not match those selectors.
		 */
		if ( $isolate ) {
			$list_tag  = 'div';
			$item_tag  = 'div';
			$list_role = ' role="list"';
			$item_role = ' role="listitem"';
		}

		$show_time         = ! empty( $guide['show_read_time'] );
		$show_density      = ! empty( $guide['show_density'] );
		$show_preview      = ! empty( $guide['show_previews'] );
		$show_reactions    = ! empty( $guide['show_reactions'] );
		$show_citations    = ! empty( $guide['show_citations'] );
		$preview_on_hover  = ! empty( $guide['preview_on_hover'] );
		$show_reader_notes = ! empty( $guide['show_reader_notes'] );
		$notes             = isset( $guide['section_notes'] ) && is_array( $guide['section_notes'] ) ? $guide['section_notes'] : array();
		$any_guide         = $show_time || $show_density || $show_preview || $show_reactions || $show_citations || ! empty( $notes ) || $preview_on_hover || $show_reader_notes;
		$ordered           = ! empty( $guide['ordered'] );
		$numbering         = isset( $guide['numbering'] ) ? sanitize_key( $guide['numbering'] ) : 'default';
		$hide_markers      = ! empty( $guide['hide_markers'] );
		$marker_counts     = array();

		// Denominator for density bar proportions.
		$max_words = 1;
		if ( $show_density ) {
			foreach ( $headings as $h ) {
				if ( isset( $h['word_count'] ) && $h['word_count'] > $max_words ) {
					$max_words = $h['word_count'];
				}
			}
		}

		$html = '';
		$prev = 0;
		$open = 0;

		foreach ( $headings as $heading ) {
			$level  = (int) $heading['level'];
			$slug   = $heading['slug'];
			$text   = $heading['text'];
			if ( $isolate ) {
				$text = self::strip_stray_zero( $text );
			}
			$marker = self::marker_label( $marker_counts, $level, $ordered, $numbering, $hide_markers );

			if ( $level > $prev ) {
				for ( $i = 0; $i < ( $level - $prev ); $i++ ) {
					$class = 0 === $open ? ' class="tocguide__list"' : ' class="tocguide__sub"';
					$html .= '<' . $list_tag . $class . $list_role . '>';
					++$open;
				}
			} else {
				$html .= '</' . $item_tag . '>';
				if ( $level < $prev ) {
					for ( $i = 0; $i < ( $prev - $level ); $i++ ) {
						$html .= '</' . $list_tag . '></' . $item_tag . '>';
						--$open;
					}
				}
			}

			// ── Item opening tag ──────────────────────────────────────────────
			$tip_id = $preview_on_hover ? 'tocguide-tip-' . sanitize_html_class( $slug ) : '';
			if ( $any_guide ) {
				$item_extra = '';
				if ( $preview_on_hover ) {
					$item_extra = ' class="tocguide__item has-hover-preview"';
				} else {
					$item_extra = ' class="tocguide__item"';
				}
				$item_style = '';
				if ( $show_density && isset( $heading['word_count'] ) ) {
					$density    = $max_words > 0 ? min( 1.0, (float) $heading['word_count'] / $max_words ) : 0.0;
					$item_style = ' style="--tocguide-density:' . esc_attr( number_format( $density, 3, '.', '' ) ) . '"';
				}
				$html .= '<' . $item_tag . $item_extra
					. ' data-tocguide-slug="' . esc_attr( $slug ) . '"'
					. ' data-tocguide-heading="' . esc_attr( $text ) . '"'
					. $item_style . $item_role . '>';
			} else {
				$html .= '<' . $item_tag . ' class="tocguide__item"' . $item_role . '>';
			}

			$has_author_note = isset( $notes[ $slug ] ) && '' !== (string) $notes[ $slug ];
			$note_id         = $has_author_note ? 'tocguide-note-' . sanitize_html_class( $slug ) : '';
			$rnote_id        = $show_reader_notes ? 'tocguide-rnote-' . sanitize_html_class( $slug ) : '';
			$has_tools       = $show_reader_notes || $has_author_note || $show_citations;

			// ── Link row: marker, heading, then icons on the right ───────────
			$html .= '<div class="tocguide__item-row">';
			if ( 'bullet' === $marker ) {
				$html .= '<span class="tocguide__marker tocguide__marker--bullet" aria-hidden="true"></span>';
			} elseif ( '' !== $marker ) {
				$html .= '<span class="tocguide__marker" aria-hidden="true">' . esc_html( $marker ) . '</span>';
			}

			if ( $tip_id ) {
				$html .= '<a class="tocguide__link" href="#' . esc_attr( $slug ) . '"'
					. ' aria-describedby="' . esc_attr( $tip_id ) . '">'
					. esc_html( $text ) . '</a>';
			} else {
				$html .= '<a class="tocguide__link" href="#' . esc_attr( $slug ) . '">' . esc_html( $text ) . '</a>';
			}
			if ( $show_time && isset( $heading['read_minutes'] ) ) {
				$mins  = (int) $heading['read_minutes'];
				$html .= '<span class="tocguide__time" aria-hidden="true">~'
					. $mins . '&thinsp;'
					/* translators: abbreviation for "minute" in read-time estimates, e.g. "~3 min" */
					. esc_html( __( 'min', 'tocguide' ) )
					. '</span>';
			}

			if ( $has_tools ) {
				$html .= '<span class="tocguide__item-tools">';
				if ( $show_reader_notes ) {
					$html .= '<button type="button" class="tocguide__icon-btn tocguide__rnote-toggle"'
						. ' aria-expanded="false"'
						. ' aria-controls="' . esc_attr( $rnote_id ) . '"'
						. ' aria-label="' . esc_attr( sprintf( /* translators: %s = heading text */ __( 'My note for: %s', 'tocguide' ), $text ) ) . '">'
						. self::icon_svg( 'note' )
						. '</button>';
				}
				if ( $has_author_note ) {
					$html .= '<button type="button" class="tocguide__icon-btn tocguide__note-toggle"'
						. ' aria-expanded="false"'
						. ' aria-controls="' . esc_attr( $note_id ) . '"'
						. ' aria-label="' . esc_attr__( "Author's note", 'tocguide' ) . '">'
						. self::icon_svg( 'pencil' )
						. '</button>';
				}
				if ( $show_citations ) {
					$html .= '<button type="button" class="tocguide__icon-btn tocguide__cite-btn"'
						. ' aria-label="' . esc_attr__( 'Copy citation for this section', 'tocguide' ) . '">'
						. self::icon_svg( 'cite' )
						. '<span class="tocguide__visually-hidden">' . esc_html__( 'Cite', 'tocguide' ) . '</span>'
						. '</button>';
				}
				$html .= '</span>';
			}
			$html .= '</div>';

			// ── Hover tooltip label (screen-reader target for aria-describedby) ─
			if ( $preview_on_hover && ! empty( $heading['preview'] ) ) {
				$html .= '<span class="tocguide__tip-label tocguide__visually-hidden"'
					. ' id="' . esc_attr( $tip_id ) . '"'
					. ' role="tooltip">'
					. esc_html( $heading['preview'] )
					. '</span>';
			}

			// ── Density bar ───────────────────────────────────────────────────
			if ( $show_density ) {
				$html .= '<span class="tocguide__density-bar" aria-hidden="true"></span>';
			}

			// ── Content preview ───────────────────────────────────────────────
			if ( $show_preview && ! empty( $heading['preview'] ) ) {
				$html .= '<span class="tocguide__preview">' . esc_html( $heading['preview'] ) . '</span>';
			}

			// ── Author note body ──────────────────────────────────────────────
			if ( $has_author_note ) {
				$html .= '<span class="tocguide__note" id="' . esc_attr( $note_id ) . '" hidden>'
					. esc_html( (string) $notes[ $slug ] )
					. '</span>';
			}

			// ── Reader note pad (toggle lives in the heading row) ─────────────
			if ( $show_reader_notes ) {
				$html .= '<div class="tocguide__rnote-pad" id="' . esc_attr( $rnote_id ) . '" hidden>'
					. '<textarea class="tocguide__rnote-ta" rows="3"'
					. ' placeholder="' . esc_attr__( 'Your notes for this section…', 'tocguide' ) . '"'
					. ' aria-label="' . esc_attr( sprintf( /* translators: %s = heading text */ __( 'Notes for: %s', 'tocguide' ), $text ) ) . '">'
					. '</textarea>'
					. '</div>';
			}

			// ── Reactions ─────────────────────────────────────────────────────
			if ( $show_reactions ) {
				$reaction_map = array(
					'💡' => __( 'Insightful', 'tocguide' ),
					'⭐' => __( 'Saved', 'tocguide' ),
					'🤔' => __( 'Unclear', 'tocguide' ),
					'✅' => __( 'Got it', 'tocguide' ),
				);
				$html        .= '<div class="tocguide__actions">';
				$html        .= '<div class="tocguide__reactions" role="group" aria-label="'
					. esc_attr__( 'React to this section', 'tocguide' ) . '">';
				foreach ( $reaction_map as $emoji => $label ) {
					$html .= '<button type="button" class="tocguide__reaction"'
						. ' aria-pressed="false"'
						. ' aria-label="' . esc_attr( $label ) . '"'
						. ' data-reaction="' . esc_attr( $emoji ) . '">'
						. $emoji
						. '</button>';
				}
				$html .= '</div></div>';
			}

			$prev = $level;
		}

		$html .= '</' . $item_tag . '>';
		for ( $i = 0; $i < $open; $i++ ) {
			$html .= '</' . $list_tag . '>';
			if ( $i < $open - 1 ) {
				$html .= '</' . $item_tag . '>';
			}
		}

		return $html;
	}

	/**
	 * Build the full <nav> markup for a TOC.
	 *
	 * @param array $attributes Block attributes.
	 * @param int   $post_id    Post ID.
	 * @param bool  $wrap       Whether to include block wrapper attributes.
	 * @return string
	 */
	public static function render_nav( $attributes, $post_id, $wrap = true ) {
		$levels = self::levels_from_attributes( $attributes );
		$all    = self::get_all( $post_id );
		$items  = self::filter_and_normalize( $all, $levels );

		// ── Section data (shared by guide mode and hover previews) ───────────
		$sections      = array();
		$section_map   = array();
		$need_sections = ! empty( $attributes['guideMode'] ) || ! empty( $attributes['previewOnHover'] );
		if ( $need_sections ) {
			$sections = self::get_sections( $post_id );
			foreach ( $sections as $section ) {
				$section_map[ $section['slug'] ] = $section;
			}
		}

		// ── Reading Guide mode ────────────────────────────────────────────────
		$guide = array();
		if ( ! empty( $attributes['guideMode'] ) ) {
			// Merge full section content data into the filtered items.
			foreach ( $items as &$item ) {
				if ( isset( $section_map[ $item['slug'] ] ) ) {
					$s                    = $section_map[ $item['slug'] ];
					$item['word_count']   = $s['word_count'];
					$item['read_minutes'] = $s['read_minutes'];
					$item['preview']      = $s['preview'];
				}
			}
			unset( $item );

			$notes = array();
			if ( ! empty( $attributes['sectionNotes'] ) && is_array( $attributes['sectionNotes'] ) ) {
				foreach ( $attributes['sectionNotes'] as $k => $v ) {
					$notes[ sanitize_key( $k ) ] = sanitize_text_field( (string) $v );
				}
			}

			$guide = array(
				'show_previews'     => ! empty( $attributes['showPreviews'] ),
				'show_density'      => ! empty( $attributes['showDensity'] ),
				'show_read_time'    => ! empty( $attributes['showReadTime'] ),
				'show_reactions'    => ! empty( $attributes['showReactions'] ),
				'show_citations'    => ! empty( $attributes['showCitations'] ),
				'section_notes'     => $notes,
				'preview_on_hover'  => ! empty( $attributes['previewOnHover'] ),
				'show_reader_notes' => ! empty( $attributes['showReaderNotes'] ),
			);
		} elseif ( ! empty( $attributes['previewOnHover'] ) ) {
			// Hover previews only — merge just the preview text into items.
			foreach ( $items as &$item ) {
				if ( isset( $section_map[ $item['slug'] ] ) ) {
					$item['preview'] = $section_map[ $item['slug'] ]['preview'];
				}
			}
			unset( $item );
			$guide = array( 'preview_on_hover' => true );
		}

		// Always propagate the hover flag so render_list() can use it.
		if ( ! empty( $attributes['previewOnHover'] ) ) {
			$guide['preview_on_hover'] = true;
		}

		// Reader notes work independently of guide mode.
		if ( ! empty( $attributes['showReaderNotes'] ) ) {
			$guide['show_reader_notes'] = true;
		}

		$min = (int) TOCguide_Settings::get_value( 'min_headings', 2 );
		if ( isset( $attributes['minHeadings'] ) && (int) $attributes['minHeadings'] >= 1 ) {
			$min = (int) $attributes['minHeadings'];
		}
		if ( count( $items ) < $min ) {
			return '';
		}

		$settings   = TOCguide_Settings::get();
		$list_tag   = ! empty( $attributes['ordered'] ) ? 'ol' : 'ul';
		$title_raw  = isset( $attributes['title'] ) ? (string) $attributes['title'] : '';
		$title_text = trim( wp_strip_all_tags( $title_raw ) );
		$label      = '' !== $title_text ? $title_text : __( 'Table of Contents', 'tocguide' );
		$show_title = ! isset( $attributes['showTitle'] ) || ! empty( $attributes['showTitle'] );
		$show_title = $show_title && '' !== $title_text;
		$title_tag  = self::title_tag_from_attributes( $attributes );
		$preset     = self::style_slug_from_attributes( $attributes );
		$class_name = isset( $attributes['className'] ) ? (string) $attributes['className'] : '';
		$classes    = array(
			'tocguide',
			'tocguide--' . $preset,
		);

		/*
		 * Gutenberg Block Styles add is-style-{name} via className, which
		 * get_block_wrapper_attributes() already prints. Shortcode/auto-insert
		 * ($wrap = false) must add the class themselves.
		 */
		if ( ! $wrap || false === strpos( $class_name, 'is-style-' ) ) {
			$classes[] = 'is-style-' . $preset;
		}

		if ( ! empty( $attributes['collapsible'] ) ) {
			$classes[] = 'is-collapsible';
		}
		$show_close = ! array_key_exists( 'showClose', $attributes ) || ! empty( $attributes['showClose'] );
		$focus_mode = ! empty( $attributes['focusMode'] );
		if ( $show_close ) {
			$classes[] = 'has-close';
		}
		if ( $focus_mode ) {
			$classes[] = 'has-focus-mode';
		}
		if ( ! empty( $attributes['sticky'] ) ) {
			$classes[] = 'is-sticky';
		}
		$fixed_left = ! empty( $attributes['fixedLeft'] );
		if ( ! $fixed_left && isset( $settings['auto_insert'] ) && 'fixed-left' === $settings['auto_insert'] ) {
			$fixed_left = true;
		}
		if ( $fixed_left ) {
			$classes[] = 'is-fixed-left';
		}

		/*
		 * The left-edge panel stays open. A collapsed dock hides the
		 * headings and leaves only the title and tools.
		 */
		if ( ! empty( $attributes['collapsedDefault'] ) && ! $fixed_left ) {
			$classes[] = 'is-collapsed';
		}
		$design_vars = TOCguide_Settings::design_css_vars();
		if ( TOCguide_Settings::has_design_colors() ) {
			$classes[] = 'has-design-colors';
		}
		if ( ! empty( $attributes['compact'] ) ) {
			$classes[] = 'is-compact';
		}
		if ( ! empty( $attributes['hideMarkers'] ) ) {
			$classes[] = 'is-no-markers';
		}
		if ( ! empty( $attributes['twoColumns'] ) ) {
			$classes[] = 'has-columns-2';
		}
		if ( ! empty( $attributes['underlineLinks'] ) ) {
			$classes[] = 'has-underlined-links';
		}
		if ( ! empty( $attributes['ordered'] ) && isset( $attributes['numbering'] ) && 'nested' === $attributes['numbering'] ) {
			$classes[] = 'is-nested-counters';
		}
		$isolate = TOCguide_Settings::excludes_theme_styles( $attributes );
		if ( $isolate ) {
			$classes[] = 'is-theme-isolated';
		}
		$classes = array_merge( $classes, TOCguide_Settings::appearance_classes() );

		$highlight = array_key_exists( 'highlightActive', $attributes )
			? ! empty( $attributes['highlightActive'] )
			: ! empty( $settings['highlight_active'] );
		if ( $highlight ) {
			$classes[] = 'has-scroll-spy';
		}
		if ( ! empty( $attributes['guideMode'] ) ) {
			$classes[] = 'has-guide-mode';
		}
		if ( ! empty( $attributes['previewOnHover'] ) ) {
			$classes[] = 'has-hover-preview';
		}
		if ( ! empty( $attributes['showExport'] ) ) {
			$classes[] = 'has-export';
		}
		if ( ! empty( $attributes['showReaderNotes'] ) ) {
			$classes[] = 'has-reader-notes';
		}
		if ( ! empty( $attributes['showReadingProgress'] ) ) {
			$classes[] = 'has-reading-progress';
		}
		if ( ! empty( $attributes['showBookmark'] ) ) {
			$classes[] = 'has-bookmark';
		}

		$max_height = isset( $attributes['maxHeight'] ) ? (int) $attributes['maxHeight'] : 0;
		if ( $max_height > 0 ) {
			$classes[] = 'has-max-height';
		}

		$offset = (int) $settings['scroll_offset'];
		if ( isset( $attributes['scrollOffset'] ) && (int) $attributes['scrollOffset'] >= 0 ) {
			$offset = (int) $attributes['scrollOffset'];
		}

		$smooth = 'inherit';
		if ( ! empty( $attributes['smoothScroll'] ) ) {
			$smooth = sanitize_key( $attributes['smoothScroll'] );
		}
		if ( 'on' === $smooth ) {
			$smooth_flag = '1';
		} elseif ( 'off' === $smooth ) {
			$smooth_flag = '0';
		} else {
			$smooth_flag = ! empty( $settings['smooth_scroll'] ) ? '1' : '0';
		}

		$class_attr  = implode( ' ', array_map( 'sanitize_html_class', $classes ) );
		$style_parts = array( '--tocguide-offset:' . (int) $offset . 'px' );
		if ( $max_height > 0 ) {
			$style_parts[] = '--tocguide-max-height:' . $max_height . 'px';
		}

		foreach ( $design_vars as $css_prop => $val ) {
			if ( '' !== $val ) {
				$style_parts[] = $css_prop . ':' . $val;
			}
		}

		$style_attr = implode( ';', $style_parts );

		// Build extra data attributes for guide mode, study tools, and accessibility.
		$guide_attrs = array();

		// Focus ring style (accessibility setting).
		$focus_style = isset( $settings['focus_style'] ) ? sanitize_key( $settings['focus_style'] ) : 'default';
		if ( 'default' !== $focus_style ) {
			$guide_attrs['data-tocguide-focus'] = $focus_style;
		}

		// data-tocguide-post is needed by any feature that uses localStorage.
		$needs_post_id = ! empty( $attributes['guideMode'] )
			|| ! empty( $attributes['showBookmark'] )
			|| ! empty( $attributes['showReaderNotes'] )
			|| $show_close
			|| $focus_mode;
		if ( $needs_post_id ) {
			$guide_attrs['data-tocguide-post'] = (string) $post_id;
		}

		if ( ! empty( $attributes['guideMode'] ) ) {
			if ( ! empty( $attributes['trackProgress'] ) ) {
				$guide_attrs['data-tocguide-progress'] = '1';
			}
			if ( ! empty( $guide['show_citations'] ) ) {
				$cite_style = isset( $attributes['citationStyle'] ) ? sanitize_key( $attributes['citationStyle'] ) : 'apa';
				$cite_meta  = self::citation_meta( $post_id, $cite_style );
				if ( ! empty( $cite_meta ) ) {
					$guide_attrs['data-tocguide-meta'] = (string) wp_json_encode( $cite_meta );
				}
			}
		}

		if ( ! empty( $attributes['showReadingProgress'] ) ) {
			$guide_attrs['data-tocguide-reader-progress'] = '1';
		}
		if ( ! empty( $attributes['showBookmark'] ) ) {
			$guide_attrs['data-tocguide-bookmark'] = '1';
		}
		if ( ! empty( $attributes['showReaderNotes'] ) ) {
			$guide_attrs['data-tocguide-reader-notes'] = '1';
		}

		if ( $wrap ) {
			$wrapper_args = array_merge(
				array(
					'class'                => $class_attr,
					'aria-label'           => $label,
					'data-tocguide-offset' => (string) $offset,
					'data-tocguide-smooth' => $smooth_flag,
					'style'                => $style_attr,
				),
				$guide_attrs
			);
			$wrapper      = get_block_wrapper_attributes( $wrapper_args );
			$html         = '<nav ' . $wrapper . '>';
		} else {
			$extra = '';
			foreach ( $guide_attrs as $attr_name => $attr_val ) {
				$extra .= ' ' . esc_attr( $attr_name ) . '="' . esc_attr( $attr_val ) . '"';
			}
			$html = sprintf(
				'<nav class="%1$s" aria-label="%2$s" data-tocguide-offset="%3$s" data-tocguide-smooth="%4$s" style="%5$s"%6$s>',
				esc_attr( $class_attr . ' wp-block-tocguide-table-of-contents' ),
				esc_attr( $label ),
				esc_attr( (string) $offset ),
				esc_attr( $smooth_flag ),
				esc_attr( $style_attr ),
				$extra
			);
		}

		// Hidden live region — picks up copy-to-clipboard and other state changes.
		$html .= '<span class="tocguide__live-region tocguide__visually-hidden" aria-live="polite" aria-atomic="true"></span>';

		$show_header = $show_title || ! empty( $attributes['collapsible'] ) || $show_close || $focus_mode;
		if ( $show_header ) {
			$html .= '<div class="tocguide__header">';
			if ( $show_title ) {
				$html .= '<' . $title_tag . ' class="tocguide__title">' . esc_html( $title_text ) . '</' . $title_tag . '>';
			} else {
				$html .= '<span class="tocguide__title tocguide__visually-hidden">' . esc_html( $label ) . '</span>';
			}
			$html .= '<div class="tocguide__header-actions">';
			if ( $focus_mode ) {
				$html .= '<button type="button" class="tocguide__focus" aria-pressed="false" aria-label="' . esc_attr__( 'Focused reading', 'tocguide' ) . '">';
				$html .= self::icon_svg( 'focus' );
				$html .= '<span>' . esc_html__( 'Focus', 'tocguide' ) . '</span>';
				$html .= '</button>';
			}
			if ( ! empty( $attributes['collapsible'] ) ) {
				$expanded = ( empty( $attributes['collapsedDefault'] ) || $fixed_left ) ? 'true' : 'false';
				$html    .= '<button type="button" class="tocguide__toggle" aria-expanded="' . esc_attr( $expanded ) . '">';
				$html    .= '<span class="tocguide__visually-hidden">' . esc_html__( 'Toggle table of contents', 'tocguide' ) . '</span>';
				$html    .= '<span class="tocguide__toggle-icon" aria-hidden="true">' . self::icon_svg( 'chevron' ) . '</span>';
				$html    .= '</button>';
			}
			if ( $show_close ) {
				$html .= '<button type="button" class="tocguide__close" aria-label="' . esc_attr__( 'Close table of contents', 'tocguide' ) . '">';
				$html .= self::icon_svg( 'close' );
				$html .= '</button>';
			}
			$html .= '</div></div>';
		}
		if ( $show_close ) {
			$html .= '<button type="button" class="tocguide__restore" hidden>' . esc_html__( 'Show outline', 'tocguide' ) . '</button>';
		}

		// ── Study tools bar (total time + resume button) ─────────────────────
		$has_total_time = ! empty( $attributes['guideMode'] )
			&& ! empty( $attributes['showReadTime'] )
			&& ! empty( $items );
		$has_resume     = ! empty( $attributes['showBookmark'] );

		if ( $has_total_time || $has_resume ) {
			$html .= '<div class="tocguide__study-bar">';
			if ( $has_total_time ) {
				$total_words = 0;
				foreach ( $items as $item ) {
					if ( isset( $item['word_count'] ) ) {
						$total_words += (int) $item['word_count'];
					}
				}
				$total_mins = max( 1, (int) ceil( $total_words / 200 ) );
				/* translators: %d = estimated total reading time in minutes */
				$html .= '<span class="tocguide__total-time" aria-hidden="true">'
					/* translators: %d = estimated total reading time in minutes */
					. sprintf( esc_html__( '~%d min total', 'tocguide' ), $total_mins )
					. '</span>';
			}
			if ( $has_resume ) {
				$html .= '<button type="button" class="tocguide__resume-btn" hidden>'
					. self::icon_svg( 'resume' )
					. '<span>' . esc_html__( 'Resume', 'tocguide' ) . '</span>'
					. '</button>';
			}
			$html .= '</div>';
		}

		// ── Reading progress bar ──────────────────────────────────────────────
		if ( ! empty( $attributes['showReadingProgress'] ) ) {
			$html .= '<div class="tocguide__reading-wrap"'
				. ' role="progressbar"'
				. ' aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"'
				. ' aria-label="' . esc_attr__( 'Reading progress', 'tocguide' ) . '">'
				. '<div class="tocguide__reading-bar"></div>'
				. '</div>';
		}

		$guide['ordered']      = ! empty( $attributes['ordered'] );
		$guide['isolate']      = $isolate;
		$guide['numbering']    = isset( $attributes['numbering'] ) ? sanitize_key( $attributes['numbering'] ) : 'default';
		$guide['hide_markers'] = ! empty( $attributes['hideMarkers'] );

		$html .= '<div class="tocguide__body">';
		$html .= self::render_list( $items, $list_tag, $guide );
		$html .= '</div>';

		// ── Export / print bar ────────────────────────────────────────────────
		if ( ! empty( $attributes['showExport'] ) ) {
			$post_title = html_entity_decode( get_the_title( $post_id ), ENT_QUOTES, 'UTF-8' );
			$html      .= '<div class="tocguide__export-bar" role="group"'
				. ' aria-label="' . esc_attr__( 'Export table of contents', 'tocguide' ) . '"'
				. ' data-tocguide-export-title="' . esc_attr( $post_title ) . '">';

			$html .= '<button type="button" class="tocguide__export-btn" data-tocguide-action="copy-md"'
				. ' aria-label="' . esc_attr__( 'Copy outline as Markdown', 'tocguide' ) . '">'
				. self::icon_svg( 'copy' )
				. '<span>' . esc_html__( 'Copy', 'tocguide' ) . '</span>'
				. '<span class="tocguide__export-confirm tocguide__visually-hidden" role="status" aria-live="polite"></span>'
				. '</button>';

			$html .= '<button type="button" class="tocguide__export-btn" data-tocguide-action="download-md"'
				. ' aria-label="' . esc_attr__( 'Download outline as Markdown file', 'tocguide' ) . '">'
				. self::icon_svg( 'download' )
				. '<span>' . esc_html__( '.md', 'tocguide' ) . '</span>'
				. '</button>';

			$html .= '<button type="button" class="tocguide__export-btn" data-tocguide-action="download-doc"'
				. ' aria-label="' . esc_attr__( 'Download outline as Word document', 'tocguide' ) . '">'
				. self::icon_svg( 'download' )
				. '<span>' . esc_html__( '.doc', 'tocguide' ) . '</span>'
				. '</button>';

			$html .= '<button type="button" class="tocguide__export-btn" data-tocguide-action="print"'
				. ' aria-label="' . esc_attr__( 'Print table of contents', 'tocguide' ) . '">'
				. self::icon_svg( 'print' )
				. '<span>' . esc_html__( 'Print', 'tocguide' ) . '</span>'
				. '</button>';

			$html .= '</div>';
		}

		$html .= '</nav>';

		if ( ! empty( $settings['schema_markup'] ) ) {
			$html .= self::schema_json( $items, $post_id );
		}

		return $html;
	}

	/**
	 * JSON-LD ItemList for the outline.
	 *
	 * @param array $items   Normalized headings.
	 * @param int   $post_id Post ID.
	 * @return string
	 */
	public static function schema_json( $items, $post_id ) {
		$permalink = get_permalink( $post_id );
		$list      = array();
		$position  = 1;
		foreach ( $items as $item ) {
			$list[] = array(
				'@type'    => 'ListItem',
				'position' => $position,
				'name'     => $item['text'],
				'url'      => $permalink . '#' . $item['slug'],
			);
			++$position;
		}

		$payload = array(
			'@context'        => 'https://schema.org',
			'@type'           => 'ItemList',
			'name'            => __( 'Table of Contents', 'tocguide' ),
			'itemListElement' => $list,
		);

		return '<script type="application/ld+json">' . wp_json_encode( $payload, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS ) . '</script>';
	}

	/**
	 * Inject matching anchor IDs into core/heading blocks on the front end.
	 *
	 * @param string $block_content Rendered block HTML.
	 * @param array  $block         Parsed block.
	 * @return string
	 */
	public static function add_heading_ids( $block_content, $block ) {
		if ( is_admin() || ! is_singular() || empty( $block['blockName'] ) || 'core/heading' !== $block['blockName'] ) {
			return $block_content;
		}

		if ( ! self::should_inject_ids() ) {
			return $block_content;
		}

		$post_id = get_the_ID();
		if ( ! $post_id ) {
			return $block_content;
		}

		$text = trim( wp_strip_all_tags( $block_content ) );
		if ( '' === $text ) {
			return $block_content;
		}

		if ( self::should_skip( $block, $block_content ) ) {
			return $block_content;
		}

		$headings = self::get_all( $post_id );

		static $pointers = array();
		$pointer         = isset( $pointers[ $post_id ] ) ? $pointers[ $post_id ] : 0;

		if ( ! isset( $headings[ $pointer ] ) ) {
			return $block_content;
		}
		$pointers[ $post_id ] = $pointer + 1;

		$slug = $headings[ $pointer ]['slug'];

		if ( class_exists( 'WP_HTML_Tag_Processor' ) ) {
			$processor = new WP_HTML_Tag_Processor( $block_content );
			while ( $processor->next_tag() ) {
				$tag = $processor->get_tag();
				if ( $tag && preg_match( '/^H[1-6]$/', $tag ) ) {
					if ( null === $processor->get_attribute( 'id' ) ) {
						$processor->set_attribute( 'id', $slug );
					}
					return $processor->get_updated_html();
				}
			}
			return $block_content;
		}

		if ( preg_match( '/<h[1-6][^>]*\sid=/i', $block_content ) ) {
			return $block_content;
		}

		return preg_replace( '/(<h[1-6])(\s|>)/i', '$1 id="' . esc_attr( $slug ) . '"$2', $block_content, 1 );
	}

	// -------------------------------------------------------------------------
	// Page-builder compatibility
	// -------------------------------------------------------------------------

	/**
	 * Extract headings from an arbitrary HTML string.
	 *
	 * Used as a fallback for page-builder content that does not store headings
	 * as core/heading blocks.  Intentionally simple: does not execute shortcodes
	 * so there is no risk of recursion.
	 *
	 * @param string $html Raw HTML or mixed HTML/text to scan.
	 * @return array Heading entries identical in shape to collect() output.
	 */
	public static function get_all_from_html( $html ) {
		$used      = array();
		$collected = array();

		preg_match_all(
			'/<h([1-6])(\s[^>]*)?>(.+?)<\/h\1>/is',
			$html,
			$matches,
			PREG_SET_ORDER
		);

		foreach ( $matches as $match ) {
			$level   = (int) $match[1];
			$attrs   = $match[2];
			$content = $match[3];
			$text    = trim( wp_strip_all_tags( $content ) );

			if ( '' === $text ) {
				continue;
			}

			// Honour skip classes.
			if ( preg_match( '/class=["\'][^"\']*(?:no-toc|tocguide-skip)[^"\']*["\']/i', $attrs ) ) {
				continue;
			}

			// Preserve any existing id.
			$slug = '';
			if ( preg_match( '/\bid=["\']([^"\']+)["\']/i', $attrs, $id_m ) ) {
				$slug          = $id_m[1];
				$used[ $slug ] = true;
			} else {
				$slug = self::make_slug( $text, $used );
			}

			$collected[] = array(
				'level' => $level,
				'text'  => $text,
				'slug'  => $slug,
			);
		}

		return $collected;
	}

	/**
	 * Extract headings from Elementor JSON post meta.
	 *
	 * Recursively walks _elementor_data to find heading widgets and text widgets
	 * that contain inline heading tags.  No shortcodes are executed.
	 *
	 * @param int $post_id Post ID.
	 * @return array
	 */
	public static function get_all_from_elementor( $post_id ) {
		$raw = get_post_meta( (int) $post_id, '_elementor_data', true );
		if ( empty( $raw ) || ! is_string( $raw ) ) {
			return array();
		}

		$elements = json_decode( $raw, true );
		if ( ! is_array( $elements ) ) {
			return array();
		}

		$html_fragments = array();
		self::collect_elementor_headings( $elements, $html_fragments );

		if ( empty( $html_fragments ) ) {
			return array();
		}

		return self::get_all_from_html( implode( "\n", $html_fragments ) );
	}

	/**
	 * Recursively collect heading HTML from Elementor element tree.
	 *
	 * @param array $elements Elementor element array.
	 * @param array $parts    Accumulator (by reference).
	 */
	private static function collect_elementor_headings( $elements, &$parts ) {
		foreach ( $elements as $el ) {
			$el_type     = isset( $el['elType'] ) ? $el['elType'] : '';
			$widget_type = isset( $el['widgetType'] ) ? $el['widgetType'] : '';
			$settings    = isset( $el['settings'] ) && is_array( $el['settings'] ) ? $el['settings'] : array();

			if ( 'widget' === $el_type ) {
				if ( 'heading' === $widget_type ) {
					// Standard Elementor Heading widget.
					$tag  = ! empty( $settings['header_size'] ) ? sanitize_html_class( $settings['header_size'] ) : 'h2';
					$text = ! empty( $settings['title'] ) ? wp_strip_all_tags( $settings['title'] ) : '';
					if ( '' !== $text && preg_match( '/^h[1-6]$/i', $tag ) ) {
						$parts[] = "<{$tag}>" . esc_html( $text ) . "</{$tag}>";
					}
				} elseif ( in_array( $widget_type, array( 'text-editor', 'theme-post-content' ), true ) ) {
					// Text Editor widget — may contain inline <h*> tags.
					if ( ! empty( $settings['editor'] ) ) {
						$parts[] = wp_kses_post( $settings['editor'] );
					}
				}
			}

			if ( ! empty( $el['elements'] ) && is_array( $el['elements'] ) ) {
				self::collect_elementor_headings( $el['elements'], $parts );
			}
		}
	}

	/**
	 * Extract headings from Bricks Builder post meta.
	 *
	 * @param int $post_id Post ID.
	 * @return array
	 */
	public static function get_all_from_bricks( $post_id ) {
		$data = get_post_meta( (int) $post_id, '_bricks_page_content_2', true );
		if ( empty( $data ) ) {
			return array();
		}

		$elements = is_string( $data ) ? maybe_unserialize( $data ) : $data;
		if ( ! is_array( $elements ) ) {
			return array();
		}

		$parts = array();
		foreach ( $elements as $el ) {
			$name     = isset( $el['name'] ) ? $el['name'] : '';
			$settings = isset( $el['settings'] ) && is_array( $el['settings'] ) ? $el['settings'] : array();

			if ( 'heading' === $name && ! empty( $settings['text'] ) ) {
				$tag = ! empty( $settings['tag'] ) ? sanitize_html_class( $settings['tag'] ) : 'h2';
				if ( ! preg_match( '/^h[1-6]$/i', $tag ) ) {
					$tag = 'h2';
				}
				$parts[] = "<{$tag}>" . wp_strip_all_tags( $settings['text'] ) . "</{$tag}>";
			} elseif ( 'rich-text' === $name && ! empty( $settings['content'] ) ) {
				// Bricks rich text may contain <h*> tags.
				$parts[] = wp_kses_post( $settings['content'] );
			}
		}

		return self::get_all_from_html( implode( "\n", $parts ) );
	}

	/**
	 * Inject IDs into heading tags in an HTML string.
	 *
	 * Used for page-builder content where render_block is not fired.
	 * Skips headings that already have an id attribute or carry a skip class.
	 *
	 * @param string $html     Rendered HTML content.
	 * @param array  $headings Slug-stamped heading map from get_all().
	 * @return string Modified HTML.
	 */
	public static function inject_ids_in_html( $html, $headings ) {
		if ( empty( $headings ) || '' === $html ) {
			return $html;
		}

		$index = 0;
		$total = count( $headings );

		return preg_replace_callback(
			'/<h([1-6])(\s[^>]*)?>/',
			function ( $heading_tag ) use ( $headings, &$index, $total ) {
				$attrs = isset( $heading_tag[2] ) ? $heading_tag[2] : '';

				// Skip headings excluded from the TOC.
				if ( preg_match( '/class=["\'][^"\']*(?:no-toc|tocguide-skip)[^"\']*["\']/i', $attrs ) ) {
					return $heading_tag[0];
				}

				if ( $index >= $total ) {
					return $heading_tag[0];
				}

				// Already has an id — advance the pointer without changing markup.
				if ( preg_match( '/\bid=["\']/i', $attrs ) ) {
					$index++;
					return $heading_tag[0];
				}

				$slug = $headings[ $index ]['slug'];
				$index++;

				return '<h' . $heading_tag[1] . ' id="' . esc_attr( $slug ) . '"' . $attrs . '>';
			},
			$html
		);
	}

	/**
	 * Whether post content contains a plugin shortcode.
	 *
	 * `[tocguide]` shortcode.
	 *
	 * @param string $content Post content.
	 * @return bool
	 */
	public static function content_has_shortcode( $content ) {
		if ( ! is_string( $content ) || '' === $content ) {
			return false;
		}
		return has_shortcode( $content, 'tocguide' );
	}

	/**
	 * Inject IDs only when a TOC will actually be shown.
	 *
	 * @return bool
	 */
	public static function should_inject_ids() {
		$settings = TOCguide_Settings::get();
		if ( 'none' !== $settings['auto_insert'] ) {
			return true;
		}
		$post = get_post();
		if ( ! $post ) {
			return false;
		}
		if ( has_block( 'tocguide/table-of-contents', $post ) ) {
			return true;
		}
		if ( self::content_has_shortcode( $post->post_content ) ) {
			return true;
		}

		// Page-builder shortcode: the [tocguide] shortcode may live inside a builder
		// widget rather than in post_content directly.  Scan builder meta for it.
		if ( self::builder_has_tocguide( $post->ID ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check whether any supported page builder stores a [tocguide] shortcode
	 * reference for this post (without executing any code).
	 *
	 * @param int $post_id Post ID.
	 * @return bool
	 */
	private static function builder_has_tocguide( $post_id ) {
		// Elementor.
		if ( 'builder' === get_post_meta( $post_id, '_elementor_edit_mode', true ) ) {
			$data = get_post_meta( $post_id, '_elementor_data', true );
			if ( is_string( $data ) && false !== strpos( $data, 'tocguide' ) ) {
				return true;
			}
		}

		// Beaver Builder.
		if ( get_post_meta( $post_id, '_fl_builder_enabled', true ) ) {
			$data = get_post_meta( $post_id, '_fl_builder_data', true );
			if ( is_string( $data ) && false !== strpos( $data, 'tocguide' ) ) {
				return true;
			}
			if ( is_array( $data ) ) {
				// Beaver Builder stores modules as objects; json-encode and search.
				$json = wp_json_encode( $data );
				if ( is_string( $json ) && false !== strpos( $json, 'tocguide' ) ) {
					return true;
				}
			}
		}

		// Bricks.
		$bricks = get_post_meta( $post_id, '_bricks_page_content_2', true );
		if ( ! empty( $bricks ) ) {
			$json = is_string( $bricks ) ? $bricks : wp_json_encode( $bricks );
			if ( is_string( $json ) && false !== strpos( $json, 'tocguide' ) ) {
				return true;
			}
		}

		// Oxygen / Breakdance (raw shortcode content stored in meta).
		foreach ( array( 'ct_builder_shortcodes', 'breakdance_data' ) as $meta_key ) {
			$val = get_post_meta( $post_id, $meta_key, true );
			if ( $val && false !== strpos( (string) $val, 'tocguide' ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Allowed Gutenberg Block Style slugs (block.json "styles").
	 *
	 * @return string[]
	 */
	public static function allowed_style_slugs() {
		return array( 'default', 'minimal', 'boxed', 'underline', 'card' );
	}

	/**
	 * Resolve the active style from className (is-style-*) with a stylePreset fallback.
	 *
	 * @param array $attributes Block or shortcode attributes.
	 * @return string
	 */
	public static function style_slug_from_attributes( $attributes ) {
		$allowed    = self::allowed_style_slugs();
		$class_name = isset( $attributes['className'] ) ? (string) $attributes['className'] : '';
		$preset     = 'default';

		if ( preg_match( '/(?:^|\s)is-style-([a-z0-9-]+)/', $class_name, $matches ) ) {
			$preset = sanitize_key( $matches[1] );
		} elseif ( ! empty( $attributes['stylePreset'] ) ) {
			$preset = sanitize_key( $attributes['stylePreset'] );
		}

		if ( ! in_array( $preset, $allowed, true ) ) {
			return 'default';
		}

		return $preset;
	}

	/**
	 * Allowed title element for the outline heading.
	 *
	 * @param array $attributes Block attributes.
	 * @return string
	 */
	public static function title_tag_from_attributes( $attributes ) {
		$allowed = array( 'p', 'h2', 'h3', 'h4' );
		$tag     = isset( $attributes['titleTag'] ) ? strtolower( (string) $attributes['titleTag'] ) : 'p';
		return in_array( $tag, $allowed, true ) ? $tag : 'p';
	}
}
