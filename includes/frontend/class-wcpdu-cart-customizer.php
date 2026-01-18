<?php
/**
 * Display uploaded design files in cart & checkout
 *
 * @package WooCommerce_Product_Design_Upload
 */

if (!defined('ABSPATH')) {
	exit;
}

class WCPDU_Cart_Customizer {

	public function __construct() {
		add_filter('woocommerce_add_cart_item_data', [$this, 'add_to_cart'], 10, 2);
	}

	public function add_to_cart($cart_item_data, $product_id) {
		if (empty($_POST['wcpdu_custom_design'])) {
			return $cart_item_data;
		}

		$cart_item_data['wcpdu_custom_design'] = [
			'image' => sanitize_textarea_field($_POST['wcpdu_custom_design']),
		];

		return $cart_item_data;
	}
}
