<?php

namespace km_message_filter;

$link_to_messages     = admin_url( 'admin.php' ) . '?page=kmcf7-filtered-messages';
$link_to_old_messages = $link_to_messages . '&old';
$ajax_url             = admin_url( "admin-ajax.php" );


$selected_form = isset( $_GET['form'] ) ? sanitize_text_field( $_GET['form'] ) : '';
$data          = explode( '-', $selected_form );
$form_id       = - 1;
$contact_form     = '';
if ( sizeof( $data ) > 1 ) {
	$contact_form = trim( $data[0] );
	$form_id   = $data[1];
	$form_id   = $form_id == '' ? - 1 : intval( $form_id );
}

?>
<style>
    #wpbody-content {
        overflow-x: scroll;
    }
</style>

<!--<button class="btn btn-primary">Export to CSV</button>-->
<?php if ( $form_id > 0 && $contact_form != '' ) {
	$rows = MessagesModule::getRows2( $form_id, $contact_form );
	?>
    <form action="" class="form-inline mb-4 mt-4">
        <input type="hidden" name="page" value="kmcf7-filtered-messages">
        <select name="form" id="" class="py-0 form-control">
            <option value=""><?php _e( "Select a form", KMCF7MS_TEXT_DOMAIN ) ?></option>
			<?php foreach ( MessagesModule::getForms() as $form ): ?>
                <option value="<?php echo $form[1] ?>" <?php echo $selected_form == $form[1] ? 'selected' : '' ?>><?php echo $form[0] ?></option>
			<?php endforeach; ?>
        </select>
        <button class="btn btn-primary btn-inline ml-1"><?php _e( "Show Blocked Messages", KMCF7MS_TEXT_DOMAIN ) ?></button>
    </form>
    <div class="mb-2">
        <div class="alert alert-info">
			<?php _e( "Hint: Press and hold <kbd>CMD</kbd> or <kbd>CRTL</kbd> while clicking on any cell to select it", KMCF7MS_TEXT_DOMAIN ) ?>
        </div>
        <button class="btn btn-danger btn-sm km-delete-btn" style="display: none" data-toggle="modal"
                data-target="#deleteModal">
			<?php _e( "Delete selected", KMCF7MS_TEXT_DOMAIN ) ?>
        </button>
    </div>
    <table id="km-table" class="kmcfmf_table table table-striped" style="overflow-x: scroll">
        <thead>
        <tr>
            <th></th>
            <th><?php _e( "Actions", KMCF7MS_TEXT_DOMAIN ) ?></th>
            <th><b>ID</b></th>
			<?php foreach ( $rows as $row ): ?>
                <td>
                    <b><?php echo $row ?></b>
                </td>
			<?php endforeach; ?>
        </tr>
        </thead>
        <tbody>

        </tbody>
    </table>
    <button class="btn btn-danger btn-sm km-delete-btn" style="display: none" data-toggle="modal"
            data-target="#deleteModal">
		<?php _e( "Delete selected", KMCF7MS_TEXT_DOMAIN ) ?>
    </button>    <br>
    <!-- Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?php _e( "Delete Message", KMCF7MS_TEXT_DOMAIN ) ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
					<?php _e( "Are you sure you want to delete the selected messages?", KMCF7MS_TEXT_DOMAIN ) ?>
                    <div class="alert alert-danger d-none" id="delete-error">
                        something went wrong
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm modal-btn"
                            data-dismiss="modal"><?php _e( "Cancel", KMCF7MS_TEXT_DOMAIN ) ?></button>
                    <button type="button" id="delete-message"
                            class="btn btn-danger btn-sm modal-btn"><?php _e( "Yes Delete", KMCF7MS_TEXT_DOMAIN ) ?></button>
                    <button type="button" id="btn-loading"
                            class="btn btn-primary btn-sm hidden d-none"><?php _e( "Please wait...", KMCF7MS_TEXT_DOMAIN ) ?></button>
                </div>
            </div>
        </div>
    </div>
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
<div class="row">
    <form action="https://ko-fi.com/kofimokome" method="post" target="_blank">
        <input type="hidden" name="hosted_button_id" value="B3JAV39H95RFG"/>
        <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit"
               title="Ko-fi is the easiest way for you to start making an income directly from your fans" alt="Donate with PayPal button"/>
        <img alt="" border="0" src="https://www.paypal.com/en_CM/i/scr/pixel.gif" width="1" height="1"/>
    </form>
</div>
<script>
    jQuery(function ($) {
        $(document).ready(function () {
            const table = $("#km-table").DataTable({
                    dom: 'lBfrtip',
                    ordering: false,
                    processing: true,
                    serverSide: true,
                    ajax: '<?php echo admin_url( "admin-ajax.php?action=kmcf7_messages&form_id={$form_id}&contact_form={$contact_form}" )?>',
                    columnDefs: [{
                        orderable: false,
                        className: 'select-checkbox',
                        targets: 0
                    }],
                    buttons: [
                        'colvis',
                        {
                            extend: 'csv',
                            text: 'Download CSV'
                        },
                    ],
                    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                    select: true

                }
            );
            $('#km-table tbody').on('click', 'tr', function () {
                const delete_count = table.rows({selected: true}).count()
                // const delete_count = table.rows('.selected').data().length
                if (delete_count > 0) {
                    $(".km-delete-btn").show()
                } else {
                    $(".km-delete-btn").hide()
                }
            });

            const loading_btn = $("#btn-loading")
            const error_container = $("#delete-error")
            const modal_btn = $(".modal-btn")

            $("#delete-message").click(function (e) {
                e.preventDefault();
                let message_ids = []
                const data = table.rows('.selected').data()
                for (let i = 0; i < data.length; i++) {
                    message_ids.push(data[i][2])
                }
                modal_btn.hide()
                loading_btn.removeClass("d-none");
                error_container.addClass("d-none")
                let formData = new FormData();
                formData.append("action", 'kmcf7_delete_message');
                formData.append("message_ids", message_ids.join(','));

                $.ajax({
                    type: "POST",
                    contentType: false,
                    processData: false,
                    url: "<?php echo $ajax_url?>",
                    data: formData,
                    success: function (e) {
                        window.location.reload()
                        //window.location.href = "<?php //echo $link_to_messages?>//"
                    },
                    error: function (e) {
                        loading_btn.addClass("d-none")
                        modal_btn.show();
                        error_container.removeClass("d-none")
                    }
                })
            })
        })

    })

</script>
