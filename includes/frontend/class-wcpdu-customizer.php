<?php
/**
 * Frontend functionality
 *
 * @package WooCommerce_Product_Design_Upload
 */

if (!defined('ABSPATH')) {
	exit;
}

class WCPDU_Customizer {

	public function __construct() {
		add_action('woocommerce_before_add_to_cart_button', [$this, 'render_customizer']);
	}

	public function render_customizer() {
		global $product;

		if (!$product) {
			return;
		}

		$enabled = $product->get_meta('_wcpdu_enable_upload');
		if ($enabled !== 'yes') {
			return;
		}
		?>
		<div id="wcpdu-customizer" class="wcpdu-customizer">
			<h3><?php esc_html_e('Customize Your Product', 'wcpdu'); ?></h3>

			<input type="file" id="wcpdu-upload-image" name="wcpdu_upload_image" accept="image/*" />

			<div class="wcpdu-canvas-wrapper">
				<canvas id="wcpdu-canvas" width="450" height="450"></canvas>
			</div>

			<div class="wcpdu-toolbar">
				<button
					type="button"
					class="wcpdu-btn wcpdu-remove-object"
					aria-label="Remove selected object"
				>
					✕ Remove
				</button>
			</div>

			<input type="hidden" id="wcpdu-custom-design" name="wcpdu_custom_design">

		</div>
		<?php
	}
}
