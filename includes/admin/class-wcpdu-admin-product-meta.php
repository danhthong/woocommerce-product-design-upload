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
	 * Meta key for clipping mask image URL.
	 *
	 * @var string
	 */
	const META_IMG_CLIPPING = '_wcpdu_img_clipping';

	/**
	 * Constructor.
	 */
	public function __construct() {

		add_action(
			'woocommerce_product_options_general_product_data',
			[ $this, 'add_product_option' ]
		);

		add_action( 'woocommerce_product_options_general_product_data',
			[ $this, 'render_fields_in_general_tab' ],
			21
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
				'label'       => __( 'Enable design upload', 'danhthong-print-design-upload' ),
				'description' => __( 'Allow customers to upload design files for this product.', 'danhthong-print-design-upload' ),
			]
		);
	}

	public function render_fields_in_general_tab() {
    global $post;

    $img_clipping = '';
    if ( $post && isset( $post->ID ) ) {
        $product = wc_get_product( (int) $post->ID );
        if ( $product instanceof WC_Product ) {
            $img_clipping = (string) $product->get_meta( self::META_IMG_CLIPPING );
        }
    }

    echo '<div class="options_group">'; // quan trọng: giữ trong panel + style đúng

    woocommerce_wp_text_input([
        'id'          => self::META_IMG_CLIPPING,
        'label'       => __( 'Clipping mask image', 'danhthong-print-design-upload' ),
        'value'       => $img_clipping,
        'placeholder' => 'https://...',
        'desc_tip'    => false,
        'description' => __( 'Optional. Upload a PNG used to limit the editable/design area. Recommended: PNG where allowed area is transparent (window) and outside area is opaque (frame).', 'danhthong-print-design-upload' ),
    ]);

    echo '<p class="form-field wcpdu-img-clipping-actions">';
    echo ' <button type="button" class="button button-primary wcpdu-upload-img-clipping">' . esc_html__( 'Upload', 'danhthong-print-design-upload' ) . '</button>';
    echo ' <button type="button" class="button wcpdu-remove-img-clipping">' . esc_html__( 'Remove', 'danhthong-print-design-upload' ) . '</button>';
    echo '<br/><img class="wcpdu-img-clipping-preview" src="' . esc_url( $img_clipping ) . '" style="max-width:180px;height:auto;display:' . ( $img_clipping ? 'inline-block' : 'none' ) . ';margin-top:8px;border:1px solid #ccd0d4;padding:4px;background:#ccd0d4;" alt="" />';
    echo '</p>';

    echo '</div>';
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

		$img_clipping = isset( $_POST[ self::META_IMG_CLIPPING ] ) ? sanitize_text_field( wp_unslash( $_POST[ self::META_IMG_CLIPPING ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$img_clipping = esc_url_raw( $img_clipping );
		$product->update_meta_data( self::META_IMG_CLIPPING, $img_clipping );
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
