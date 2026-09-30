<?php
/**
 * Admin settings and support screens.
 *
 * @package TOCguide
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin settings UI. Loaded only when is_admin().
 */
class TOCguide_Admin {

	/**
	 * Singleton.
	 *
	 * @var TOCguide_Admin|null
	 */
	private static $instance = null;

	/**
	 * Get instance.
	 *
	 * @return TOCguide_Admin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Hook admin actions.
	 */
	public function boot() {
		add_action( 'admin_menu', array( $this, 'menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
		add_action( 'admin_notices', array( $this, 'welcome_notice' ) );
		add_action( 'wp_ajax_tocguide_dismiss_welcome', array( $this, 'dismiss_welcome' ) );
		add_action( 'admin_head-settings_page_tocguide', array( $this, 'help_tabs' ) );
	}

	/**
	 * Settings → TOCguide.
	 */
	public function menu() {
		add_options_page(
			__( 'TOCguide', 'tocguide' ),
			__( 'TOCguide', 'tocguide' ),
			'manage_options',
			'tocguide',
			array( $this, 'render' )
		);
	}

	/**
	 * Enqueue admin CSS on our screen only.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function assets( $hook ) {
		if ( get_transient( 'tocguide_activation_redirect' ) && ! get_user_meta( get_current_user_id(), 'tocguide_welcome_dismissed', true ) ) {
			wp_enqueue_script(
				'tocguide-welcome',
				TOCGUIDE_URL . 'admin/js/welcome.js',
				array(),
				TOCGUIDE_VERSION,
				true
			);
		}

		if ( 'settings_page_tocguide' !== $hook ) {
			return;
		}
		wp_enqueue_style(
			'tocguide-admin',
			TOCGUIDE_URL . 'admin/css/admin.css',
			array(),
			TOCGUIDE_VERSION
		);
		wp_enqueue_style( 'tocguide-table-of-contents-style' );
		wp_enqueue_script(
			'tocguide-admin',
			TOCGUIDE_URL . 'admin/js/admin.js',
			array(),
			TOCGUIDE_VERSION,
			true
		);
		wp_localize_script(
			'tocguide-admin',
			'tocguideAdmin',
			array(
				'fontStacks'     => TOCguide_Settings::font_family_stacks(),
				'previewUpdated' => __( 'Outline preview updated.', 'tocguide' ),
				'lengthHints'    => array(
					'size'   => __( 'Use px, rem, or em. A whole number is saved as pixels. A small decimal is saved as rem.', 'tocguide' ),
					'box'    => __( 'Use one to four lengths, such as 1rem or 12px 16px. A whole number is saved as pixels.', 'tocguide' ),
					'signed' => __( 'Use a length such as -0.02em. A number without a unit is saved as em.', 'tocguide' ),
					'number' => __( 'Use a unitless number, such as 1.6.', 'tocguide' ),
				),
			)
		);
	}

	/**
	 * Help tabs on the settings screen.
	 */
	public function help_tabs() {
		$screen = get_current_screen();
		if ( ! $screen ) {
			return;
		}
		$screen->add_help_tab(
			array(
				'id'      => 'tocguide-block',
				'title'   => __( 'Using the block', 'tocguide' ),
				'content' => '<p>' . esc_html__( 'Edit a post, click the inserter, and search for “Table of Contents”. The outline is built from Heading blocks in that post.', 'tocguide' ) . '</p>',
			)
		);
		$screen->add_help_tab(
			array(
				'id'      => 'tocguide-auto',
				'title'   => __( 'Auto-generate', 'tocguide' ),
				'content' => '<p>' . esc_html__( 'Settings → TOCguide can print the Table of Contents block at the top of content or after the first heading. Customize title, heading levels, style, and layout there. Manual blocks and the [tocguide] shortcode still skip auto-generate so you never get two outlines.', 'tocguide' ) . '</p>',
			)
		);
		$screen->add_help_tab(
			array(
				'id'      => 'tocguide-shortcode',
				'title'   => __( 'Shortcode', 'tocguide' ),
				'content' => '<p><code>[tocguide]</code> ' . esc_html__( 'prints the same outline in classic content, widgets, or a theme template. The Gutenberg block is the primary placement method.', 'tocguide' ) . '</p>',
			)
		);
		$screen->set_help_sidebar(
			'<p><strong>' . esc_html__( 'Support', 'tocguide' ) . '</strong></p>' .
			'<p><a href="https://github.com/matthummel-pa/tocguide/issues" target="_blank" rel="noopener noreferrer">' . esc_html__( 'GitHub issues', 'tocguide' ) . '</a></p>' .
			'<p><a href="https://matthummel-pa.github.io/tocguide/" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Online docs', 'tocguide' ) . '</a></p>'
		);
	}

	/**
	 * Render the settings / support page.
	 */
	public function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'settings'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! in_array( $tab, array( 'settings', 'support' ), true ) ) {
			$tab = 'settings';
		}

		$settings = TOCguide_Settings::get();
		$types    = get_post_types( array( 'public' => true ), 'objects' );

		include TOCGUIDE_DIR . 'admin/views/settings.php';
	}

	/**
	 * One-time welcome notice after activation.
	 */
	public function welcome_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( ! get_transient( 'tocguide_activation_redirect' ) ) {
			return;
		}
		if ( get_user_meta( get_current_user_id(), 'tocguide_welcome_dismissed', true ) ) {
			delete_transient( 'tocguide_activation_redirect' );
			return;
		}
		$screen = get_current_screen();
		if ( $screen && 'settings_page_tocguide' === $screen->id ) {
			return;
		}
		?>
		<div class="notice notice-success is-dismissible tocguide-welcome" data-nonce="<?php echo esc_attr( wp_create_nonce( 'tocguide_dismiss_welcome' ) ); ?>">
			<p>
				<strong><?php esc_html_e( 'TOCguide is ready.', 'tocguide' ); ?></strong>
				<?php esc_html_e( 'Add the Table of Contents block to a post, or auto-generate it under Settings → TOCguide. The [tocguide] shortcode still works in classic content.', 'tocguide' ); ?>
				<a href="<?php echo esc_url( admin_url( 'options-general.php?page=tocguide' ) ); ?>"><?php esc_html_e( 'Open settings', 'tocguide' ); ?></a>
			</p>
		</div>
		<?php
	}

	/**
	 * Persist welcome dismissal.
	 */
	public function dismiss_welcome() {
		check_ajax_referer( 'tocguide_dismiss_welcome', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( null, 403 );
		}
		update_user_meta( get_current_user_id(), 'tocguide_welcome_dismissed', 1 );
		delete_transient( 'tocguide_activation_redirect' );
		wp_send_json_success();
	}
}
