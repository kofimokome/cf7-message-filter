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
 * Version: 1.4.0
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
require KMCF7MS_CORE_DIR . '/Migration.php';
require KMCF7MS_CORE_DIR . '/Model.php';

if ( ! function_exists( 'kmcf7_message_filter\\kmcfmf_fs' ) ) {
	// Create a helper function for easy SDK access.
	function kmcfmf_fs() {
		global $kmcfmf_fs;

		if ( ! isset( $kmcfmf_fs ) ) {
			// Include Freemius SDK.
			require_once dirname( __FILE__ ) . '/freemius/start.php';

			$kmcfmf_fs = fs_dynamic_init( array(
				'id'                  => '11062',
				'slug'                => 'cf7-message-filter',
				'type'                => 'plugin',
				'public_key'          => 'pk_699cdf1dd29834038369b6605acb5',
				'is_premium'          => true,
				'premium_suffix'      => 'Pro',
				// If your plugin is a serviceware, set this option to false.
				'has_premium_version' => true,
				'has_addons'          => false,
				'has_paid_plans'      => true,
				'trial'               => array(
					'days'               => 7,
					'is_require_payment' => false,
				),
				'menu'                => array(
					'slug' => 'kmcf7-message-filter',
				),
				// Set the SDK to work in a sandbox mode (for development & testing).
				// IMPORTANT: MAKE SURE TO REMOVE SECRET KEY BEFORE DEPLOYMENT.
				'secret_key'          => 'sk_QeWS=jV8~);E:I:pdEO$F!>r6k7ys',
			) );
		}

		return $kmcfmf_fs;
	}

	// Init Freemius.
	kmcfmf_fs();
	// Signal that SDK was initiated.
	do_action( 'kmcfmf_fs_loaded' );

	function kmcfmf_fs_settings_url() {
		return admin_url( 'admin.php?page=kmcf7-message-filter' );
	}

	kmcfmf_fs()->add_filter( 'connect_url', 'kmcf7_message_filter\\kmcfmf_fs_settings_url' );
	kmcfmf_fs()->add_filter( 'after_skip_url', 'kmcf7_message_filter\\kmcfmf_fs_settings_url' );
	kmcfmf_fs()->add_filter( 'after_connect_url', 'kmcf7_message_filter\\kmcfmf_fs_settings_url' );
	kmcfmf_fs()->add_filter( 'after_pending_connect_url', 'kmcf7_message_filter\\kmcfmf_fs_settings_url' );
}

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

function KMCF7Start() {
//	if ( get_option( 'is_initial_db_migration_run', false ) ) {
//		Migration::runMigrations();
//		update_option( 'is_initial_db_migration_run', 1 );
//	} else {
//		Migration::runUpdateMigrations();
//	}
	$message_filter = new CF7MessageFilter();
	$message_filter->run();
}


if ( ! KMCF7Loader() ) {
	KMCF7Start();
}


// remove options upon deactivation

register_deactivation_hook( __FILE__, 'kmcf7_message_filter\\KMCF7Deactivation' );

function KMCF7Deactivation() {
	// set options to remove here
}


register_uninstall_hook( __FILE__, 'kmcf7_message_filter\\KMCF7Uninstall' );

/**
 * Set of actions to be performed on uninstallation
 * @since v1.3.6
 */
function KMCF7Uninstall() {
	Migration::dropAll();
}

register_activation_hook( __FILE__, 'kmcf7_message_filter\\KMCF7Activation' );

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