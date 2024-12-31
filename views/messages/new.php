<?php

namespace km_message_filter;

$link_to_messages     = admin_url( 'admin.php' ) . '?page=kmcf7-filtered-messages';
$link_to_old_messages = $link_to_messages . '&old';
$ajax_url             = admin_url( "admin-ajax.php" );


$columns               = '{}';
$form_id               = isset( $_GET['form-id'] ) ? sanitize_text_field( $_GET['form-id'] ) : 'all';
$selected_contact_form = isset( $_GET['contact-form'] ) ? sanitize_text_field( $_GET['contact-form'] ) : 'all';
if ( $selected_contact_form == 'all' ) {
	if ( class_exists( 'WPCF7_ContactForm' ) ) {
		$selected_contact_form = 'cf7';
	} else if ( function_exists( 'wpforms' ) ) {
		$selected_contact_form = 'wpforms';
	}
}
$selected_form = $selected_contact_form . '-' . $form_id;

if ( $selected_form != '' ) {
	$columns = get_option( 'kmcfmf_visible_columns_' . $selected_form, '{}' );
}
update_option( "kmcfmf_messages_blocked_today_tmp", 0 );
$forms = MessagesModule::getInstance()->getForms();

?>
<style>
    #wpbody-content {
        overflow-x: scroll;
    }
</style>

<!--<button class="btn btn-primary">Export to CSV</button>-->
<!--<div class="row mt-5">
    <form action="https://ko-fi.com/kofimokome" method="post" target="_blank">
        <input type="hidden" name="hosted_button_id" value="B3JAV39H95RFG"/>
        <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit"
               title="Ko-fi is the easiest way for you to start making an income directly from your fans"
               alt="Donate with PayPal button"/>
        <img alt="" border="0" src="https://www.paypal.com/en_CM/i/scr/pixel.gif" width="1" height="1"/>
    </form>
</div>-->
<?php
$rows = MessagesModule::getInstance()->getRows2( $form_id, $selected_contact_form );

?>
<form action="" class="form-inline mb-4 mt-4">
    <input type="hidden" name="page" value="kmcf7-filtered-messages">
    <select name="contact-form" id="km-contact-forms" class="py-0 form-control mr-1">
        <!--        <option value="all">--><?php //_e( "All Contact Forms", KMCF7MS_TEXT_DOMAIN )
		?><!--</option>-->
		<?php foreach ( $forms as $key => $form ): ?>
            <option value="<?php echo $key ?>" <?php echo $selected_contact_form == $key ? 'selected' : '' ?>><?php echo $form['name'] ?></option>
		<?php endforeach; ?>
    </select>
    <select name="form-id" id="km-registered-forms" class="py-0 form-control">
        <option value="all"><?php _e( "All Registered Forms", KMCF7MS_TEXT_DOMAIN ) ?></option>
		<?php foreach ( $forms as $key => $contact_form ): ?>
			<?php if ( $key == $selected_contact_form || $selected_contact_form == 'all' ): ?>
				<?php foreach ( $contact_form['forms'] as $form ): ?>
                    <option value="<?php echo $form['id'] ?>" <?php echo $form_id == $form['id'] ? 'selected' : '' ?>><?php echo $form['name'] ?></option>
				<?php endforeach; ?>
			<?php endif; ?>

		<?php endforeach; ?>
    </select>
    <button class="btn btn-primary btn-inline ml-1"><?php _e( "Show Blocked Messages", KMCF7MS_TEXT_DOMAIN ) ?></button>
</form>
<div class="mb-2">
    <div class="alert alert-info">
		<?php _e( "Hint: Press and hold <kbd>CMD</kbd> or <kbd>CRTL</kbd> while clicking on any cell to select it", KMCF7MS_TEXT_DOMAIN ) ?>
    </div>
    <button class="btn btn-danger btn-sm km-delete-btn" style="display: none" onclick="showDeleteModal()">
		<?php _e( "Delete selected", KMCF7MS_TEXT_DOMAIN ) ?>
    </button>
    <!--<button class="btn btn-primary btn-sm km-delete-btn" style="display: none" onclick="showResubmitModal()">
			<?php /*_e( "Restore selected", KMCF7MS_TEXT_DOMAIN ) */ ?>
        </button>-->
</div>
<div class="mb-3">
    <b><?php _e( "Visible Columns", KMCF7MS_TEXT_DOMAIN ) ?>: <a href="#" id="toggle-visible-columns-container">Show/Hide</a>
        <div id="visible-columns-container" class="mt-2">
            <input id="input-ID" name="ID" type="checkbox" value="2" class="table-column"
                   checked/> <span class="mr-2">ID</span>
			<?php foreach ( $rows as $index => $row ):if ( strlen( trim( $row ) ) > 0 ): ?>
                <input id="input-<?php echo $row ?>" name="<?php echo $row ?>" type="checkbox"
                       value="<?php echo $index + 3 ?>" class="table-column"
                       checked/> <span class="mr-2"> <?php echo $row ?></span>
			<?php endif; endforeach; ?>
        </div>
</div>
<table id="km-table" class="kmcfmf_table table table-striped" style="overflow-x: scroll">
    <thead>
    <tr>
        <th></th>
        <th><?php _e( "Actions", KMCF7MS_TEXT_DOMAIN ) ?></th>
        <th><b>ID</b></th>
		<?php foreach ( $rows as $row ): ?>
            <th>
                <b><?php echo $row ?></b>
            </th>
		<?php endforeach; ?>
    </tr>
    </thead>
    <tbody>

    </tbody>
</table>
<button class="btn btn-danger btn-sm km-delete-btn" style="display: none" onclick="showDeleteModal()">
	<?php _e( "Delete selected", KMCF7MS_TEXT_DOMAIN ) ?>
</button>
<!--<button class="btn btn-primary btn-sm km-delete-btn" style="display: none" onclick="showResubmitModal()">
		<?php /*_e( "Restore selected", KMCF7MS_TEXT_DOMAIN ) */ ?>
    </button> -->
<br>
<?php

?>
<!--<div class="row">
    <form action="https://ko-fi.com/kofimokome" method="post" target="_blank">
        <input type="hidden" name="hosted_button_id" value="B3JAV39H95RFG"/>
        <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit"
               title="Ko-fi is the easiest way for you to start making an income directly from your fans"
               alt="Donate with PayPal button"/>
        <img alt="" border="0" src="https://www.paypal.com/en_CM/i/scr/pixel.gif" width="1" height="1"/>
    </form>
</div>-->
<script>
    let table = '';
    const GET_MESSAGES_NONCE = "<?php echo wp_create_nonce( 'get-blocked-messages' )?>";
    const DELETE_MESSAGE_NONCE = "<?php echo wp_create_nonce( 'can-delete-messages' )?>";
    const RESUBMIT_MESSAGE_NONCE = "<?php echo wp_create_nonce( 'can-resubmit-messages' )?>";
    const forms = <?php echo json_encode( $forms )?>;
    const all_registered_form_placeholder = "<?php _e( "All Registered Forms", KMCF7MS_TEXT_DOMAIN )?>"
    console.log(forms['cf7']);
    jQuery(function ($) {
        $(document).ready(function () {
            const registered_form_select = $("#km-registered-forms");
            $("#km-contact-forms").change((e) => {
                e.preventDefault();
                registered_form_select.empty();
                const selected_form = e.target.value;
                registered_form_select.append(`<option value="all">${all_registered_form_placeholder}</option>`)
                if (selected_form === 'all') {
                    for (const form in forms) {
                        for (const registered_form of forms[form]['forms']) {
                            registered_form_select.append(`<option value="${registered_form['id']}">${registered_form['name']}</option>`)
                        }
                    }
                } else {
                    const registered_forms = forms[selected_form]['forms']
                    console.log(registered_forms)
                    for (const registered_form of registered_forms) {
                        registered_form_select.append(`<option value="${registered_form['id']}">${registered_form['name']}</option>`)
                    }
                }
            })
            table = $("#km-table").DataTable({
                    dom: 'lBfrtip',
                    ordering: false,
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: '<?php echo admin_url( "admin-ajax.php?action=kmcf7_messages&form_id={$form_id}&contact_form={$selected_contact_form}" )?>' + "&_wpnonce=" + GET_MESSAGES_NONCE,
                        error: function (jqXHR, textStatus, errorThrown) {
                            let error_message = '';
                            // check if responseJSON is not empty
                            if (jqXHR.responseJSON != undefined && jqXHR.responseJSON.data != undefined)
                                error_message = jqXHR.responseJSON.data.toString()
                            else
                                error_message = 'An error occurred. Please try again later';
                            console.log("Message Filter Error " + error_message);
                            Swal.fire({
                                title: `Ooops!`,
                                icon: 'error',
                                text: error_message + '.\n Please contact support if this error persists.',
                            })
                        }
                    },
                    columnDefs: [{
                        orderable: false,
                        className: 'select-checkbox',
                        targets: 0
                    }],
                    buttons: [
                        // 'colvis',
                        {
                            extend: 'csv',
                            text: 'Download CSV'
                        },
                    ],
                    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                    select: true

                }
            );

            table.on('select', function () {
                $(".km-delete-btn").show()
            });
            table.on('deselect', function () {
                toggleDeleteBtn()
            });

            // column.visible(!column.visible());
            let cachedColumns = localStorage.getItem("<?php echo $selected_form?>")
            if (cachedColumns !== undefined && cachedColumns !== null) {
                cachedColumns = JSON.parse(cachedColumns)
            } else {
                cachedColumns = JSON.parse('<?php echo $columns?>');
            }
            Object.entries(cachedColumns).forEach((a) => {
                $("#input-" + a[0]).prop('checked', a[1].visible)
                const column = table.column(a[1].id);
                column.visible(a[1].visible);
            })

            function toggleDeleteBtn() {
                const selected_cells = table.rows({selected: true}).count()
                if (selected_cells > 0) {
                    $(".km-delete-btn").show()
                } else {
                    $(".km-delete-btn").hide()
                }
            }

            $(".table-column").on('click', function () {
                const value = $(this).attr('value');
                const name = $(this).attr('name');
                const column = table.column(value);

                // Toggle the visibility
                column.visible(!column.visible());
                const cachedColumnsInLocalStorage = localStorage.getItem("<?php echo $selected_form?>")
                if (cachedColumnsInLocalStorage !== undefined && cachedColumnsInLocalStorage !== null) {
                    localStorage.removeItem("<?php echo $selected_form?>")
                }
                cachedColumns[name] = {"id": value, visible: column.visible()}
                let formData = new FormData();
                formData.append("action", 'kmcf7_save_visible_columns');
                formData.append("form", '<?php echo $selected_form ?>');
                formData.append("columns", JSON.stringify(cachedColumns));
                fetch("<?php echo $ajax_url?>", {
                    method: 'POST',
                    body: formData
                })
            })

            $("#toggle-visible-columns-container").click(function (e) {
                e.preventDefault();
                $("#visible-columns-container").toggle(300)
            });
        })

    })

    function bootstrapSwal() {
        return Swal.mixin({
            customClass: {
                confirmButton: 'btn btn-success mr-2',
                cancelButton: 'btn btn-danger'
            },
            buttonsStyling: false
        });
    }

    function showResubmitModal(message_id = null) {
        let formData = new FormData();
        formData.append("action", 'kmcf7_resubmit_message');
        if (message_id == null)
            formData.append("message_ids", table.rows({selected: true}).data().toArray().map(a => a[2]).join(","));
        else
            formData.append("message_ids", message_id);

        bootstrapSwal().fire({
            title: 'Resubmit Message(s)',
            text: '<?php _e( "Resubmitting a message may not work if you have another spam filter or captcha plugin installed. We will not be able to bypass the verification process of these plugins.", KMCF7MS_TEXT_DOMAIN ) ?>',
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
                    text: '<?php  _e( "Message(s) resubmitted successfully", KMCF7MS_TEXT_DOMAIN )?>',
                }).then((result) => {
                    if (result.isConfirmed)
                        window.location.reload()
                })
            }
        })
    }

    function showDeleteModal() {
        let message_ids = []
        const data = table.rows('.selected').data()
        for (let i = 0; i < data.length; i++) {
            message_ids.push(data[i][2])
        }

        let formData = new FormData();
        formData.append("action", 'kmcf7_delete_message');
        formData.append("message_ids", message_ids);
        bootstrapSwal().fire({
            title: 'Delete Message(s)',
            text: '<?php _e( "Are you sure you want to delete the selected message(s)?", KMCF7MS_TEXT_DOMAIN ) ?>',
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
                /* Swal.fire({
					 title: `Delete Message(s)`,
					 icon: 'success',
					 text: '<?php  _e( "Message(s) deleted successfully", KMCF7MS_TEXT_DOMAIN )?>',
                }).then((result) => {
                    if (result.isConfirmed)*/
                window.location.reload()
                // })
            }
        })
    }


</script>
