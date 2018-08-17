<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              www.kofimokome.ml
 * @since             1.0.0
 * @package           Contact_form_message_filter
 *
 * @wordpress-plugin
 * Plugin Name:       Contact Form Message Filter
 * Plugin URI:        zingersystems.com
 * Description:       Filters messages submitted from contact form seven if it has words or email marked as spam by the user
 * Version:           1.1.0
 * Author:            Kofi Mokome
 * Author URI:        www.kofimokome.ml
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       contact_form_message_filter
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PLUGIN_NAME_VERSION', '1.1.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-contact_form_message_filter-activator.php
 */
function activate_contact_form_message_filter() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-contact_form_message_filter-activator.php';
	Contact_form_message_filter_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-contact_form_message_filter-deactivator.php
 */
function deactivate_contact_form_message_filter() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-contact_form_message_filter-deactivator.php';
	Contact_form_message_filter_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_contact_form_message_filter' );
register_deactivation_hook( __FILE__, 'deactivate_contact_form_message_filter' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-contact_form_message_filter.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_contact_form_message_filter() {

	$plugin = new Contact_form_message_filter();
	$plugin->run();

}
run_contact_form_message_filter();
