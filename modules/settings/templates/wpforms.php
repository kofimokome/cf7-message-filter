<?php

namespace km_message_filter;
$tags = ContactForm7Module::getTags();
?>
    <h1><?php esc_html_e( "WP Forms Settings ", KMCF7MS_TEXT_DOMAIN ) ?></h1>

	<?php settings_errors(); ?>
    <div>
        <strong>Tip:</strong> <?php _e( "Use <code>*</code> to analyse all fields for each category below:", KMCF7MS_TEXT_DOMAIN ) ?>
    </div>
    <form method="post" action="options.php">
		<?php

		settings_fields( 'kmcfmf_wp_forms' );
		do_settings_sections( 'kmcf7-message-filter-options&tab=wpforms' );

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
<?php
// $settings->run();