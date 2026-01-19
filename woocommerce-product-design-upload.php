<?php
/**
 * Plugin Name: WooCommerce Product Design Upload
 * Plugin URI: https://wpdu.danhthong.com
 * Description: Allow customers to upload their own design files when purchasing WooCommerce products.
 * Version: 1.0.0
 * Author: Thong Dang
 * Author URI: https://danhthong.com
 * Text Domain: wcpdu
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
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
 * CHECK WOOCOMMERCE
 * ------------------------------------------------------------------------
 */
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {
	return;
}

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
 * ACTIVATE / DEACTIVATE (MUST BE HERE)
 * ------------------------------------------------------------------------
 */
register_activation_hook(
	__FILE__,
	array( 'WCPDU_Activator', 'activate' )
);

register_deactivation_hook(
	__FILE__,
	array( 'WCPDU_Deactivator', 'deactivate' )
);

/**
 * ------------------------------------------------------------------------
 * RUN PLUGIN
 * ------------------------------------------------------------------------
 */
function run_wcpdu() {
	new WCPDU_Loader();
}

run_wcpdu();
