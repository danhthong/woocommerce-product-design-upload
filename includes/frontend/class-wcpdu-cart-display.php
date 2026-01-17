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
	 * @param array $item_data
	 * @param array $cart_item
	 * @return array
	 */
  public function display_cart_item_data( $item_data, $cart_item ) {

    if ( empty( $cart_item[ $this->cart_key ] ) ) {
      return $item_data;
    }

    foreach ( $cart_item[ $this->cart_key ] as $file ) {

      $name = esc_html( $file['name'] ?? '' );
      $url  = esc_url( $file['url'] ?? '' );
      $type = $file['type'] ?? '';

      if ( empty( $url ) ) {
        continue;
      }

      $value = '';

      // Image preview
      if ( $this->is_image( $type ) ) {
        $value .= sprintf(
          '<img src="%s" alt="%s" style="max-width:80px;display:block;margin-bottom:5px;" />',
          $url,
          $name
        );
      }

      // File link
      $value .= sprintf(
        '<a href="%s" target="_blank" rel="noopener" data-wcpdu-lightbox>%s</a>',
        $url,
        $name ? $name : esc_html__( 'View file', 'wcpdu' )
      );

      $item_data[] = [
        'key'     => esc_html__( 'Design File', 'wcpdu' ),
        'value'   => $value,
        'display' => '',
      ];
    }

    return $item_data;
  }

  /**
   * Check if file is an image.
   *
   * @param string $mime
   * @return bool
   */
  private function is_image( $mime ) {

    return in_array(
      $mime,
      [
        'image/jpeg',
        'image/png',
        'image/webp',
      ],
      true
    );
  }
}
