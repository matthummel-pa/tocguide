<?php
/**
 * Plugin Name:       TOCguide
 * Plugin URI:        https://github.com/matthummel-pa/tocguide
 * Description:       A lightweight Table of Contents block that auto-generates a linked outline from your post headings. Independent plugin by Matt Hummel — not affiliated with any other product.
 * Version:           1.6.8
 * Requires at least: 6.4
 * Requires PHP:      7.4
 * Author:            Matt Hummel
 * Author URI:        https://matthummel.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       tocguide
 * Domain Path:       /languages
 *
 * @package   TOCguide
 * @copyright 2026 Matt Hummel
 * @license   GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TOCGUIDE_VERSION', '1.6.8' );
define( 'TOCGUIDE_FILE', __FILE__ );
define( 'TOCGUIDE_DIR', plugin_dir_path( __FILE__ ) );
define( 'TOCGUIDE_URL', plugin_dir_url( __FILE__ ) );
define( 'TOCGUIDE_BASENAME', plugin_basename( __FILE__ ) );

require_once TOCGUIDE_DIR . 'includes/class-tocguide-settings.php';
require_once TOCGUIDE_DIR . 'includes/class-tocguide-headings.php';
require_once TOCGUIDE_DIR . 'includes/class-tocguide-plugin.php';

/**
 * Returns the main plugin instance.
 *
 * @return TOCguide_Plugin
 */
function tocguide() {
	return TOCguide_Plugin::instance();
}

tocguide()->boot();

register_activation_hook( TOCGUIDE_FILE, array( 'TOCguide_Plugin', 'activate' ) );
register_deactivation_hook( TOCGUIDE_FILE, array( 'TOCguide_Plugin', 'deactivate' ) );

/**
 * Heading map for a post. Kept as a prefixed wrapper so render.php stays thin.
 *
 * @param int $post_id Post ID.
 * @return array
 */
function tocguide_get_all_headings( $post_id ) {
	return TOCguide_Headings::get_all( $post_id );
}

/**
 * Render nested list markup from heading data.
 *
 * @param array  $headings Heading data with normalized 'level'.
 * @param string $list_tag 'ol' or 'ul'.
 * @return string
 */
function tocguide_render_list( $headings, $list_tag ) {
	return TOCguide_Headings::render_list( $headings, $list_tag );
}
