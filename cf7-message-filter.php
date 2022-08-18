<?php
/**
 * @link              www.kofimokome.stream
 * @since             1.0.0
 * @package           kmcf7_message_filter
 *
 * @wordpress-plugin
 * Plugin Name: Message Filter for Contact Form 7
 * Plugin URI: https://github.com/kofimokome/cf7-message-filter
 * Description: Filters messages submitted from contact form seven if it has words or email marked as spam by the user
 * Version: 1.3.5
 * Author: Kofi Mokome
 * Author URI: www.kofimokome.stream
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: cf7-message-filter
 * Domain Path: /languages
 */

namespace kmcf7_message_filter;

defined( 'ABSPATH' ) or die( 'Giving To Cesar What Belongs To Caesar' );

require 'constants.php';
require KMCF7MS_CORE_DIR . '/CF7MessageFilter.php';
require KMCF7MS_CORE_DIR . '/Module.php';

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

add_action( 'admin_notices', 'kmcf7_message_filter\\KMCF7ErrorNotice', 10, 1 );

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

function kmcf7_start() {
	$message_filter = new CF7MessageFilter();
	$message_filter->run();
}


if ( ! KMCF7Loader() ) {
	kmcf7_start();
}


// remove options upon deactivation

register_deactivation_hook( __FILE__, 'kmcf7_message_filter\\kmcf7_deactivation' );

function kmcf7_deactivation() {
	// set options to remove here
}

// todo: for future use
load_plugin_textdomain( KMCF7MS_TEXT_DOMAIN, false, basename( dirname( __FILE__ ) ) . '/languages' );