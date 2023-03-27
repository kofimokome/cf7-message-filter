<?php
/**
 * @link              www.kofimokome.stream
 * @since             1.0.0
 * @package           km_message_filter
 *
 * @wordpress-plugin
 * Plugin Name: Message Filter for Contact Form 7
 * Plugin URI: https://github.com/kofimokome/cf7-message-filter
 * Description: Filters messages submitted from contact form seven if it has words or email marked as spam by the user
 * Version: 1.4.5
 * Author: Kofi Mokome
 * Author URI: www.kofimokome.stream
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: cf7-message-filter
 * Domain Path: /languages
 */

namespace km_message_filter;

defined( 'ABSPATH' ) or die( 'Giving To Cesar What Belongs To Caesar' );

require 'constants.php';
require KMCF7MS_CORE_DIR . '/KMCFMessageFilter.php';
require KMCF7MS_CORE_DIR . '/Module.php';
require KMCF7MS_CORE_DIR . '/Migration.php';
require KMCF7MS_CORE_DIR . '/Model.php';
require KMCF7MS_CORE_DIR . '/Validator.php';
require KMCF7MS_CORE_DIR . '/Filter.php';

/**
 * Scan directories for files to include
 */
foreach ( scandir( __DIR__ ) as $dir ) {
	if ( strpos( $dir, '.' ) === false && is_dir( __DIR__ . '/' . $dir ) && is_file( __DIR__ . '/' . $dir . '/includes.php' ) ) {
		require __DIR__ . '/' . $dir . '/includes.php';
	}
}

function KMCF7ErrorNotice( $message = '' ) {
	if ( trim( $message ) != '' ):
		?>
        <div class="error notice is-dismissible">
            <p><b>CF7 Message Filter: </b><?php echo $message ?></p>
        </div>
	<?php
	endif;
}

add_action( 'admin_notices', 'km_message_filter\\KMCF7ErrorNotice', 10, 1 );

// loads classes / files
function KMCF7Loader() {
	$error = false;

	$includes = apply_filters( 'kmcf7_includes_filter', [] );

	foreach ( $includes as $file ) {
		if ( ! $filepath = file_exists( $file ) ) {
			KMCF7ErrorNotice( sprintf( __( 'Error locating <b>%s</b> for inclusion', KMCF7MS_TEXT_DOMAIN ), $file ) );
			$error = true;
		} else {
			include_once $file;
		}
	}

	return $error;
}

function KMCF7Start() {
	if ( get_option( 'is_initial_db_migration_run', 'not_set' ) == 'not_set' ) {
		Migration::runMigrations();
		update_option( 'is_initial_db_migration_run', 1 );
	} else {
		Migration::runUpdateMigrations();
	}
	$message_filter = new KMCFMessageFilter();
	$message_filter->run();
}


if ( ! KMCF7Loader() ) {
	KMCF7Start();
}


// remove options upon deactivation

register_deactivation_hook( __FILE__, 'km_message_filter\\KMCF7Deactivation' );

function KMCF7Deactivation() {
	// set options to remove here
}


register_uninstall_hook( __FILE__, 'km_message_filter\\KMCF7Uninstall' );

/**
 * Set of actions to be performed on uninstallation
 * @since v1.3.6
 */
function KMCF7Uninstall() {
	Migration::dropAll();
}

register_activation_hook( __FILE__, 'km_message_filter\\KMCF7Activation' );

/**
 * Set of actions to be performed on activation
 * @since v1.3.6
 */
function KMCF7Activation() {
	Migration::runMigrations();
	update_option( 'is_initial_db_migration_run', 1 );
}

// todo: for future use
load_plugin_textdomain( KMCF7MS_TEXT_DOMAIN, false, basename( dirname( __FILE__ ) ) . '/languages' );