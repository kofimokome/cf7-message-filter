<?php

namespace km_message_filter;

$message_id = intval( sanitize_text_field( $_GET['message_id'] ) );
$ajax_url   = admin_url( "admin-ajax.php" );

?>
    <style>
        #wpbody-content {
            overflow-x: scroll;
        }
    </style>
    <h3>
        <button class="btn btn-sm btn-primary"
                onclick="window.history.back()"><?php _e( "Go back", KMCF7MS_TEXT_DOMAIN ) ?></button>
		<?php _e( "Message Details", KMCF7MS_TEXT_DOMAIN ) ?>
    </h3>

	<?php if ( $message_id > 0 ) {
	$message_object = Message::find( $message_id );
	$form_id        = $message_object->form_id;
	$contact_form   = $message_object->contact_form;
	$message        = json_decode( $message_object->message );
	$rows           = MessagesModule::getRows2( $form_id, $contact_form );
	?>
    <table class="kmcfmf_table table table-striped" style="overflow-x: scroll">
        <thead>
        <tr>
            <th><?php _e( "Field", KMCF7MS_TEXT_DOMAIN ) ?></th>
            <th><?php _e( "Value", KMCF7MS_TEXT_DOMAIN ) ?></th>
        </tr>
        </thead>
        <tbody>
		<?php foreach ( $rows as $row ): ?>
            <tr>
                <td>
                    <b> <?php echo $row ?></b>
                </td>
                <td>
					<?php if ( property_exists( $message, $row ) ) {
						echo esc_html( $message->$row );
					} else {
						echo "";
					} ?>
                </td>
            </tr>
		<?php endforeach; ?>
        <tr>
            <td>
                <b>
					<?php _e( "Blocked at", KMCF7MS_TEXT_DOMAIN ) ?>
                </b>
            </td>
            <td>
				<?php echo $message_object->created_at ?>
            </td>
        </tr>
        </tbody>
    </table>
    <button class="btn btn-danger btn-sm" data-toggle="modal"
            data-target="#deleteModal"><?php _e( "Delete", KMCF7MS_TEXT_DOMAIN ) ?></button>

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
					<?php _e( "Are you sure you want to delete this message?", KMCF7MS_TEXT_DOMAIN ) ?>
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
    <script>
        jQuery(function ($) {
            $(document).ready(function () {
                const message_id = <?php echo $message_id?>;
                const loading_btn = $("#btn-loading")
                const error_container = $("#delete-error")
                const modal_btn = $(".modal-btn")
                $("#delete-message").click(function (e) {
                    e.preventDefault();
                    modal_btn.hide()
                    loading_btn.removeClass("d-none");
                    error_container.addClass("d-none")
                    let formData = new FormData();
                    formData.append("action", 'kmcf7_delete_message');
                    formData.append("message_ids", message_id);

                    $.ajax({
                        type: "POST",
                        contentType: false,
                        processData: false,
                        url: "<?php echo $ajax_url?>",
                        data: formData,
                        success: function (e) {
                            history.back()
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
	<?php

}
?>