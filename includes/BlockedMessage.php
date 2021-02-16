<?php
/**
 * Created by PhpStorm.
 * User: kofi
 * Date: 6/5/19
 * Time: 12:41 PM
 */

namespace kmcf7_message_filter;

class BlockedMessage
{
    private static $test;

    public static function get_forms()
    {
        $log_file = CF7MessageFilter::get_log_file_path();
        $messages = (array)json_decode(file_get_contents($log_file));
        $forms = array();
        foreach ($messages as $message) {
            if (property_exists($message, 'id')) {
                if (sizeof($form = \WPCF7_ContactForm::find(array('p' => $message->id))) > 0) {
                    array_push($forms, [$form[0]->title(), $message->id]);
                } else {
                    array_push($forms, [$message->title, $message->id]);
                }
            } else {
                array_push($forms, ['uncategorized', 0]);
            }
        }
        return array_unique($forms, SORT_REGULAR);

    }

}