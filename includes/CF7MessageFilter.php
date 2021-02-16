<?php
/**
 * Created by PhpStorm.
 * User: kofi
 * Date: 18/8/20
 * Time: 08:10 PM
 * Added by UnderWordPressure: [text <name> ...] filter.
 */

namespace kmcf7_message_filter;

use WPCF7_Submission;

class CF7MessageFilter
{

    private $temp_email;
    private $temp_message;
    private $count_updated = false;
    private $blocked;
    private static $log_file;
    private $version;

    public function __construct()
    {
        // our constructor
        $this->blocked = get_option("kmcfmf_messages_blocked_today");
        //  $this->error_notice("hi there");
        $this->version = '1.2.5.2';
    }

    /**
     * Creates a directory in wordpress upload folder if it does not exist
     * @since 1.2.5
     */
    private function init_upload_dir()
    {
        $logs_root = wp_upload_dir()['basedir'] . '/kmcf7mf_logs/';
        if (!is_dir($logs_root)) {
            mkdir($logs_root, 0700);
        }
        self::$log_file = $logs_root . 'messages.txt';
        if (!is_file(self::$log_file)) {
            file_put_contents(self::$log_file, '{}');
        }
    }

    public static function get_log_file_path()
    {
        return self::$log_file;
    }

    public function run()
    {
        $this->init_upload_dir();
        $this->add_actions();
        $this->add_options();
        $this->add_filters();
        $this->add_main_menu();
        $this->add_settings();
        $this->transfer_old_data();
        $this->clear_messages();
    }


    private function add_actions()
    {

        // add actions here
        add_action('admin_enqueue_scripts', array($this, 'add_scripts'));
        // add_action('wpcf7_submit', array($this, 'on_wpcf7_submit'),10, 2);


    }

    public function on_wpcf7_submit($contact_form, $result)
    {
        $logs_root = wp_upload_dir()['basedir'] . '/kmcf7mf_logs/';
        $submission = WPCF7_Submission::get_instance();
        file_put_contents($logs_root . 'test.txt', json_encode($submission->get_posted_data()));

    }

    public function error_notice($message = '')
    {
        if (trim($message) != ''):
            ?>
            <div class="error notice is-dismissible">
                <p><b>Contact Form Message Filter: </b><?php echo $message ?></p>
            </div>
        <?php
        endif;
    }

    public function add_scripts($hook)
    {
        global $wp;
        $url = add_query_arg(array($_GET), $wp->request);
        $url = substr($url, 0, 29);
        // echo "<script> alert('$url');</script>";
        //wp_enqueue_style( 'style-name', get_stylesheet_uri() );
        if ($hook == 'toplevel_page_kmcf7-message-filter' || $url == '?page=kmcf7-filtered-messages') {

            wp_enqueue_script('vendor', plugins_url('assets/js/vendor.min.js', dirname(__FILE__)), array('jquery'), '1.0.0', true);
            wp_enqueue_script('moment', plugins_url('assets/libs/moment/moment.min.js', dirname(__FILE__)), array('jquery'), '1.0.0', true);
            wp_enqueue_script('apex', plugins_url('assets/libs/apexcharts/apexcharts.min.js', dirname(__FILE__)), array('jquery'), '1.0.0', false);
            wp_enqueue_script('flat', plugins_url('assets/libs/flatpickr/flatpickr.min.js', dirname(__FILE__)), array('jquery'), '1.0.0', true);
            wp_enqueue_script('dash', plugins_url('assets/js/pages/dashboard.init.js', dirname(__FILE__)), array('jquery'), '1.0.0', true);
            wp_enqueue_script('app', plugins_url('assets/js/app.min.js', dirname(__FILE__)), array('jquery'), '1.0.0', true);


            wp_enqueue_style('bootstrap', plugins_url('/assets/css/bootstrap.min.css', dirname(__FILE__)), '', '4.3.1');
            wp_enqueue_style('app', plugins_url('/assets/css/app.min.css', dirname(__FILE__)), '', '4.3.1');
            wp_enqueue_style('icons', plugins_url('/assets/css/icons.min.css', dirname(__FILE__)), '', '4.3.1');
        }
    }

    private function add_main_menu()
    {
        // Create the menu page
        $menu_title = 'CF7 Form Filter';
        if ($this->blocked > 0) {
            $menu_title .= " <span class='update-plugins count-1'><span class='update-count'>$this->blocked </span></span>";
        }
        $menu_page = new MenuPage('CF7 Form Filter', $menu_title, 'read', 'kmcf7-message-filter', 'dashicons-filter', null, array($this, 'dashboard_view'));
        $messages_page = new SubMenuPage($menu_page->get_menu_slug(), 'Blocked Messages', 'Blocked Messages', 'manage_options', 'kmcf7-filtered-messages', array($this, 'messages_view'));
        $menu_page->add_sub_menu_page($messages_page);

        $settings_page = new SubMenuPage($menu_page->get_menu_slug(), 'Options', 'Options', 'manage_options', 'kmcf7-message-filter-options', array($this, 'settings_view'), true);
        $settings_page->add_tab('basic', 'Basic Settings', array($this, 'status_tab_view'), array('tab' => 'basic'));
        $settings_page->add_tab('advanced', 'Advanced Settings (experimental)', array($this, 'status_tab_view'), array('tab' => 'advanced'));
        $settings_page->add_tab('plugins', 'More Plugins', array($this, 'status_tab_view'), array('tab' => 'plugins'));
        $menu_page->add_sub_menu_page($settings_page);

        $menu_page->run();

    }

    /**
     * Clears saved blocked messages
     * @since 1.2.5.1
     */
    private function clear_messages()
    {
        $clear_messages = get_option('kmcfmf_message_auto_delete_toggle') == 'on' ? true : false;
        if ($clear_messages) {
            $last_cleared_date = get_option('kmcfmf_last_cleared_date');
            $frequency = get_option('kmcfmf_message_auto_delete_duration');
            $to_delete = get_option('kmcfmf_message_auto_delete_amount');
            if ($last_cleared_date != '0') {
                $now = strtotime(Date("d F Y"));
                $diff = $now - $last_cleared_date;
                $diff = round($diff / (60 * 60 * 24));
                if ($diff >= $frequency) {
                    // clear messages
                    $log_messages = (array)json_decode(file_get_contents(self::$log_file));
                    $log_messages = array_slice($log_messages, $to_delete);
                    $log_messages = json_encode((object)$log_messages);
                    file_put_contents(self::$log_file, $log_messages);
                    update_option('kmcfmf_last_cleared_date', $now);
                }
            }
        }
    }

    /**
     * Displays settings page
     * @since 1.2.5
     */
    public function status_tab_view($args)
    {
        switch ($args['tab']) {
            case 'basic':
                include "views/settings/basic.php";
                break;
            case 'plugins':
                include "views/settings/plugins.php";
                break;
            case 'advanced':
                include "views/settings/advanced.php";
                break;
            default:
                include "views/settings/basic.php";
                break;
        }
    }

    /**
     * Adds Settings
     * @since 1.2.5
     */
    private function add_settings()
    {

        $settings = new Setting('kmcf7-message-filter-options&tab=basic');
        $settings->add_section('kmcfmf_message_filter_basic');
        $settings->add_field(
            array(
                'type' => 'textarea',
                'id' => 'kmcfmf_restricted_words',
                'label' => 'Restricted Words: ',
                'tip' => 'type <code>[link]</code> to filter messages containing links, type <code>[russian]</code> to filter messages contains russian characters',
                'placeholder' => 'eg john, doe, baby, man, [link], [russian]'
            )
        );
        $settings->add_field(
            array(
                'type' => 'textarea',
                'id' => 'kmcfmf_restricted_emails',
                'label' => 'Restricted Emails: ',
                'tip' => 'Note: If you write john, we will check for ( john@gmail.com, john@yahoo.com, john@hotmail.com, etc... )',
                'placeholder' => 'eg john, john@doe.com, mary@doman.tk, man, earth'
            )
        );
        $settings->add_field(
            array(
                'type' => 'textarea',
                'id' => 'kmcfmf_tags_by_name',
                'label' => 'Analyze single line Text Fields with these names for restricted word, also: ',
                'tip' => 'Note: your-subject, your-address, your-lastname, etc.',
                'placeholder' => ''
            )
        );

        $settings->add_field(
            array(
                'type' => 'textarea',
                'id' => 'kmcfmf_spam_word_error',
                'label' => 'Error Message For Restricted Words: ',
                'tip' => '',
                'placeholder' => 'You have entered a word marked as spam'
            )
        );
        $settings->add_field(
            array(
                'type' => 'textarea',
                'id' => 'kmcfmf_spam_email_error',
                'label' => 'Error Message For Restricted Emails: ',
                'tip' => '',
                'placeholder' => 'The e-mail address entered is invalid.',
            )
        );

        $settings->add_field(
            array(
                'type' => 'checkbox',
                'id' => 'kmcfmf_message_filter_toggle',
                'label' => 'Enable Message Filter?: ',
                'tip' => ''
            )
        );

        $settings->add_field(
            array(
                'type' => 'checkbox',
                'id' => 'kmcfmf_email_filter_toggle',
                'label' => 'Enable Email Filter?: ',
                'tip' => ''
            )
        );

        $settings->add_field(
            array(
                'type' => 'checkbox',
                'id' => 'kmcfmf_tags_by_name_filter_toggle',
                'label' => 'Enable Filter on single line Text Fields by Name?: ',
                'tip' => ''
            )
        );

        $settings->add_field(
            array(
                'type' => 'checkbox',
                'id' => 'kmcfmf_message_filter_reset',
                'label' => 'Reset Filter Count?: ',
                'tip' => ''
            )
        );

        $settings->save();


        $settings = new Setting('kmcf7-message-filter-options&tab=advanced');
        $settings->add_section('kmcfmf_message_filter_advanced');
        $settings->add_field(
            array(
                'type' => 'checkbox',
                'id' => 'kmcfmf_message_auto_delete_toggle',
                'label' => 'Auto delete messages?: ',
                'tip' => ''
            )
        );
        $settings->add_field(
            array(
                'type' => 'number',
                'id' => 'kmcfmf_message_auto_delete_duration',
                'label' => 'Number of days: ',
                'tip' => '',
                'min' => 1,
                'max' => ''
            )
        );
        $settings->add_field(
            array(
                'type' => 'select',
                'id' => 'kmcfmf_message_auto_delete_duration',
                'label' => 'Number of days: ',
                'options' => array(
                    '1 Month' => '30',
                    '1 Day' => '1',
                    '3 Days' => '3',
                    '1 Week' => '7',
                    '2 Weeks' => '14',
                ),
                // 'default_option' => ''
            )
        );
        $settings->add_field(
            array(
                'type' => 'select',
                'id' => 'kmcfmf_message_auto_delete_amount',
                'label' => 'Number of messages to delete: ',
                'options' => array(
                    '10 Messages' => '10',
                    '20 Messages' => '20',
                    '40 Messages' => '40',
                    '80 Messages' => '80',
                ),
                // 'default_option' => ''
            )
        );
        $settings->save();
    }

    /**
     * Todo: Add Description
     * @since    1.0.0
     * @access   public
     */
    private function add_options()
    {

        //
        $reset_message_filter_counter = get_option('kmcfmf_message_filter_reset') == 'on' ? true : false;

        $option_names = array(
            'kmcfmf_messages_blocked',
            'kmcfmf_last_message_blocked',
            'kmcfmf_message_filter_reset',
            'kmcfmf_date_of_today',
            'kmcfmf_messages_blocked_today',
            'kmcfmf_messages', // todo: remove this as it is no longer used
            'kmcfmf_weekly_stats',
            'kmcfmf_weekend',
            'kmcfmf_last_cleared_date',
        );

        foreach ($option_names as $option_name) {
            if (get_option($option_name) == false) {
                // The option hasn't been added yet. We'll add it with $autoload set to 'no'.
                $deprecated = null;
                $autoload = 'no';
                add_option($option_name, 0, $deprecated, $autoload);
            }

            if ($reset_message_filter_counter) {
                update_option($option_name, 0);
            }

        }
        if ($reset_message_filter_counter || file_get_contents(self::$log_file) == '') {
            $content = "{}";
            file_put_contents(self::$log_file, $content);
        }
        update_option('kmcfmf_message_filter_reset', 'off');
        update_option('kmcfmf_weekly_stats', get_option('kmcfmf_weekly_stats') == '0' ? '[0,0,0,0,0,0,0]' : get_option('kmcfmf_weekly_stats'));
        update_option('kmcfmf_last_cleared_date', get_option('kmcfmf_last_cleared_date') == '0' ? strtotime(Date("d F Y")) : get_option('kmcfmf_last_cleared_date'));

        $date = get_option('kmcfmf_date_of_today');
        $now = strtotime(Date("d F Y"));
        $today = date("N", $now);
        if ((int)get_option('kmcfmf_weekend') == 0 || (int)get_option('kmcfmf_weekend') < (int)$now) {
            $sunday = strtotime("+" . (7 - $today) . "day");
            update_option('kmcfmf_weekend', $sunday);
            update_option('kmcfmf_weekly_stats', '[0,0,0,0,0,0,0]');
        }
        if ((int)$date < (int)$now) {
            $weekly_stats = json_decode(get_option('kmcfmf_weekly_stats'));
            $weekly_stats[date('N', $date) - 1] = get_option("kmcfmf_messages_blocked_today");
            update_option('kmcfmf_weekly_stats', json_encode($weekly_stats));
            update_option("kmcfmf_date_of_today", $now);
            update_option("kmcfmf_messages_blocked_today", 0);
            update_option("kmcfmf_emails_blocked_today", 0);
        }
    }

    private function add_filters()
    {
        add_filter('wpcf7_messages', array($this, 'add_custom_messages'), 10, 1);

        $enable_message_filter = get_option('kmcfmf_message_filter_toggle') == 'on' ? true : false;
        $enable_email_filter = get_option('kmcfmf_email_filter_toggle') == 'on' ? true : false;
        $enable_tags_by_names_filter = get_option('kmcfmf_tags_by_name_filter_toggle') == 'on' ? true : false;

        if ($enable_email_filter) {
            add_filter('wpcf7_validate_email', array($this, 'text_validation_filter'), 12, 2);
            add_filter('wpcf7_validate_email*', array($this, 'text_validation_filter'), 12, 2);
        }

        if ($enable_message_filter) {
            add_filter('wpcf7_validate_textarea', array($this, 'textarea_validation_filter'), 12, 2);
            add_filter('wpcf7_validate_textarea*', array($this, 'textarea_validation_filter'), 12, 2);
        }

        if ($enable_tags_by_names_filter) {
            add_filter('wpcf7_validate_text', array($this, 'text_tags_by_name_validation_filter'), 12, 2);
            add_filter('wpcf7_validate_text*', array($this, 'text_tags_by_name_validation_filter'), 12, 2);
        }

    }

    /**
     * Adds a custom message for messages flagged as spam
     * @since 1.2.2
     */
    public function add_custom_messages($messages)
    {
        $spam_word_eror = get_option('kmcfmf_spam_word_error') ? get_option('kmcfmf_spam_word_error') : 'One or more fields have an error. Please check and try again.';
        $spam_email_error = get_option('kmcfmf_spam_email_error') ? get_option('kmcfmf_spam_email_error') : 'The e-mail address entered is invalid.';
        $messages = array_merge($messages, array(
            'spam_word_error' => array(
                'description' =>
                    __("Message contains a word marked as spam", 'contact-form-7'),
                'default' =>
                    __($spam_word_eror, 'contact-form-7'),
            ),
            'spam_email_error' => array(
                'description' =>
                    __("Email is an email marked as spam", 'contact-form-7'),
                'default' =>
                    __($spam_email_error, 'contact-form-7'),
            ),
        ));

        return $messages;
    }

    /**
     * Displays Dashboard page
     * @since 1.2.0
     */
    public function dashboard_view()
    {
        include "views/dashboard.php";
    }

    /**
     * Displays messages page
     * @since 1.2.0
     */
    public function messages_view()
    {
        include "views/messages.php";
    }

    /**
     * Filters text from form text elements from elems_names List
     * @author: UnderWordPressure
     * @since 1.2.3
     */
    function text_tags_by_name_validation_filter($result, $tag)
    {

        $name = $tag->name;
        $names = preg_split('/[\s,]+/', get_option('kmcfmf_tags_by_name'));
        if (in_array($name, $names)) {
            $result = $this->textarea_validation_filter($result, $tag);
        }

        return $result;

    }

    /**
     * Filters text from textarea
     * @since 1.0.0
     */
    function textarea_validation_filter($result, $tag)
    {
        $name = $tag->name;

        $found = false;

        // UnderWordPressue: Change explode(" ", $values) to preg_split reason: whole whitespace range AND comma are valid separators
        $check_words = preg_split('/[\s,]+/', get_option('kmcfmf_restricted_words'));

        $message = isset($_POST[$name]) ? trim((string)$_POST[$name]) : '';

        // UnderWordPressue: make all lowercase - safe is safe
        $values = strtolower($message);
        //$value = '';

        // UnderWordPressue: Change explode(" ", $values) to preg_split([white-space]) -  reason: whole whitespace range are valid separators
        //                   and rewrite the foreach loops
        $values = preg_split('/\s+/', $values);
        foreach ($values as $value) {
            $value = trim($value);

            foreach ($check_words as $check_word) {

                /*if (preg_match("/^\.\w+/miu", $value) > 0) {
                    $found = true;
                }else if (preg_match("/\b" . $check_word . "\b/miu", $value) > 0) {
                    $found = true;
                }*/

                $check_word = strtolower(trim($check_word));
                switch ($check_word) {
                    case '':
                        break;
                    case '[russian]':
                        $found = preg_match('/[а-яА-Я]/miu', $value);
                        break;
                    case '[link]':
                        $pattern = '/((ftp|http|https):\/\/\w+)|(www\.\w+\.\w+)/ium'; // filters http://google.com and http://www.google.com and www.google.com
                        $found = preg_match($pattern, $value);
                        break;
                    default:

                        $like_start = (preg_match('/^\*/', $check_word));
                        $like_end = (preg_match('/\*$/', $check_word));

                        # Remove leading and trailing asterisks from $check_word
                        $regex_pattern = preg_quote(trim($check_word, '*'), '/');

                        if ($like_start) {
                            $regex_pattern = '.*' . $regex_pattern;
                        }
                        if ($like_end) {
                            $regex_pattern = $regex_pattern . '+.*';
                        }

                        $found = preg_match('/^' . $regex_pattern . '$/miu', $value);

                        break;
                }

                if ($found) {
                    break 2; // stops the first foreach loop since we have already identified a spam word
                }
            }

        } // end of foreach($values...)


        #####################
        # Final evaluation. #
        #####################

        // Spam word is recognized
        if ($found) {
            $result->invalidate($tag, wpcf7_get_message('spam_word_error'));

            $this->temp_email = $_POST['your-email'];

            if (!$this->count_updated && $this->temp_email != '') {
                $this->update_log($this->temp_email, $message);
            }
        } else {

            // Check additional conditions on $message
            if (empty($message)) {
                // No content ($message) in a required Tag
                if ($tag->is_required()) {
                    $result->invalidate($tag, wpcf7_get_message('invalid_required'));
                }
            } else {

                $maxlength = $tag->get_maxlength_option();
                $minlength = $tag->get_minlength_option();

                if ($maxlength && $minlength && $maxlength < $minlength) {
                    $maxlength = $minlength = null;
                }

                $code_units = wpcf7_count_code_units(stripslashes($message));

                if ($code_units) {
                    if ($maxlength && $maxlength < $code_units) {
                        $result->invalidate($tag, wpcf7_get_message('invalid_too_long'));
                    } elseif ($minlength && $code_units < $minlength) {
                        $result->invalidate($tag, wpcf7_get_message('invalid_too_short'));
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Filters text from text input fields
     * @since 1.0.0
     */
    function text_validation_filter($result, $tag)
    {
        $name = $tag->name;
        $check_words = explode(" ", get_option('kmcfmf_restricted_emails'));

        $value = isset($_POST[$name])
            ? trim(wp_unslash(strtr((string)$_POST[$name], "\n", " ")))
            : '';

        if ('text' == $tag->basetype) {
            if ($tag->is_required() && '' == $value) {
                $result->invalidate($tag, wpcf7_get_message('invalid_required'));
            }
        }

        if ('email' == $tag->basetype) {
            if ($tag->is_required() && '' == $value) {
                $result->invalidate($tag, wpcf7_get_message('invalid_required'));
            } elseif ('' != $value && !wpcf7_is_email($value)) {
                $result->invalidate($tag, wpcf7_get_message('invalid_email'));
            } else {
                foreach ($check_words as $check_word) {
                    if (strpos($value, $check_word) !== false) {
                        $this->temp_message = $_POST['your-message'];
                        $result->invalidate($tag, wpcf7_get_message('spam_email_error'));

                        if (!$this->count_updated && $this->temp_message != '') {
                            $this->update_log($value, $this->temp_message);
                        }
                    }
                }
            }
        }

        if ('' !== $value) {
            $maxlength = $tag->get_maxlength_option();
            $minlength = $tag->get_minlength_option();

            if ($maxlength && $minlength && $maxlength < $minlength) {
                $maxlength = $minlength = null;
            }

            $code_units = wpcf7_count_code_units(stripslashes($value));

            if (false !== $code_units) {
                if ($maxlength && $maxlength < $code_units) {
                    $result->invalidate($tag, wpcf7_get_message('invalid_too_long'));
                } elseif ($minlength && $code_units < $minlength) {
                    $result->invalidate($tag, wpcf7_get_message('invalid_too_short'));
                }
            }
        }

        return $result;
    }

    /**
     * Logs messages blocked to the log file
     * @since 1.2.0
     */
    private function update_log($email, $message)
    {
        $submission = WPCF7_Submission::get_instance();
        $contact_form = $submission->get_contact_form();
        update_option('kmcfmf_last_message_blocked', '<td>' . Date('d-m-y h:ia') . ' </td><td>' . $email . '</td><td>' . $message . ' </td>');
        //update_option("kmcfmf_messages", get_option("kmcfmf_messages") . "]kmcfmf_message[ kmcfmf_data=" . $message . " kmcfmf_data=" . $this->temp_email . " kmcfmf_data=" . Date('d-m-y  h:ia'));
        $log_messages = (array)json_decode(file_get_contents(self::$log_file));
        $log_message = ['message' => $message, 'date' => Date('d-m-y  h:ia'), 'email' => $email];
        $log_message = ['id' => $contact_form->id(), 'name' => $contact_form->name(), 'title' => $contact_form->title(), 'data' => $submission->get_posted_data(), 'date' => Date('d-m-y  h:ia')];
        array_push($log_messages, $log_message);

        $log_messages = json_encode((object)$log_messages);
        file_put_contents(self::$log_file, $log_messages);
        update_option('kmcfmf_messages_blocked', get_option('kmcfmf_messages_blocked') + 1);
        update_option("kmcfmf_messages_blocked_today", get_option("kmcfmf_messages_blocked_today") + 1);
        $today = date('N');
        $weekly_stats = json_decode(get_option('kmcfmf_weekly_stats'));
        $weekly_stats[$today - 1] = get_option("kmcfmf_messages_blocked_today");
        update_option('kmcfmf_weekly_stats', json_encode($weekly_stats));

        $this->count_updated = true;

        $logs_root = wp_upload_dir()['basedir'] . '/kmcf7mf_logs/';
        $submission = WPCF7_Submission::get_instance();
        file_put_contents($logs_root . 'test.txt', json_encode($submission->get_posted_data()));
    }

    /**
     * Transfer data in old format to new format, when plugin is updated to from an older version to this version
     * @since 1.2.0
     */
    private function transfer_old_data()
    {
        if (get_option('kmcfmf_messages') == '0') {
            // for those migrating from =<v1.2.4 to >=v1.2.5
            $old_logs_root = plugin_dir_path(dirname(__FILE__)) . 'logs/';
            $old_logs_file = $old_logs_root . 'messages.txt';
            if (is_file($old_logs_file)) {
                rename($old_logs_file, self::$log_file);
            }
        } else {
            // for those migrating from v1.1.x to >=v1.2.0
            $messages = explode("]kmcfmf_message[", get_option('kmcfmf_messages'));
            $log_messages = [];
            for ($i = 0; $i < sizeof($messages); $i++) {
                $data = explode("kmcfmf_data=", $messages[$i]);
                if ($data[1] != '' && $data[2] != '' && $data[3] != '') {
                    $log_message = ['message' => $data[1], 'date' => $data[3], 'email' => $data[2]];
                    array_push($log_messages, $log_message);

                }
            }
            $log_messages = json_encode((object)$log_messages);
            file_put_contents(self::$log_file, $log_messages);

            update_option('kmcfmf_messages', 0);
        }
    }
}
