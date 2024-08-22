<?php

/**
 * @link              www.kofimokome.stream
 * @since             1.0.0
 * @package           km_message_filter
 *
 * @wordpress-plugin
 * Plugin Name: Message Filter for Contact Form 7
 * Plugin URI: https://github.com/kofimokome/cf7-message-filter
 * Description: Filters messages submitted from contact form 7 if it has words or email marked as spam by the user
 * Version: 1.6.2.1
 * Author: Kofi Mokome
 * Author URI: www.kofimokome.stream
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: cf7-message-filter
 * Domain Path: /languages
 */
// TODO: PLUGIN BIRTHDAY IS ON THE 30TH AUGUST 2018
namespace km_message_filter;

use KMEnv;
use WordPressTools;
defined( 'ABSPATH' ) or die( 'Giving To Cesar What Belongs To Caesar' );
if ( function_exists( 'kmcf7ms_fs' ) ) {
    kmcf7ms_fs()->set_basename( false, __FILE__ );
} else {
    if ( !function_exists( 'kmcf7ms_fs' ) ) {
        // Create a helper function for easy SDK access.
        function kmcf7ms_fs() {
            global $kmcf7ms_fs;
            if ( !isset( $kmcf7ms_fs ) ) {
                // Activate multisite network integration.
                if ( !defined( 'WP_FS__PRODUCT_11062_MULTISITE' ) ) {
                    define( 'WP_FS__PRODUCT_11062_MULTISITE', true );
                }
                // Include Freemius SDK.
                require_once dirname( __FILE__ ) . '/freemius/start.php';
                $kmcf7ms_fs = fs_dynamic_init( array(
                    'id'             => '11062',
                    'slug'           => 'cf7-message-filter',
                    'type'           => 'plugin',
                    'public_key'     => 'pk_699cdf1dd29834038369b6605acb5',
                    'is_premium'     => false,
                    'premium_suffix' => 'Pro',
                    'has_addons'     => false,
                    'has_paid_plans' => true,
                    'menu'           => array(
                        'slug' => 'kmcf7-message-filter',
                    ),
                    'is_live'        => true,
                ) );
            }
            return $kmcf7ms_fs;
        }

        // Init Freemius.
        $instance = kmcf7ms_fs();
        // Signal that SDK was initiated.
        do_action( 'kmcf7ms_fs_loaded' );
    }
    require 'constants.php';
    require KMCF7MS_CORE_DIR . '/KMCFMessageFilter.php';
    require KMCF7MS_CORE_DIR . '/Module.php';
    require KMCF7MS_CORE_DIR . '/Filter.php';
    kmcf7ms_fs()->add_action( 'after_uninstall', 'km_message_filter\\KMCF7Uninstall' );
    function add_collectspam_permission(  $permissions  ) {
        $permissions['collectspam'] = array(
            'icon-class' => 'dashicons dashicons-cloud-upload',
            'label'      => kmcf7ms_fs()->get_text_inline( 'Collect Spam Info', 'collectspam' ),
            'desc'       => kmcf7ms_fs()->get_text_inline( "Collect spam words, spam emails and messages blocked", 'permissions-collectspam' ),
            'priority'   => 16,
            'tooltip'    => kmcf7ms_fs()->get_text_inline( "We would like to collect only the spam words and emails saved in the plugin settings and only the messages blocked by this plugin on a regular basis. This is solely for the purpose of improving the plugin. You can disable this feature in the plugin settings page", 'permissions-collectspam' ),
        );
        return $permissions;
    }

    //	kmcf7ms_fs()->add_filter( 'permission_list', 'km_message_filter\\add_collectspam_permission' );
    /**
     * Scan directories for files to include
     */
    foreach ( scandir( __DIR__ ) as $dir ) {
        if ( strpos( $dir, '.' ) === false && is_dir( __DIR__ . '/' . $dir ) && is_file( __DIR__ . '/' . $dir . '/includes.php' ) ) {
            require __DIR__ . '/' . $dir . '/includes.php';
        }
    }
    function KMCF7ErrorNotice(  $message = ''  ) {
        if ( trim( $message ) != '' ) {
            ?>
            <div class="error notice is-dismissible">
                <p><b>CF7 Message Filter: </b><?php 
            echo $message;
            ?></p>
            </div>
		<?php 
        }
    }

    add_action(
        'admin_notices',
        'km_message_filter\\KMCF7ErrorNotice',
        10,
        1
    );
    // loads classes / files
    function KMCF7Loader() : bool {
        $error = false;
        // scan directories for requires.php files
        foreach ( scandir( __DIR__ ) as $dir ) {
            if ( strpos( $dir, '.' ) === false && is_dir( __DIR__ . '/' . $dir ) && is_file( __DIR__ . '/' . $dir . '/requires.php' ) ) {
                require_once __DIR__ . '/' . $dir . '/requires.php';
            }
        }
        $requires = apply_filters( 'kmcf7_requires_filter', [] );
        foreach ( $requires as $file ) {
            if ( !($filepath = file_exists( $file )) ) {
                KMCF7ErrorNotice( sprintf( __( 'Error locating <b>%s</b> for inclusion', KMCF7MS_TEXT_DOMAIN ), $file ) );
                $error = true;
            } else {
                require_once $file;
            }
        }
        // scan directories for includes.php files
        foreach ( scandir( __DIR__ ) as $dir ) {
            if ( strpos( $dir, '.' ) === false && is_dir( __DIR__ . '/' . $dir ) && is_file( __DIR__ . '/' . $dir . '/includes.php' ) ) {
                require_once __DIR__ . '/' . $dir . '/includes.php';
            }
        }
        $includes = apply_filters( 'kmcf7_includes_filter', [] );
        foreach ( $includes as $file ) {
            if ( !($filepath = file_exists( $file )) ) {
                KMCF7ErrorNotice( sprintf( __( 'Error locating <b>%s</b> for inclusion', KMCF7MS_TEXT_DOMAIN ), $file ) );
                $error = true;
            } else {
                include_once $file;
            }
        }
        return $error;
    }

    function KMCF7Start() {
        if ( get_option( 'is_initial_db_migration_run', 'not_set' ) == 1 ) {
            delete_option( 'is_initial_db_migration_run' );
        }
        if ( get_option( 'kmcfmf_weekly_stats', 'not_set' ) != 'not_set' ) {
            delete_option( 'kmcfmf_weekly_stats' );
            delete_option( 'kmcfmf_weekend' );
        }
        $wordpress_tools = new WordPressTools(__FILE__);
        $wordpress_tools->migration_manager->runMigrations();
        $message_filter = new KMCFMessageFilter();
        $message_filter->run();
    }

    if ( !KMCF7Loader() ) {
        KMCF7Start();
    }
    // remove options upon deactivation
    register_deactivation_hook( __FILE__, 'km_message_filter\\KMCF7Deactivation' );
    function KMCF7Deactivation() {
        // set options to remove here
    }

    //register_uninstall_hook( __FILE__, 'km_message_filter\\KMCF7Uninstall' );
    /**
     * Set of actions to be performed on uninstallation
     * @since v1.3.6
     */
    function KMCF7Uninstall() {
        global $wpdb;
        if ( get_option( 'kmcfmf_message_delete_data', 'off' ) == 'on' ) {
            $instance = WordPressTools::getInstance( __FILE__ );
            $instance->migration_manager->dropAll();
            //query the wp options table and delete all options that start with kmcfmf_
            $query = $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'kmcfmf_%'" );
            $wpdb->query( $query );
            // todo; drop migrations table
            $env = ( new KMEnv(__FILE__) )->getEnv();
            $table_name = $wpdb->prefix . trim( $env['TABLE_PREFIX'] ) . 'migrations';
            $query = $wpdb->prepare( "DROP TABLE IF EXISTS {$table_name}" );
            $wpdb->query( $query );
        }
    }

    register_activation_hook( __FILE__, 'km_message_filter\\KMCF7Activation' );
    /**
     * Set of actions to be performed on activation
     * @since v1.3.6
     */
    function KMCF7Activation() {
        // do some magic here
    }

    // todo: for future use
    load_plugin_textdomain( KMCF7MS_TEXT_DOMAIN, false, basename( dirname( __FILE__ ) ) . '/languages' );
}