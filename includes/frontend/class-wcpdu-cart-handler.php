<?php
/**
 * Handle design file upload and store paths into cart item data.
 *
 * - Saves canvas base64 image as PNG.
 * - Saves original uploaded file.
 * - Forces both into uploads/wcpdu-designs/mm/dd/ for easier cleanup.
 *
 * @package WooCommerce_Product_Design_Upload
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WCPDU_Cart_Handler {

	/**
	 * Gate flag to ensure upload_dir override applies only to this plugin flow.
	 *
	 * @var bool
	 */
	private $wcpdu_force_dir = false;

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
	 * Register hooks.
	 */
	public function __construct() {
		add_filter( 'woocommerce_add_cart_item_data', [ $this, 'add_cart_item_data' ], 20, 3 );
	}

	/**
	 * Attach design file info into cart item data on add-to-cart.
	 *
	 * @param array $cart_item_data Existing cart item data.
	 * @param int   $product_id     Product ID.
	 * @param int   $variation_id   Variation ID.
	 * @return array Modified cart item data.
	 */
	public function add_cart_item_data( $cart_item_data, $product_id, $variation_id ) {

		if ( ! $this->verify_nonce_from_post() ) {
			return $cart_item_data;
		}

		$uploads = wp_upload_dir();
		$data    = [];

		/**
		 * 1) Save FINAL canvas image (base64 -> PNG) into uploads/wcpdu-designs/mm/dd/
		 */
		$png_bytes = $this->get_canvas_png_bytes_from_post();

		if ( '' !== $png_bytes ) {
			$subdir     = $this->get_wcpdu_subdir();
			$target_dir = trailingslashit( $uploads['basedir'] ) . $subdir;

			wp_mkdir_p( $target_dir );

			$filename  = wp_unique_filename( $target_dir, 'wcpdu-final-' . wp_generate_uuid4() . '.png' );
			$file_path = $target_dir . $filename;

			$written = file_put_contents( $file_path, $png_bytes );

			if ( false !== $written && file_exists( $file_path ) ) {
				$data['final'] = [
					'url'  => trailingslashit( $uploads['baseurl'] ) . $subdir . $filename,
					'path' => $file_path,
					'kind' => 'final',
				];
			}
		}

		/**
		 * 2) Save ORIGINAL uploaded file into uploads/wcpdu-designs/mm/dd/
		 */
		if (
			! empty( $_FILES['wcpdu_upload_image'] ) &&
			isset( $_FILES['wcpdu_upload_image']['error'] ) &&
			UPLOAD_ERR_OK === (int) $_FILES['wcpdu_upload_image']['error']
		) {
			if ( ! function_exists( 'wp_handle_upload' ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
			}

			$this->wcpdu_force_dir = true;

			add_filter( 'upload_dir', [ $this, 'wcpdu_upload_dir' ], 9 );
			add_filter( 'wp_handle_upload_prefilter', [ $this, 'rename_original_upload' ], 9 );

			$upload = wp_handle_upload(
				$_FILES['wcpdu_upload_image'],
				[ 'test_form' => false ]
			);

			remove_filter( 'wp_handle_upload_prefilter', [ $this, 'rename_original_upload' ], 9 );
			remove_filter( 'upload_dir', [ $this, 'wcpdu_upload_dir' ], 9 );

			$this->wcpdu_force_dir = false;

			if ( is_array( $upload ) && empty( $upload['error'] ) ) {
				$data['original'] = [
					'url'  => isset( $upload['url'] ) ? (string) $upload['url'] : '',
					'path' => isset( $upload['file'] ) ? (string) $upload['file'] : '',
					'kind' => 'uploaded',
				];
			}
		}

		/**
		 * Persist into cart item and prevent cart item merging.
		 */
		if ( ! empty( $data ) ) {
			$cart_item_data['wcpdu_design_files'] = $data;
			$cart_item_data['wcpdu_uid']          = wp_generate_uuid4();
		}

		return $cart_item_data;
	}

	/**
	 * Verify nonce from POST.
	 *
	 * @return bool
	 */
	private function verify_nonce_from_post() {

		if ( empty( $_POST[ $this->nonce_name ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return false;
		}

		$nonce = sanitize_text_field( wp_unslash( $_POST[ $this->nonce_name ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		return (bool) wp_verify_nonce( $nonce, $this->nonce_action );
	}

	/**
	 * Get PNG bytes from the posted canvas data URL.
	 *
	 * @return string PNG binary bytes or empty string on failure.
	 */
	private function get_canvas_png_bytes_from_post() {

		if ( empty( $_POST['wcpdu_custom_design'] ) || ! is_string( $_POST['wcpdu_custom_design'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return '';
		}

		$raw = (string) wp_unslash( $_POST['wcpdu_custom_design'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		if ( '' === $raw ) {
			return '';
		}

		$raw = trim( $raw );

		if ( str_starts_with( $raw, 'data:image' ) ) {
			$pos = strpos( $raw, 'base64,' );
			if ( false === $pos ) {
				return '';
			}
			$raw = substr( $raw, $pos + 7 );
		}

		$raw = preg_replace( '/\s+/', '', $raw );
		$raw = (string) $raw;

		if ( '' === $raw ) {
			return '';
		}

		if ( ! preg_match( '/^[A-Za-z0-9+\/=]+$/', $raw ) ) {
			return '';
		}

		$bytes = base64_decode( $raw, true );
		if ( false === $bytes || '' === $bytes ) {
			return '';
		}

		return $bytes;
	}

	/**
	 * Build a date-based relative subdir under uploads.
	 *
	 * Example: wcpdu-designs/01/18/
	 *
	 * Uses WordPress timezone via wp_date().
	 *
	 * @return string
	 */
	private function get_wcpdu_subdir() {
		$mm = wp_date( 'm' );
		$dd = wp_date( 'd' );

		return 'wcpdu-designs/' . $mm . '/' . $dd . '/';
	}

	/**
	 * Override WordPress upload directory for this upload flow.
	 *
	 * Forces uploads into: /uploads/wcpdu-designs/mm/dd
	 *
	 * @param array $dirs Upload dir array.
	 * @return array Modified upload dir array.
	 */
	public function wcpdu_upload_dir( $dirs ) {
		if ( empty( $this->wcpdu_force_dir ) ) {
			return $dirs;
		}

		$subdir = '/' . rtrim( $this->get_wcpdu_subdir(), '/' );

		$target_dir = trailingslashit( $dirs['basedir'] ) . ltrim( $subdir, '/' );
		if ( ! file_exists( $target_dir ) ) {
			wp_mkdir_p( $target_dir );
		}

		$dirs['subdir'] = $subdir;
		$dirs['path']   = $target_dir;
		$dirs['url']    = trailingslashit( $dirs['baseurl'] ) . ltrim( $subdir, '/' );

		return $dirs;
	}

	/**
	 * Rename the original uploaded file before WordPress moves it.
	 *
	 * @param array $file Uploaded file array.
	 * @return array Modified file array.
	 */
	public function rename_original_upload( $file ) {
		$name = isset( $file['name'] ) ? (string) $file['name'] : '';
		$ext  = strtolower( pathinfo( $name, PATHINFO_EXTENSION ) );

		if ( empty( $ext ) ) {
			$ext = 'jpg';
		}

		$file['name'] = 'wcpdu-original-' . wp_generate_uuid4() . '.' . $ext;

		return $file;
	}
}
