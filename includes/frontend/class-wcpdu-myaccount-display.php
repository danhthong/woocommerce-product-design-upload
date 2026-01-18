<?php
/**
 * Frontend functionality
 *
 * @package WooCommerce_Product_Design_Upload
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

class WCPDU_MyAccount_Display {

  const META_KEY = 'wcpdu_design_files';

  public function __construct() {
    add_action(
      'woocommerce_order_item_meta_end',
      [ $this, 'render_design_files' ],
      10,
      3
    );
  }

  /**
   * Render design files in My Account order details
   */
  public function render_design_files( $item_id, $item, $order ) {

    if ( ! is_account_page() ) {
      return;
    }

    $files = $item->get_meta( self::META_KEY );

    if ( empty( $files ) || ! is_array( $files ) ) {
      return;
    }

    echo '<div class="wcpdu-myaccount-files">';
    echo '<strong>' . esc_html__( 'Your Design', 'wcpdu' ) . '</strong>';

    foreach ( $files as $type => $file ) {

      if ( empty( $file['url'] ) ) {
        continue;
      }

      echo '<div class="wcpdu-file">';
      echo '<a href="' . esc_url( $file['url'] ) . '"
              class="wcpdu-lightbox"
              data-title="' . esc_attr( ucfirst( $type ) ) . '">';
      echo '<img src="' . esc_url( $file['url'] ) . '" />';
      echo '</a>';
      echo '</div>';
    }

    echo '</div>';
  }
}
