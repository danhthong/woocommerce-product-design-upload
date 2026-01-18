<?php
/**
 * Handle design file upload and store path into cart item
 *
 * @package WooCommerce_Product_Design_Upload
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WCPDU_Cart_Handler {

	/**
	 * Cart item key
	 */
	const CART_KEY = 'wcpdu_design_files';

	/**
	 * Constructor
	 */
	public function __construct() {

		add_filter(
			'woocommerce_add_cart_item_data',
			[ $this, 'handle_upload_and_store_path' ],
			20,
			3
		);
	}

	/**
	 * Handle canvas design upload and store paths into cart item
	 *
	 * @param array $cart_item_data
	 * @param int   $product_id
	 * @param int   $variation_id
	 * @return array
	 */
	public function handle_upload_and_store_path( $cart_item_data, $product_id, $variation_id ) {

		if (
			empty( $_POST['wcpdu_custom_design'] ) &&
			(
				empty( $_FILES['wcpdu_upload_image'] ) ||
				$_FILES['wcpdu_upload_image']['error'] !== UPLOAD_ERR_OK
			)
		) {
			return $cart_item_data;
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';

		$uploads = wp_upload_dir();
		$dir     = trailingslashit( $uploads['basedir'] ) . 'wcpdu-designs/';
		wp_mkdir_p( $dir );

		$data = [];

		/**
		 * 1️⃣ Save FINAL canvas image (PNG)
		 */
		if ( ! empty( $_POST['wcpdu_custom_design'] ) ) {

			$raw = wp_unslash( $_POST['wcpdu_custom_design'] );

			$base64 = preg_replace(
				'#^data:image/\w+;base64,#i',
				'',
				$raw
			);

			$binary = base64_decode( $base64 );

			if ( $binary !== false ) {
				$filename = uniqid( 'design_' ) . '.png';
				$path     = $dir . $filename;

				file_put_contents( $path, $binary );

				$data['final'] = [
					'url'  => trailingslashit( $uploads['baseurl'] ) . 'wcpdu-designs/' . $filename,
					'path' => $path,
				];
			}
		}

		/**
		 * 2️⃣ Save ORIGINAL uploaded image (FILE)
		 */
		if (
			! empty( $_FILES['wcpdu_upload_image'] ) &&
			$_FILES['wcpdu_upload_image']['error'] === UPLOAD_ERR_OK
		) {

			$upload = wp_handle_upload(
				$_FILES['wcpdu_upload_image'],
				[ 'test_form' => false ]
			);

			if ( empty( $upload['error'] ) ) {
				$data['original'] = [
					'url'  => esc_url_raw( $upload['url'] ),
					'path' => sanitize_text_field( $upload['file'] ),
				];
			}
		}

		if ( empty( $data ) ) {
			return $cart_item_data;
		}

		$cart_item_data[ self::CART_KEY ] = $data;

		// Prevent cart item merge
		$cart_item_data['wcpdu_uid'] = md5( microtime() . wp_rand() );

		return $cart_item_data;
	}

}
