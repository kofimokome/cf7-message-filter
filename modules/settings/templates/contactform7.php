<?php

namespace km_message_filter;
$tags = ContactForm7Module::getTags();
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
                persist: false,
                options: <?php echo json_encode( $tags['text'] )?>,
                create: function (input) {
                    return {
                        value: input,
                        text: input
                    }
                }
            });
            $('#kmcfmf_contact_form_7_textarea_fields').selectize({
                delimiter: ',',
                persist: false,
                options: <?php echo json_encode( $tags['textarea'] )?>,
                create: function (input) {
                    return {
                        value: input,
                        text: input
                    }
                }
            });
            $('#kmcfmf_contact_form_7_email_fields').selectize({
                delimiter: ',',
                persist: false,
                options: <?php echo json_encode( $tags['email'] )?>,
                create: function (input) {
                    return {
                        value: input,
                        text: input
                    }
                }
            });

        })
    </script>
<?php endif;
// $settings->run();