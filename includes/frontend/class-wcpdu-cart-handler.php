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
		$uploads = wp_upload_dir();
		$data    = [];

		/**
		 * 1) Save FINAL canvas image (base64 -> PNG) into uploads/wcpdu-designs/mm/dd/
		 */
		if ( ! empty( $_POST['wcpdu_custom_design'] ) && is_string( $_POST['wcpdu_custom_design'] ) ) {
			$base64 = wp_unslash( $_POST['wcpdu_custom_design'] );

			if ( str_contains( $base64, 'base64,' ) ) {
				$base64 = substr( $base64, strpos( $base64, 'base64,' ) + 7 );
			}

			$image_data = base64_decode( $base64 );

			if ( $image_data !== false ) {
				$subdir     = $this->get_wcpdu_subdir();
				$target_dir = trailingslashit( $uploads['basedir'] ) . $subdir;

				wp_mkdir_p( $target_dir );

				$filename  = 'wcpdu-final-' . wp_generate_uuid4() . '.png';
				$file_path = $target_dir . $filename;

				$written = file_put_contents( $file_path, $image_data );

				if ( $written !== false && file_exists( $file_path ) ) {
					$data['final'] = [
						'url'  => trailingslashit( $uploads['baseurl'] ) . $subdir . $filename,
						'path' => $file_path,
					];
				}
			}
		}

		/**
		 * 2) Save ORIGINAL uploaded file into uploads/wcpdu-designs/mm/dd/
		 */
		if (
			! empty( $_FILES['wcpdu_upload_image'] ) &&
			isset( $_FILES['wcpdu_upload_image']['error'] ) &&
			$_FILES['wcpdu_upload_image']['error'] === UPLOAD_ERR_OK
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
					'url'  => $upload['url'],
					'path' => $upload['file'],
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
