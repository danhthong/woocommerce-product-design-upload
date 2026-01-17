<?php
/**
 * Display uploaded design files in Admin Order Details
 *
 * Meta key source: wcpdu_design_files (ORDER ITEM META)
 *
 * @package WooCommerce_Product_Design_Upload
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WCPDU_Admin_Order_Details {

	/**
	 * Order item meta key
	 *
	 * @var string
	 */
	private $meta_key = 'wcpdu_design_files';

	/**
	 * Constructor
	 */
	public function __construct() {

		// Show in Admin Order page (top section)
		add_action(
			'woocommerce_admin_order_data_after_order_details',
			array( $this, 'render_order_design_files' )
		);
	}

	/**
	 * Render uploaded design files in admin order page
	 *
	 * @param WC_Order $order
	 */
	public function render_order_design_files( $order ) {

		if ( ! $order instanceof WC_Order ) {
			return;
		}

		$items = $order->get_items();

		if ( empty( $items ) ) {
			return;
		}

		echo '<div class="form-field form-field-wide wc-customer-uploaded-design-files">';
		echo '<div class="wcpdu-order-design-files">';
		echo '<h3>' . esc_html__( 'Customer Design Files', 'wcpdu' ) . '</h3>';

		foreach ( $items as $item ) {

			/**
			 * 🔥 READ FROM ORDER ITEM META
			 * meta_key = wcpdu_design_files
			 */
			$files = $item->get_meta( $this->meta_key );

			if ( empty( $files ) || ! is_array( $files ) ) {
				continue;
			}

			echo '<div class="wcpdu-order-item-files" style="margin-bottom:20px;">';
			echo '<strong>' . esc_html( $item->get_name() ) . '</strong>';

			echo '<ul style="margin-top:10px;">';

			foreach ( $files as $file ) {

				$file_url  = isset( $file['url'] ) ? esc_url( $file['url'] ) : '';
				$file_name = isset( $file['name'] ) ? esc_html( $file['name'] ) : '';

				if ( empty( $file_url ) ) {
					continue;
				}

				echo '<li style="margin-bottom:12px;">';

				// Image preview (detect by file extension, NOT mime)
				if ( $this->is_image( $file_url ) ) {
					echo '<img src="' . esc_url( $file_url ) . '"
						style="max-width:240px;display:block;margin-bottom:6px;
						border:1px solid #ddd;padding:3px;background:#fff;"
						alt="' . esc_attr( $file_name ) . '" />';
				}

				// File link
				echo '<a href="' . esc_url( $file_url ) . '" target="_blank" rel="noopener noreferrer">';
				echo $file_name ? esc_html( $file_name ) : esc_html__( 'View file', 'wcpdu' );
				echo '</a>';

				echo '</li>';
			}

			echo '</ul>';
			echo '</div>';
		}

		echo '</div>';
		echo '</div>';
	}

	/**
	 * Detect image by URL extension
	 *
	 * @param string $url
	 * @return bool
	 */
	private function is_image( $url ) {

		if ( empty( $url ) ) {
			return false;
		}

		$path = parse_url( $url, PHP_URL_PATH );
		$ext  = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );

		return in_array(
			$ext,
			array( 'jpg', 'jpeg', 'png', 'gif', 'webp' ),
			true
		);
	}
}
