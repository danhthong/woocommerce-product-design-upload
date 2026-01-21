<?php
/**
 * Display uploaded design files in Admin Order Line Items (per item) with frontend-like lightbox.
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
	 * Ensure lightbox markup is printed once.
	 *
	 * @var bool
	 */
	private $printed_lightbox_markup = false;

	/**
	 * Constructor
	 */
	public function __construct() {

		// Render inside each line item in admin order items table.
		add_action(
			'woocommerce_after_order_itemmeta',
			array( $this, 'render_item_design_files' ),
			10,
			3
		);

		// Enqueue lightbox assets on order edit screens only.
		add_action(
			'admin_enqueue_scripts',
			array( $this, 'enqueue_admin_assets' )
		);

		// Print the same lightbox markup as frontend in admin footer.
		add_action(
			'admin_footer',
			array( $this, 'print_lightbox_markup' )
		);
	}

	/**
	 * Enqueue lightbox assets on WooCommerce order edit screens.
	 *
	 * @param string $hook_suffix
	 * @return void
	 */
	public function enqueue_admin_assets( $hook_suffix ) {
		if ( ! $this->is_order_screen() ) {
			return;
		}

		$ver = defined( 'WCPDU_VERSION' ) ? WCPDU_VERSION : '1.0.0';

		wp_enqueue_style(
			'wcpdu-lightbox',
			WCPDU_PLUGIN_URL . 'assets/css/wcpdu-lightbox.css',
			array(),
			$ver
		);

		wp_enqueue_script(
			'wcpdu-lightbox',
			WCPDU_PLUGIN_URL . 'assets/js/wcpdu-lightbox.js',
			array( 'jquery' ),
			$ver,
			true
		);
	}

	/**
	 * Print frontend-like lightbox markup in admin footer (once).
	 *
	 * @return void
	 */
	public function print_lightbox_markup() {
		if ( ! $this->is_order_screen() ) {
			return;
		}

		if ( $this->printed_lightbox_markup ) {
			return;
		}

		$this->printed_lightbox_markup = true;
		?>
		<div id="wcpdu-lightbox" style="display:none;">
			<div class="wcpdu-lightbox-overlay"></div>
			<div class="wcpdu-lightbox-content">
				<img src="" alt="">
				<span class="wcpdu-lightbox-close">&times;</span>
			</div>
		</div>
		<?php
	}

	/**
	 * Render uploaded design files for a single order item (admin).
	 *
	 * Adds data-wcpdu-lightbox attribute so existing JS can bind as on frontend.
	 *
	 * @param int             $item_id
	 * @param WC_Order_Item   $item
	 * @param WC_Product|bool $product
	 * @return void
	 */
	public function render_item_design_files( $item_id, $item, $product ) {

		if ( ! $this->is_order_screen() ) {
			return;
		}

		if ( ! $item instanceof WC_Order_Item ) {
			return;
		}

		$files = $item->get_meta( $this->meta_key );

		if ( empty( $files ) || ! is_array( $files ) ) {
			return;
		}

		echo '<div class="wcpdu-order-item-files" style="margin-top:8px;">';
		echo '<strong>' . esc_html__( 'Design Files', 'product-design-upload-for-ecommerce' ) . '</strong>';
		echo '<ul style="margin:8px 0 0;display:flex;gap:12px;">';

		foreach ( $files as $file ) {

			$file_url  = isset( $file['url'] ) ? (string) $file['url'] : '';
			$file_name = isset( $file['name'] ) ? (string) $file['name'] : '';

			if ( '' === $file_url ) {
				continue;
			}

			$label_raw = $file_name ? $file_name : __( 'View file', 'product-design-upload-for-ecommerce' );

			echo '<li style="margin:0 0 12px;display:flex;flex-direction:column;">';

			if ( $this->is_image( $file_url ) ) {
				echo '<a href="' . esc_url( $file_url ) . '" data-wcpdu-lightbox="1">';
				echo '<img src="' . esc_url( $file_url ) . '" style="max-width:150px;display:block;margin:6px 0;border:1px solid #ddd;padding:3px;background:#fff;" alt="' . esc_attr( wp_strip_all_tags( $file_name ) ) . '" />';
				echo '</a>';
			}

			echo '<a href="' . esc_url( $file_url ) . '" download>';
			echo esc_html( $label_raw );
			echo '</a>';

			echo '</li>';
		}

		echo '</ul>';
		echo '</div>';
	}

	/**
	 * Detect image by URL extension.
	 *
	 * @param string $url
	 * @return bool
	 */
	private function is_image( $url ) {

		if ( empty( $url ) ) {
			return false;
		}

		$path = wp_parse_url( $url, PHP_URL_PATH );
		$ext  = strtolower( pathinfo( (string) $path, PATHINFO_EXTENSION ) );

		return in_array(
			$ext,
			array( 'jpg', 'jpeg', 'png', 'gif', 'webp' ),
			true
		);
	}

	/**
	 * Check if current admin screen is WooCommerce order edit/view screen.
	 *
	 * @return bool
	 */
	private function is_order_screen() {
		if ( ! is_admin() || ! function_exists( 'get_current_screen' ) ) {
			return false;
		}

		$screen = get_current_screen();
		if ( ! $screen ) {
			return false;
		}

		return ( 'shop_order' === $screen->id || 'woocommerce_page_wc-orders' === $screen->id );
	}
}
