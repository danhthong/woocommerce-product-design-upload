<?php
/**
 * Plugin Name: RO Print Design Upload
 * Plugin URI: https://wpdu.danhthong.com
 * Description: Allow customers to upload design files when purchasing products.
 * Version: 1.0.0
 * Author: Thong Dang
 * Author URI: https://danhthong.com
 * Text Domain: ro-print-design-upload
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ------------------------------------------------------------------------
 * CONSTANTS
 * ------------------------------------------------------------------------
 */
define( 'WCPDU_VERSION', '1.0.0' );
define( 'WCPDU_PLUGIN_FILE', __FILE__ );
define( 'WCPDU_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WCPDU_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * ------------------------------------------------------------------------
 * LOAD CORE FILES
 * ------------------------------------------------------------------------
 */
require_once WCPDU_PLUGIN_DIR . 'includes/class-wcpdu-loader.php';
require_once WCPDU_PLUGIN_DIR . 'includes/class-wcpdu-activator.php';
require_once WCPDU_PLUGIN_DIR . 'includes/class-wcpdu-deactivator.php';

/**
 * ------------------------------------------------------------------------
 * ACTIVATE / DEACTIVATE
 * ------------------------------------------------------------------------
 */
register_activation_hook(
	__FILE__,
	[ 'WCPDU_Activator', 'activate' ]
);

register_deactivation_hook(
	__FILE__,
	[ 'WCPDU_Deactivator', 'deactivate' ]
);

/**
 * ------------------------------------------------------------------------
 * BOOTSTRAP
 * ------------------------------------------------------------------------
 */
add_action(
	'plugins_loaded',
	static function () {

		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		new WCPDU_Loader();
	},
	20
);
