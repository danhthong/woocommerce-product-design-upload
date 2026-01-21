<?php
/**
 * Display uploaded design files in cart & checkout
 *
 * @package WooCommerce_Product_Design_Upload
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WCPDU_Cart_Display {

	/**
	 * Cart item key.
	 *
	 * @var string
	 */
	private $cart_key = 'wcpdu_design_files';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter(
			'woocommerce_get_item_data',
			[ $this, 'display_cart_item_data' ],
			10,
			2
		);
	}

	/**
	 * Add design file info to cart & checkout.
	 *
	 * Expects each file item to contain:
	 * - name (optional)
	 * - url  (required)
	 * - kind (optional): uploaded | final
	 *
	 * @param array $item_data
	 * @param array $cart_item
	 * @return array
	 */
	public function display_cart_item_data( $item_data, $cart_item ) {

		if ( empty( $cart_item[ $this->cart_key ] ) || ! is_array( $cart_item[ $this->cart_key ] ) ) {
			return $item_data;
		}

		$uploaded_files = [];
		$result_files   = [];

		foreach ( $cart_item[ $this->cart_key ] as $file ) {

			if ( ! is_array( $file ) ) {
				continue;
			}

			$kind = isset( $file['kind'] ) ? (string) $file['kind'] : 'uploaded';

			if ( 'final' === $kind ) {
				$result_files[] = $file;
			} else {
				$uploaded_files[] = $file;
			}
		}

		if ( ! empty( $uploaded_files ) ) {
			$item_data[] = [
				'key'     => esc_html__( 'Design File', 'product-design-upload-for-ecommerce' ),
				'value'   => $this->render_files_html( $uploaded_files ),
				'display' => '',
			];
		}

		if ( ! empty( $result_files ) ) {
			$item_data[] = [
				'key'     => esc_html__( 'Result File', 'product-design-upload-for-ecommerce' ),
				'value'   => $this->render_files_html( $result_files ),
				'display' => '',
			];
		}

		return $item_data;
	}

	/**
	 * Render files list HTML.
	 *
	 * @param array $files
	 * @return string
	 */
	private function render_files_html( array $files ) {

		$html = '<div class="wcpdu-cart-files">';

		foreach ( $files as $file ) {

			$name = isset( $file['name'] ) ? (string) $file['name'] : '';
			$url  = isset( $file['url'] ) ? (string) $file['url'] : '';

			$url_safe = esc_url( $url );
			if ( empty( $url_safe ) ) {
				continue;
			}

			$label_raw = $name ? $name : __( 'View file', 'product-design-upload-for-ecommerce' );

			$html .= '<div class="wcpdu-cart-file" style="margin:0 0 10px;">';

			$html .= sprintf(
				'<a href="%1$s" rel="noopener" data-wcpdu-lightbox>%2$s</a>',
				$url_safe,
				esc_html( $label_raw )
			);

			$html .= '</div>';
		}

		$html .= '</div>';

		return $html;
	}
}
