<?php
/**
 * Frontend functionality
 *
 * @package WooCommerce_Product_Design_Upload
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WCPDU_Frontend {

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->load_dependencies();
		$this->init_hooks();
	}

	/**
	 * Load required frontend classes.
	 *
	 * @return void
	 */
	private function load_dependencies() {

		// Helpers.
		require_once WCPDU_PLUGIN_DIR . 'includes/helpers/wcpdu-helpers.php';

		// Frontend core.
		require_once WCPDU_PLUGIN_DIR . 'includes/frontend/class-wcpdu-upload-field.php';
		require_once WCPDU_PLUGIN_DIR . 'includes/frontend/class-wcpdu-cart-display.php';
		require_once WCPDU_PLUGIN_DIR . 'includes/frontend/class-wcpdu-order-display.php';
		require_once WCPDU_PLUGIN_DIR . 'includes/frontend/class-wcpdu-customizer.php';
		require_once WCPDU_PLUGIN_DIR . 'includes/frontend/class-wcpdu-customizer-assets.php';
		require_once WCPDU_PLUGIN_DIR . 'includes/frontend/class-wcpdu-cart-customizer.php';
		require_once WCPDU_PLUGIN_DIR . 'includes/frontend/class-wcpdu-order-customizer.php';

		// Init classes.
		new WCPDU_Upload_Field();
		new WCPDU_Cart_Display();
		new WCPDU_Order_Display();
		new WCPDU_Customizer();
		new WCPDU_Customizer_Assets();
		new WCPDU_Cart_Customizer();
		new WCPDU_Order_Customizer();
	}

	/**
	 * Initialize frontend hooks.
	 *
	 * @return void
	 */
	private function init_hooks() {

		add_action( 'wp_footer', function () {
			?>
			<div id="wcpdu-lightbox" style="display:none;">
				<div class="wcpdu-lightbox-overlay"></div>
				<div class="wcpdu-lightbox-content">
					<img src="" alt="">
					<span class="wcpdu-lightbox-close">&times;</span>
				</div>
			</div>
			<?php
		});

		add_action( 'wp_enqueue_scripts', function () {

			wp_enqueue_style(
				'wcpdu-lightbox',
				WCPDU_PLUGIN_URL . 'assets/css/wcpdu-lightbox.css',
				[],
				WCPDU_VERSION
			);

			wp_enqueue_script(
				'wcpdu-lightbox',
				WCPDU_PLUGIN_URL . 'assets/js/wcpdu-lightbox.js',
				[ 'jquery' ],
				WCPDU_VERSION,
				true
			);
		});

	}
}
