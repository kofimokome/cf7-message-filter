<?php

namespace km_message_filter;

$message_id = intval( sanitize_text_field( wp_unslash( $_GET['message_id'] ) ) );
$ajax_url   = admin_url( "admin-ajax.php" );

?>
    <style>
        #wpbody-content {
            overflow-x: scroll;
        }
    </style>
    <h3>
        <button class="btn btn-sm btn-primary"
                onclick="window.history.back()"><?php _e( "Go back", 'cf7-message-filter' ) ?></button>
		<?php _e( "Message Details", 'cf7-message-filter' ) ?>
    </h3>

	<?php if ( $message_id > 0 ) {
	$message_object  = Message::find( $message_id );
	$form_id         = $message_object->form_id;
	$contact_form    = $message_object->contact_form;
	$message         = json_decode( $message_object->message );
	$messages_module = MessagesModule::getInstance();
	$rows            = $messages_module->getColumns2( $form_id, $contact_form );
	?>
    <table class="kmcfmf_table table table-striped" style="overflow-x: scroll">
        <thead>
        <tr>
            <th><?php _e( "Field", 'cf7-message-filter' ) ?></th>
            <th><?php _e( "Value", 'cf7-message-filter' ) ?></th>
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
						if ( is_array( $message->$row ) ) {
							echo esc_html( wp_json_encode( $message->$row ) );
						} else {
							echo esc_html( $message->$row );
						}
					} else {
						echo "";
					} ?>
                </td>
            </tr>
		<?php endforeach; ?>
        <tr>
            <td>
                <b>
					<?php _e( "Date Blocked", 'cf7-message-filter' ) ?>
                </b>
            </td>
            <td>
				<?php echo $message_object->created_at ?>
            </td>
        </tr>
        </tbody>
    </table>
    <button class="btn btn-danger btn-sm" onclick="showDeleteModal()">
		<?php _e( "Delete", 'cf7-message-filter' ) ?>
    </button>
    <button class="btn btn-primary btn-sm" onclick="showResubmitModal()">
		<?php _e( "Resubmit", 'cf7-message-filter' ) ?>
    </button>

    <!--    <div class="mt-3">
			<form action="https://ko-fi.com/kofimokome" method="post" target="_blank">
				<input type="hidden" name="hosted_button_id" value="B3JAV39H95RFG"/>
				<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit"
					   title="Ko-fi is the easiest way for you to start making an income directly from your fans" alt="Donate with PayPal button"/>
				<img alt="" border="0" src="https://www.paypal.com/en_CM/i/scr/pixel.gif" width="1" height="1"/>
			</form>
		</div>-->
    <script>
        const message_id = <?php echo $message_id?>;
        const DELETE_MESSAGE_NONCE = "<?php echo wp_create_nonce( 'kmcfmf_can_delete_messages' )?>";
        const RESUBMIT_MESSAGE_NONCE = "<?php echo wp_create_nonce( 'kmcfmf_can_resubmit_messages' )?>";

        function bootstrapSwal() {
            return Swal.mixin({
                customClass: {
                    confirmButton: 'btn btn-success mr-2',
                    cancelButton: 'btn btn-danger'
                },
                buttonsStyling: false
            });
        }

        function showDeleteModal() {
            let formData = new FormData();
            formData.append("action", 'kmcf7_delete_message');
            formData.append("message_ids", message_id);

            bootstrapSwal().fire({
                title: 'Delete Message',
                text: '<?php _e( "Are you sure you want to delete this message?", 'cf7-message-filter' ) ?>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete',
                showLoaderOnConfirm: true,
                preConfirm: (login) => {
                    return fetch("<?php echo $ajax_url?>" + "?_wpnonce=" + DELETE_MESSAGE_NONCE, {
                        method: 'POST',
                        body: formData
                    })
                        .then(async response => {
                            if (!response.ok) {
                                const e = await response.text();
                                let message = "Something went wrong";
                                try {
                                    const response_json = JSON.parse(e)
                                    if (response_json.data)
                                        message = response_json.data.message ?? response_json.data.toString()
                                } catch (e) {
                                    // Silence is golden
                                }
                                throw new Error(message)
                            } else
                                return response.json()
                        })
                        .catch(error => {
                            Swal.showValidationMessage(
                                `Request failed: ${error}`
                            )
                        })
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    /*Swal.fire({
                        title: `Delete Message`,
                        icon: 'success',
                        text: '<?php  _e( "Message deleted successfully", 'cf7-message-filter' )?>',
                    }).then((result) => {
                        if (result.isConfirmed)*/
                    history.back()
                    // })
                }
            })
        }

        function showResubmitModal() {
            let formData = new FormData();
            formData.append("action", 'kmcf7_resubmit_message');
            formData.append("message_ids", message_id);

            bootstrapSwal().fire({
                title: 'Resubmit Message',
                text: '<?php _e( "Resubmitting a message may not work if you have another spam filter or captcha plugin installed. We will not be able to bypass the verification process of these plugins.", 'cf7-message-filter' ) ?>',
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'OK, resubmit',
                showLoaderOnConfirm: true,
                preConfirm: (login) => {
                    return fetch("<?php echo $ajax_url?>" + "?_wpnonce=" + RESUBMIT_MESSAGE_NONCE, {
                        method: 'POST',
                        body: formData
                    })
                        .then(async response => {
                            if (!response.ok) {
                                const e = await response.text();
                                let message = "Something went wrong";
                                try {
                                    const response_json = JSON.parse(e)
                                    if (response_json.data)
                                        message = response_json.data.message ?? response_json.data.toString()
                                } catch (e) {
                                    // Silence is golden
                                }
                                throw new Error(message)
                            } else
                                return response.json()
                        })
                        .catch(error => {
                            Swal.showValidationMessage(
                                `Request failed: ${error}`
                            )
                        })
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: `Resubmit message`,
                        icon: 'success',
                        text: '<?php  _e( "Message resubmitted successfully", 'cf7-message-filter' )?>',
                    }).then((result) => {
                        if (result.isConfirmed)
                            history.back()
                    })
                }
            })
        }
    </script>
	<?php

}
?>