<?php
/**
 * Frontend functionality
 *
 * @package WooCommerce_Product_Design_Upload
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WCPDU_Order_Display {

  public function __construct() {
    add_action(
      'woocommerce_order_item_meta_end',
      [ $this, 'render_design_files' ],
      10,
      4
    );
  }

  /**
   * Display uploaded design files in My Account > Order details
   *
   * @param int           $item_id
   * @param WC_Order_Item $item
   * @param WC_Order      $order
   * @param bool          $plain_text
   */
  public function render_design_files( $item_id, $item, $order, $plain_text ) {

    $files = $item->get_meta( 'wcpdu_design_files', true );

    if ( empty( $files ) || ! is_array( $files ) ) {
      return;
    }

    echo '<div class="wcpdu-order-files">';
    echo '<strong>' . esc_html__( 'Uploaded design files:', 'wcpdu' ) . '</strong>';
    echo '<ul>';

    foreach ( $files as $file ) {

      if ( empty( $file['url'] ) ) {
        continue;
      }

      $name = ! empty( $file['name'] )
        ? esc_html( $file['name'] )
        : basename( $file['url'] );

      echo '<li>';
      $is_image = preg_match( '/\.(jpg|jpeg|png|gif|webp)$/i', $file['url'] );

      if ( $is_image ) {

        echo '<a href="' . esc_url( $file['url'] ) . '"data-wcpdu-lightbox>';
          echo esc_html( $name );
        echo '</a>';

      } else {

        echo '<a href="' . esc_url( $file['url'] ) . '" target="_blank" rel="noopener">';
        echo esc_html( $name );
        echo '</a>';
      }
      echo '</li>';
    }

    echo '</ul>';
    echo '</div>';
  }
}
