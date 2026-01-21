<?php
/**
 * Store custom design data into cart item
 *
 * @package WooCommerce_Product_Design_Upload
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WCPDU_Cart_Customizer {

	/**
	 * Nonce action.
	 *
	 * @var string
	 */
	private $nonce_action = 'wcpdu_add_to_cart';

	/**
	 * Nonce field name.
	 *
	 * @var string
	 */
	private $nonce_name = 'wcpdu_nonce';

	/**
	 * POST field name.
	 *
	 * @var string
	 */
	private $post_key = 'wcpdu_custom_design';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter(
			'woocommerce_add_cart_item_data',
			[ $this, 'add_to_cart' ],
			10,
			3
		);
	}

	/**
	 * Add custom design data to cart item.
	 *
	 * @param array $cart_item_data
	 * @param int   $product_id
	 * @param int   $variation_id
	 * @return array
	 */
	public function add_to_cart( $cart_item_data, $product_id, $variation_id ) {

		if ( empty( $_POST[ $this->post_key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return $cart_item_data;
		}

		if ( empty( $_POST[ $this->nonce_name ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return $cart_item_data;
		}

		$nonce = sanitize_text_field( wp_unslash( $_POST[ $this->nonce_name ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		if ( ! wp_verify_nonce( $nonce, $this->nonce_action ) ) {
			return $cart_item_data;
		}

		$raw = wp_unslash( $_POST[ $this->post_key ] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$raw = (string) $raw;

		$cart_item_data[ $this->post_key ] = [
			'image' => sanitize_textarea_field( $raw ),
		];

		return $cart_item_data;
	}
}
