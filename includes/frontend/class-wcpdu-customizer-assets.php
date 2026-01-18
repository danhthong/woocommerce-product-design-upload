<?php
/**
 * Frontend functionality
 *
 * @package WooCommerce_Product_Design_Upload
 */

if (!defined('ABSPATH')) {
	exit;
}

class WCPDU_Customizer_Assets {

	public function __construct() {
		add_action('wp_enqueue_scripts', [$this, 'enqueue']);
	}

	public function enqueue() {
		if (!is_product()) {
			return;
		}

		global $product;
		if (!$product) {
			return;
		}

		$image_id  = $product->get_image_id();
		$image_url = wp_get_attachment_image_url($image_id, 'full');

		wp_enqueue_script(
			'fabric-js',
			'https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.0/fabric.min.js',
			[],
			'5.3.0',
			true
		);

		wp_enqueue_script(
			'wcpdu-fabric-editor',
			WCPDU_PLUGIN_URL . 'assets/js/wcpdu-fabric-editor.js',
			['fabric-js', 'jquery'],
			WCPDU_VERSION,
			true
		);

		wp_localize_script(
			'wcpdu-fabric-editor',
			'wcpduCustomizer',
			[
				'productImage' => esc_url($image_url),
			]
		);

		wp_enqueue_style(
			'wcpdu-customizer',
			WCPDU_PLUGIN_URL . 'assets/css/wcpdu-customizer.css',
			[],
			WCPDU_VERSION
		);
	}
}
