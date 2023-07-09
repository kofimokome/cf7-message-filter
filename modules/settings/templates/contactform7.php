<?php

namespace km_message_filter;
$tags                 = ContactForm7Module::getTags();
$text_fields          = $tags['text'];
$selected_text_fields = get_option( 'kmcfmf_tags_by_name' );
$selected_text_fields = explode( ',', $selected_text_fields );
$selected_text_fields = array_map( function ( $e ) {
	return array( 'text' => $e, 'value' => $e );
}, $selected_text_fields );
$text_fields          = array_merge( $text_fields, $selected_text_fields );

$textarea_fields          = $tags['textarea'];
$selected_textarea_fields = get_option( 'kmcfmf_contact_form_7_textarea_fields' );
$selected_textarea_fields = explode( ',', $selected_textarea_fields );
$selected_textarea_fields = array_map( function ( $e ) {
	return array( 'text' => $e, 'value' => $e );
}, $selected_textarea_fields );
$textarea_fields          = array_merge( $textarea_fields, $selected_textarea_fields );

$email_fields          = $tags['email'];
$selected_email_fields = get_option( 'kmcfmf_contact_form_7_email_fields' );
$selected_email_fields = explode( ',', $selected_email_fields );
$selected_email_fields = array_map( function ( $e ) {
	return array( 'text' => $e, 'value' => $e );
}, $selected_email_fields );
$email_fields          = array_merge( $email_fields, $selected_email_fields );

$contact_forms = ContactForm7Module::getForms();
?>
    <h1><?php esc_html_e( "Contact Form 7 Settings ", KMCF7MS_TEXT_DOMAIN ) ?></h1>
	<?php if ( ! is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ): ?>
    <div class="alert alert-danger alert-dismissible">
        <p><?php esc_html_e( 'Please Install & Activate Contact Form 7 Plugin First!', KMCF7MS_TEXT_DOMAIN ); ?></p>
    </div>
<?php else: ?>
	<?php settings_errors(); ?>
    <div>
        <strong>Tip:</strong> <?php _e( "Use <code>*</code> to analyse all fields for each category below:", KMCF7MS_TEXT_DOMAIN ) ?>
    </div>
    <form method="post" action="options.php">
		<?php

		settings_fields( 'kmcfmf_contact_form_7' );
		do_settings_sections( 'kmcf7-message-filter-options&tab=contactform7' );

		submit_button();
		?>
    </form>
    <script>
        jQuery(document).ready(function ($) {
            $('#kmcfmf_tags_by_name').selectize({
                delimiter: ',',
                persist: true,
                options: <?php echo json_encode( $text_fields )?>,
                create: function (input) {
                    return {
                        value: input,
                        text: input
                    }
                }
            });
            $('#kmcfmf_contact_form_7_textarea_fields').selectize({
                delimiter: ',',
                persist: true,
                options: <?php echo json_encode( $textarea_fields )?>,
                create: function (input) {
                    return {
                        value: input,
                        text: input
                    }
                }
            });
            $('#kmcfmf_contact_form_7_email_fields').selectize({
                delimiter: ',',
                persist: true,
                options: <?php echo json_encode( $email_fields )?>,
                create: function (input) {
                    return {
                        value: input,
                        text: input
                    }
                }
            });
            const filter_forms = $('#kmcfmf_contact_form_7_filter_forms');
            /*const filter_type = $('#kmcfmf_contact_form_7_filter_type');
            filter_type.change((e) => {
                e.preventDefault();
                console.log(e.target.value)
                if (e.target.value != '') {
                    filter_forms.show();

                }
            })
            filter_forms.hide();
*/
            filter_forms.selectize({
                delimiter: ',',
                persist: true,
                options: <?php echo json_encode( $contact_forms )?>,
            });
        })
    </script>
<?php endif;
// $settings->run();