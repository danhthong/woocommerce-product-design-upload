<?php
/**
 * Frontend functionality
 *
 * @package WooCommerce_Product_Design_Upload
 */

if (!defined('ABSPATH')) {
	exit;
}

class WCPDU_Order_Customizer {

	public function __construct() {
		add_action(
			'woocommerce_checkout_create_order_line_item',
			[$this, 'save_to_order'],
			10,
			4
		);
	}

	public function save_to_order($item, $cart_item_key, $values, $order) {
		if (empty($values['wcpdu_custom_design'])) {
			return;
		}

		$item->add_meta_data(
			'_wcpdu_custom_design',
			$values['wcpdu_custom_design']
		);
	}
}
