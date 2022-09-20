<?php

namespace kmcf7_message_filter;

use WPCF7_ContactForm;

$message_id = intval(sanitize_text_field($_GET['message_id']));

function decodeUnicodeVars($message)
{
    $message = is_array($message) ? implode(" ", $message) : $message;

    return mb_convert_encoding($message, 'UTF-8',
        mb_detect_encoding($message, 'UTF-8, ISO-8859-1', true));
}

?>
<style>
    #wpbody-content {
        overflow-x: scroll;
    }
</style>
<h3>
    <button class="btn btn-sm btn-primary" onclick="window.history.back()">Go back</button>
    Message Details
</h3>

<?php if ($message_id > 0) {
    $message_object = Message::find($message_id);
    $form_id = $message_object->form_id;
    $message = json_decode($message_object->message);
    $contact_form = WPCF7_ContactForm::get_instance($form_id);
    $rows = $contact_form->scan_form_tags();
    ?>
    <table class="kmcfmf_table table table-striped" style="overflow-x: scroll">
        <thead>
        <tr>
            <th>Field</th>
            <th>Value</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $row): $row = $row->name ?>
            <tr>
                <td>
                    <b> <?php echo $row ?></b>
                </td>
                <td>
                    <?php if (property_exists($message, $row)) {
                        echo esc_html($message->$row);
                    } else {
                        echo "";
                    } ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <button class="btn btn-danger btn-sm">Delete</button>
    <?php

}
?>
