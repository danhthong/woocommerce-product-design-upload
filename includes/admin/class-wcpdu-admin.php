<?php
/**
 * Admin functionality
 *
 * @package WooCommerce_Product_Design_Upload
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WCPDU_Admin {

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->load_dependencies();

		add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
	}

	/**
	 * Load admin dependencies.
	 *
	 * @return void
	 */
	private function load_dependencies() {

		require_once WCPDU_PLUGIN_DIR . 'includes/admin/class-wcpdu-admin-product-meta.php';
		require_once WCPDU_PLUGIN_DIR . 'includes/admin/class-wcpdu-admin-settings.php';
		require_once WCPDU_PLUGIN_DIR . 'includes/admin/class-wcpdu-admin-order-meta.php';
		require_once WCPDU_PLUGIN_DIR . 'includes/admin/class-wcpdu-admin-order-details.php';

		new WCPDU_Admin_Settings();
		new WCPDU_Admin_Order_Meta();        // SAVE
		new WCPDU_Admin_Order_Details();     // DISPLAY
		new WCPDU_Admin_Product_Meta();
	}

	/**
	 * Add plugin menu to admin.
	 *
	 * @return void
	 */
	public function add_admin_menu() {

		add_submenu_page(
			'woocommerce',
			__( 'Design Upload', 'ro-print-design-upload' ),
			__( 'Design Upload', 'ro-print-design-upload' ),
			'manage_woocommerce',
			'wcpdu-settings',
			[ $this, 'render_settings_page' ]
		);
	}

	/**
	 * Render settings page.
	 *
	 * @return void
	 */
	public function render_settings_page() {

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'Product Design Upload Settings', 'ro-print-design-upload' ) . '</h1>';

		/**
		 * Settings page content will be handled here
		 * by WCPDU_Admin_Settings class
		 */
		do_action( 'wcpdu_render_settings_page' );

		echo '</div>';
	}

	/**
	 * Enqueue admin styles & scripts.
	 *
	 * @param string $hook
	 * @return void
	 */
	public function enqueue_assets( $hook ) {

		// Only load on WooCommerce & plugin pages
		if ( strpos( $hook, 'woocommerce_page_wcpdu-settings' ) === false
			&& strpos( $hook, 'shop_order' ) === false
			&& strpos( $hook, 'product' ) === false ) {
			return;
		}

		wp_enqueue_style(
			'wcpdu-admin',
			WCPDU_PLUGIN_URL . 'assets/css/admin.css',
			[],
			WCPDU_VERSION
		);

		wp_enqueue_script(
			'wcpdu-admin',
			WCPDU_PLUGIN_URL . 'assets/js/admin.js',
			[ 'jquery' ],
			WCPDU_VERSION,
			true
		);

		wp_localize_script(
			'wcpdu-admin',
			'wcpduAdmin',
			[
				'nonce' => wp_create_nonce( 'wcpdu_admin_nonce' ),
			]
		);
	}
}
