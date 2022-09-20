<?php

namespace kmcf7_message_filter;

use WPCF7_ContactForm;

$link_to_messages = admin_url('admin.php') . '?page=kmcf7-filtered-messages';
$link_to_old_messages = $link_to_messages . '&old';


$form_id = isset($_GET['form']) ? sanitize_text_field($_GET['form']) : 0;
$form_id = $form_id == '' ? -1 : intval($form_id);

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
<h3><?php echo get_option('kmcfmf_messages_blocked'); ?> messages have been blocked</h3>

<form action="" class="form-inline mb-4">
    <input type="hidden" name="page" value="kmcf7-filtered-messages">
    <select name="form" id="" class="py-0 form-control-sm">
        <option value="">Select a form</option>
        <?php foreach (MessagesModule::getForms() as $form): ?>
            <option value="<?php echo $form[1] ?>" <?php echo $form_id == $form[1] ? 'selected' : '' ?>><?php echo $form[0] ?></option>
        <?php endforeach; ?>
    </select>
    <button class="btn btn-primary btn-inline ml-1 btn-sm">Show Blocked Messages</button>

</form>
<!--<button class="btn btn-primary">Export to CSV</button>-->
<?php if ($form_id > 0) {
    $contact_form = WPCF7_ContactForm::get_instance($form_id);
    $rows = $contact_form->scan_form_tags();
    ?>
    <table id="km-table" class="kmcfmf_table table table-striped" style="overflow-x: scroll">
        <thead>
        <tr>
            <th></th>
            <th>Actions</th>
            <th><b>ID</b></th>
            <?php foreach ($rows as $row): $row = $row->name ?>
                <td>
                    <b><?php echo $row ?></b>
                </td>
            <?php endforeach; ?>
        </tr>
        </thead>
        <tbody>

        </tbody>
    </table>
    <br>
    <?php

} else { ?>
    <div class="jumbotron">
        <?php if (is_file(MessagesModule::getLogFile())): ?>
            <div class="mb-2 border-info">
                <h5>Note: Message storage location has changed.
                    <a href="<?php echo $link_to_old_messages ?>" class="btn btn-primary btn-sm">
                        Click here to view your old messages
                    </a>
                </h5>
            </div>
        <?php endif; ?>
        <h2 class="display-5d">Blocked Messages Area</h2>
        <p class="lead">Messages are now grouped per form. Select a form above to view all messages blocked for that
            form.</p>
        <p class="lead">If you upgraded from a previous version, all old messages blocked are stored under
            uncategorized.</p>

        <hr class="my-4">
    </div>
    <?php
}

?>

<script>
    jQuery(function ($) {
        $(document).ready(function () {
            const table = $("#km-table").DataTable({
                    ordering: false,
                    processing: true,
                    serverSide: true,
                    ajax: '<?php echo admin_url("admin-ajax.php?action=kmcf7_messages&form_id={$form_id}")?>',
                    columnDefs: [{
                        orderable: false,
                        className: 'select-checkbox',
                        targets: 0
                    }],
                    select: {
                        style: 'os',
                        selector: 'td:first-child'
                    },
                }
            );

        })

    })

</script>
