<?php

namespace kmcf7_message_filter;

use WPCF7_ContactForm;

$link_to_messages     = admin_url( 'admin.php' ) . '?page=kmcf7-filtered-messages';
$link_to_old_messages = $link_to_messages . '&old';


$form_id = isset( $_GET['form'] ) ? sanitize_text_field( $_GET['form'] ) : 0;
$form_id = $form_id == '' ? - 1 : intval( $form_id );

?>
<style>
    #wpbody-content {
        overflow-x: scroll;
    }
</style>

<!--<button class="btn btn-primary">Export to CSV</button>-->
<?php if ( $form_id > 0 ) {
	$contact_form = WPCF7_ContactForm::get_instance( $form_id );
	$rows         = $contact_form->scan_form_tags();
	?>
    <form action="" class="form-inline mb-4 mt-4">
        <input type="hidden" name="page" value="kmcf7-filtered-messages">
        <select name="form" id="" class="py-0 form-control">
            <option value=""><?php _e( "Select a form", KMCF7MS_TEXT_DOMAIN ) ?></option>
			<?php foreach ( MessagesModule::getForms() as $form ): ?>
                <option value="<?php echo $form[1] ?>" <?php echo $form_id == $form[1] ? 'selected' : '' ?>><?php echo $form[0] ?></option>
			<?php endforeach; ?>
        </select>
        <button class="btn btn-primary btn-inline ml-1"><?php _e( "Show Blocked Messages", KMCF7MS_TEXT_DOMAIN ) ?></button>

    </form>
    <table id="km-table" class="kmcfmf_table table table-striped" style="overflow-x: scroll">
        <thead>
        <tr>
            <th></th>
            <th><?php _e( "Actions", KMCF7MS_TEXT_DOMAIN ) ?></th>
            <th><b>ID</b></th>
			<?php foreach ( $rows as $row ): $row = $row->name ?>
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
    <div class="jumbotron mt-5">
		<?php if ( is_file( MessagesModule::getLogFile() ) ): ?>
            <div class="mb-2 border-info">
                <h5><?php _e( "Note: Message storage location has changed.", KMCF7MS_TEXT_DOMAIN ) ?>
                    <a href="<?php echo $link_to_old_messages ?>" class="btn btn-primary btn-sm">
						<?php _e( "Click here to view your old messages", KMCF7MS_TEXT_DOMAIN ) ?>
                    </a>
                </h5>
            </div>
		<?php endif; ?>
        <h2 class="display-5d"><?php _e( "Blocked Messages Area", KMCF7MS_TEXT_DOMAIN ) ?></h2>
        <p class="lead"><?php _e( "Messages are now grouped per form. Select a form below to view all messages blocked for that
            form.", KMCF7MS_TEXT_DOMAIN ) ?></p>
        <p class="lead"><?php _e( "If you upgraded from a previous version, all old messages blocked are stored under
            uncategorized.", KMCF7MS_TEXT_DOMAIN ) ?></p>

        <hr class="my-4">
        <form action="" class="form-inline mb-4 mt-4">
            <input type="hidden" name="page" value="kmcf7-filtered-messages">
            <select name="form" id="" class="py-0 form-control">
                <option value=""><?php _e( "Select a form", KMCF7MS_TEXT_DOMAIN ) ?></option>
			    <?php foreach ( MessagesModule::getForms() as $form ): ?>
                    <option value="<?php echo $form[1] ?>" <?php echo $form_id == $form[1] ? 'selected' : '' ?>><?php echo $form[0] ?></option>
			    <?php endforeach; ?>
            </select>
            <button class="btn btn-primary btn-inline ml-1"><?php _e( "Show Blocked Messages", KMCF7MS_TEXT_DOMAIN ) ?></button>

        </form>
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
                    ajax: '<?php echo admin_url( "admin-ajax.php?action=kmcf7_messages&form_id={$form_id}" )?>',
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
