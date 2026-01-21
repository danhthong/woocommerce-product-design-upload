<?php
/**
 * Product meta settings for enabling design upload
 *
 * @package WooCommerce_Product_Design_Upload
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WCPDU_Admin_Product_Meta {

	/**
	 * Meta key.
	 *
	 * @var string
	 */
	const META_KEY = '_wcpdu_enable_upload';

	/**
	 * Constructor.
	 */
	public function __construct() {

		add_action(
			'woocommerce_product_options_general_product_data',
			[ $this, 'add_product_option' ]
		);

		add_action(
			'woocommerce_admin_process_product_object',
			[ $this, 'save_product_option' ]
		);
	}

	/**
	 * Add checkbox to product general tab.
	 *
	 * @return void
	 */
	public function add_product_option() {

		woocommerce_wp_checkbox(
			[
				'id'          => self::META_KEY,
				'label'       => __( 'Enable design upload', 'product-design-upload-for-ecommerce' ),
				'description' => __( 'Allow customers to upload design files for this product.', 'product-design-upload-for-ecommerce' ),
			]
		);
	}

	/**
	 * Save product option.
	 *
	 * @param WC_Product $product Product object.
	 * @return void
	 */
	public function save_product_option( $product ) {

		if ( ! is_admin() ) {
			return;
		}

		if ( ! $product instanceof WC_Product ) {
			return;
		}

		if ( ! $this->verify_woocommerce_product_nonce() ) {
			return;
		}

		$value = isset( $_POST[ self::META_KEY ] ) ? 'yes' : 'no'; // phpcs:ignore WordPress.Security.NonceVerification.Missing

		$product->update_meta_data( self::META_KEY, $value );
	}

	/**
	 * Verify WooCommerce product edit nonce.
	 *
	 * @return bool
	 */
	private function verify_woocommerce_product_nonce() {

		if ( empty( $_POST['woocommerce_meta_nonce'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return false;
		}

		$nonce = sanitize_text_field( wp_unslash( $_POST['woocommerce_meta_nonce'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		return (bool) wp_verify_nonce( $nonce, 'woocommerce_save_data' );
	}
}
