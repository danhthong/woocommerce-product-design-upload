<?php
/**
 * Frontend functionality
 *
 * @package WooCommerce_Product_Design_Upload
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WCPDU_Customizer_Assets {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue' ] );
	}

	/**
	 * Enqueue scripts and styles for the product customizer.
	 *
	 * @return void
	 */
	public function enqueue() {
		if ( ! is_product() ) {
			return;
		}

		global $product;
		if ( ! $product ) {
			return;
		}

		$ver = defined( 'WCPDU_VERSION' ) ? WCPDU_VERSION : '1.0.0';

		$image_id  = $product->get_image_id();
		$image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'full' ) : '';

		wp_enqueue_script(
			'fabric-js',
			WCPDU_PLUGIN_URL . 'assets/vendor/fabric/fabric.min.js',
			[],
			$ver,
			true
		);

		wp_enqueue_script(
			'wcpdu-fabric-editor',
			WCPDU_PLUGIN_URL . 'assets/js/wcpdu-fabric-editor.js',
			[ 'fabric-js', 'jquery' ],
			$ver,
			true
		);

		wp_localize_script(
			'wcpdu-fabric-editor',
			'wcpduCustomizer',
			[
				'productImage' => esc_url( (string) $image_url ),
			]
		);

		wp_enqueue_style(
			'wcpdu-customizer',
			WCPDU_PLUGIN_URL . 'assets/css/wcpdu-customizer.css',
			[],
			$ver
		);
	}
}
