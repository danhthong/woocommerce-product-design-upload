<?php
/**
 * Admin settings functionality
 *
 * @package WooCommerce_Product_Design_Upload
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WCPDU_Admin_Settings {

	/**
	 * Option name.
	 *
	 * @var string
	 */
	private $option_name = 'wcpdu_settings';

	/**
	 * Constructor.
	 */
	public function __construct() {

		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'wcpdu_render_settings_page', [ $this, 'render_settings' ] );
	}

	/**
	 * Register settings using Settings API.
	 *
	 * @return void
	 */
	public function register_settings() {

		register_setting(
			'wcpdu_settings_group',
			$this->option_name,
			[ $this, 'sanitize_settings' ]
		);

		add_settings_section(
			'wcpdu_general_section',
			__( 'General Settings', 'ro-print-design-upload' ),
			'__return_false',
			'wcpdu-settings'
		);

		add_settings_field(
			'enable_upload',
			__( 'Enable design upload', 'ro-print-design-upload' ),
			[ $this, 'render_enable_upload_field' ],
			'wcpdu-settings',
			'wcpdu_general_section'
		);

		add_settings_field(
			'max_file_size',
			__( 'Maximum file size (MB)', 'ro-print-design-upload' ),
			[ $this, 'render_max_file_size_field' ],
			'wcpdu-settings',
			'wcpdu_general_section'
		);

		add_settings_field(
			'allowed_file_types',
			__( 'Allowed file types', 'ro-print-design-upload' ),
			[ $this, 'render_allowed_file_types_field' ],
			'wcpdu-settings',
			'wcpdu_general_section'
		);
	}

	/**
	 * Sanitize settings before saving.
	 *
	 * @param array $input
	 * @return array
	 */
	public function sanitize_settings( $input ) {

		$output = [];

		$output['enable_upload'] = isset( $input['enable_upload'] ) ? 1 : 0;

		$output['max_file_size'] = isset( $input['max_file_size'] )
			? absint( $input['max_file_size'] )
			: 10;

		if ( isset( $input['allowed_file_types'] ) ) {
			$types = explode( ',', $input['allowed_file_types'] );
			$types = array_map( 'trim', $types );
			$types = array_map( 'sanitize_text_field', $types );

			$output['allowed_file_types'] = implode( ',', $types );
		} else {
			$output['allowed_file_types'] = 'jpg,png,pdf';
		}

		return $output;
	}

	/**
	 * Render settings page content.
	 *
	 * @return void
	 */
	public function render_settings() {

		$settings = get_option( $this->option_name, [] );
		?>

		<form method="post" action="options.php">
			<?php
			settings_fields( 'wcpdu_settings_group' );
			do_settings_sections( 'wcpdu-settings' );
			submit_button();
			?>
		</form>

		<?php
	}

	/**
	 * Render enable upload checkbox.
	 *
	 * @return void
	 */
	public function render_enable_upload_field() {

		$options = get_option( $this->option_name, [] );
		?>

		<label>
			<input type="checkbox"
			       name="<?php echo esc_attr( $this->option_name ); ?>[enable_upload]"
			       value="1"
			       <?php checked( 1, $options['enable_upload'] ?? 0 ); ?>>
			<?php esc_html_e( 'Allow customers to upload design files', 'ro-print-design-upload' ); ?>
		</label>

		<?php
	}

	/**
	 * Render max file size field.
	 *
	 * @return void
	 */
	public function render_max_file_size_field() {

		$options = get_option( $this->option_name, [] );
		$value   = $options['max_file_size'] ?? 10;
		?>

		<input type="number"
		       min="1"
		       name="<?php echo esc_attr( $this->option_name ); ?>[max_file_size]"
		       value="<?php echo esc_attr( $value ); ?>" />

		<p class="description">
			<?php esc_html_e( 'Maximum upload file size in megabytes.', 'ro-print-design-upload' ); ?>
		</p>

		<?php
	}

	/**
	 * Render allowed file types field.
	 *
	 * @return void
	 */
	public function render_allowed_file_types_field() {

		$options = get_option( $this->option_name, [] );
		$value   = $options['allowed_file_types'] ?? 'jpg,png,pdf';
		?>

		<input type="text"
		       class="regular-text"
		       name="<?php echo esc_attr( $this->option_name ); ?>[allowed_file_types]"
		       value="<?php echo esc_attr( $value ); ?>" />

		<p class="description">
			<?php esc_html_e( 'Comma-separated list (e.g. jpg,png,pdf,ai).', 'ro-print-design-upload' ); ?>
		</p>

		<?php
	}
}
