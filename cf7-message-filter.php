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
 * Version: 1.2.5.3
 * Author: Kofi Mokome
 * Author URI: www.kofimokome.stream
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: cf7-message-filter
 * Domain Path: /languages
 */

namespace kmcf7_message_filter;

defined('ABSPATH') or die('Giving To Cesar What Belongs To Caesar');

$error = false;

function kmcf7_error_notice($message = '')
{
    if (trim($message) != ''):
        ?>
        <div class="error notice is-dismissible">
            <p><b>CF7 Message Filter: </b><?php echo $message ?></p>
        </div>
    <?php
    endif;
}

add_action('admin_notices', 'kmcf7_message_filter\\kmcf7_error_notice', 10, 1);

// loads classes / files
function kmcf7_loader()
{
    global $error;
    $classes = array(
        'CF7MessageFilter.php', //
        'MenuPage.php', //
        'SubMenuPage.php', //
        'Setting.php', //
        'BlockedMessage.php', //
        // 'admin_menu.php', //

    );

    foreach ($classes as $file) {
        if (!$filepath = file_exists(plugin_dir_path(__FILE__) . "includes/" . $file)) {
            kmcf7_error_notice(sprintf(__('Error locating <b>%s</b> for inclusion', 'kmgt'), $file));
            $error = true;
        } else {
            include_once plugin_dir_path(__FILE__) . "includes/" . $file;
        }
    }
}

function kmcf7_start()
{
    $message_filter = new CF7MessageFilter();
    $message_filter->run();
}


kmcf7_loader();
if (!$error) {
    kmcf7_start();
}


// remove options upon deactivation

register_deactivation_hook(__FILE__, 'kmcf7_deactivation');

function kmcf7_deactivation()
{
    // set options to remove here
}

// todo: for future use
load_plugin_textdomain('cf7-message-filter', false, basename(dirname(__FILE__)) . '/languages');