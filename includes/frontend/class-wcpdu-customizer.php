<?php
/**
 * Frontend customizer UI (modal) for product design.
 *
 * @package WooCommerce_Product_Design_Upload
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WCPDU_Customizer {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'woocommerce_before_add_to_cart_button', [ $this, 'render_customizer_button_and_modal' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
	}

	/**
	 * Enqueue assets for modal + behavior.
	 *
	 * @return void
	 */
	public function enqueue_assets() {
		if ( ! is_product() ) {
			return;
		}

		$ver = defined( 'WCPDU_VERSION' ) ? WCPDU_VERSION : '1.0.0';

		wp_enqueue_style(
			'wcpdu-customizer-modal',
			WCPDU_PLUGIN_URL . 'assets/css/wcpdu-customizer-modal.css',
			[],
			$ver
		);

		wp_enqueue_script(
			'wcpdu-customizer-modal',
			WCPDU_PLUGIN_URL . 'assets/js/wcpdu-customizer-modal.js',
			[ 'jquery' ],
			$ver,
			true
		);
	}

	/**
	 * Render the customize button and modal UI.
	 *
	 * @return void
	 */
	public function render_customizer_button_and_modal() {
		global $product;

		if ( ! $product ) {
			return;
		}

		$enabled = $product->get_meta( '_wcpdu_enable_upload' );
		if ( 'yes' !== $enabled ) {
			return;
		}
		?>
		<div class="wcpdu-entry">
			<button type="button" class="button wp-element-button wcpdu-open-customizer primary">
				<?php echo esc_html__( 'Customize', 'wcpdu' ); ?>
			</button>
		</div>


		<div id="wcpdu-customizer-modal" class="wcpdu-modal" aria-hidden="true" style="display:none;">
			<div class="wcpdu-modal-overlay" data-wcpdu-modal-close="1"></div>

			<div class="wcpdu-modal-dialog" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr__( 'Product customizer', 'wcpdu' ); ?>">
				<div class="wcpdu-modal-header">
					<h3 class="wcpdu-modal-title"><?php echo esc_html__( 'Customize Your Product', 'wcpdu' ); ?></h3>
					<button type="button" class="wcpdu-modal-close" data-wcpdu-modal-close="1" aria-label="<?php echo esc_attr__( 'Close', 'wcpdu' ); ?>">×</button>
				</div>

				<div class="wcpdu-modal-body wcpdu-modal-grid">
					<div class="wcpdu-modal-col wcpdu-modal-col-left">
						<div class="wcpdu-canvas-wrapper">
							<canvas id="wcpdu-canvas" width="350" height="350"></canvas>
						</div>
					</div>

					<div class="wcpdu-modal-col wcpdu-modal-col-right">
						<div class="wcpdu-form-block">
							<label for="wcpdu-upload-image">
								<?php echo esc_html__( 'Upload Image/Design File', 'wcpdu' ); ?><span>*</span>
							</label>

							<label for="wcpdu-upload-image" class="custom-file-upload">
								<svg width="46" height="46" viewBox="0 0 46 46" fill="none" xmlns="http://www.w3.org/2000/svg">
									<rect x="3" y="3" width="40" height="40" rx="20" fill="#F2F4F7"></rect>
									<rect x="3" y="3" width="40" height="40" rx="20" stroke="#F9FAFB" stroke-width="6"></rect>
									<path d="M19.667 26.3333L23.0003 23M23.0003 23L26.3337 26.3333M23.0003 23V30.5M29.667 26.9524C30.6849 26.1117 31.3337 24.8399 31.3337 23.4167C31.3337 20.8854 29.2816 18.8333 26.7503 18.8333C26.5682 18.8333 26.3979 18.7383 26.3054 18.5814C25.2187 16.7374 23.2124 15.5 20.917 15.5C17.4652 15.5 14.667 18.2982 14.667 21.75C14.667 23.4718 15.3632 25.0309 16.4894 26.1613" stroke="#475467" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"></path>
								</svg>

								<p id="file-name">
									<span><?php echo esc_html__( 'Click to upload', 'wcpdu' ); ?></span>
									<?php echo esc_html__( ' or drag and drop', 'wcpdu' ); ?>
									<br>
									<?php echo esc_html__( 'SVG, PNG, JPG (max. 800x400px)', 'wcpdu' ); ?>
								</p>
							</label>

							<input
								id="wcpdu-upload-image"
								type="file"
								name="wcpdu_upload_image"
								accept=".svg,.png,.jpg,.jpeg"
							/>
						</div>

						<div class="wcpdu-toolbar">
							<button type="button" class="wcpdu-btn wcpdu-remove-object" aria-label="<?php echo esc_attr__( 'Remove selected object', 'wcpdu' ); ?>">
								✕ <?php echo esc_html__( 'Clear image', 'wcpdu' ); ?>
							</button>
						</div>

						<p class="wcpdu-tooltip">
							<?php
							echo wp_kses_post(
								__( 'Tip: Click the image on the left to select it. Drag to move it, use the corner handles to resize, and drag while holding <strong>Shift</strong> to keep proportions.', 'wcpdu' )
							);
							?>
						</p>


						<input type="hidden" id="wcpdu-custom-design" name="wcpdu_custom_design" value="">
					</div>
				</div>

				<div class="wcpdu-modal-footer">
					<button type="button" class="button wp-element-button wcpdu-cancel" data-wcpdu-modal-close="1">
						<?php echo esc_html__( 'Cancel', 'wcpdu' ); ?>
					</button>
					<button type="button" class="button wp-element-button button-primary wcpdu-apply">
						<?php echo esc_html__( 'Apply', 'wcpdu' ); ?>
					</button>
				</div>
			</div>
		</div>
		<?php
	}
}
