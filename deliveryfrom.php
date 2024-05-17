<?php

/**
 * @wordpress-plugin
 * Plugin Name:       DeliveryFrom
 * Plugin URI:        https://deliveryfrom.shop
 * Description:       Print labels for multiple delivery services
 * Version:           1.3.0
 * Author:            Lumen Spei
 * Author URI:        https://lumenspei.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       deliveryfrom
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 */
define( 'DELIVERYFROM_VERSION', '1.3.0' );
define( 'DELIVERYFROM_DIR', plugin_dir_path( __FILE__ ) );
define( 'DELIVERYFROM_URI', plugin_dir_url(__FILE__) );


/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-deliveryfrom-activator.php
 */
function activate_deliveryfrom() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-deliveryfrom-activator.php';
	Deliveryfrom_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-deliveryfrom-deactivator.php
 */
function deactivate_deliveryfrom() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-deliveryfrom-deactivator.php';
	Deliveryfrom_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_deliveryfrom' );
register_deactivation_hook( __FILE__, 'deactivate_deliveryfrom' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-deliveryfrom.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_deliveryfrom() {

	$plugin = new Deliveryfrom();
	$plugin->run();

}
run_deliveryfrom();