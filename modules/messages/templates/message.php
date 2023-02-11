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
					<?php _e( "Date Blocked", KMCF7MS_TEXT_DOMAIN ) ?>
                </b>
            </td>
            <td>
				<?php echo $message_object->created_at ?>
            </td>
        </tr>
        </tbody>
    </table>
    <button class="btn btn-danger btn-sm" onclick="showDeleteModal()">
		<?php _e( "Delete", KMCF7MS_TEXT_DOMAIN ) ?>
    </button>
    <button class="btn btn-primary btn-sm" onclick="showResubmitModal()">
		<?php _e( "Mark as not spam", KMCF7MS_TEXT_DOMAIN ) ?>
    </button>


    <script>
        const message_id = <?php echo $message_id?>;

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
                text: '<?php _e( "Are you sure you want to delete this message?", KMCF7MS_TEXT_DOMAIN ) ?>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete',
                showLoaderOnConfirm: true,
                preConfirm: (login) => {
                    return fetch("<?php echo $ajax_url?>", {
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
                        title: `Delete Message`,
                        icon: 'success',
                        text: '<?php  _e( "Message deleted successfully", KMCF7MS_TEXT_DOMAIN )?>',
                    }).then((result) => {
                        if (result.isConfirmed)
                            history.back()
                    })
                }
            })
        }

        function showResubmitModal() {
            let formData = new FormData();
            formData.append("action", 'kmcf7_resubmit_message');
            formData.append("message_id", message_id);

            bootstrapSwal().fire({
                title: 'Resubmit Message',
                text: '<?php _e( "Resubmitting a message may not work if you have another spam filter or captcha plugin installed. We will not be able to bypass the verification process of these plugins.", KMCF7MS_TEXT_DOMAIN ) ?>',
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'OK, resubmit',
                showLoaderOnConfirm: true,
                preConfirm: (login) => {
                    return fetch("<?php echo $ajax_url?>", {
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
                        text: '<?php  _e( "Message resubmitted successfully", KMCF7MS_TEXT_DOMAIN )?>',
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