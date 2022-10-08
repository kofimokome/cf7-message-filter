<?php

namespace kmcf7_message_filter;
?>
    <h1><?php esc_html_e( "Contact Form 7 Settings ", KMCF7MS_TEXT_DOMAIN ) ?></h1>

	<?php settings_errors(); ?>
    <div>
        <strong>Tip:</strong> <?php _e( "Use <code>*</code> to analyse all fields for each category below:", KMCF7MS_TEXT_DOMAIN ) ?>
    </div>
    <form method="post" action="options.php">
		<?php

		settings_fields( 'kmcfmf_message_filter_contact_form_7' );
		do_settings_sections( 'kmcf7-message-filter-options&tab=contactform7' );

		submit_button();
		?>
    </form>
    <script>
        jQuery(document).ready(function ($) {
            $('.select2').selectize({
                delimiter: ',',
                persist: false,
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