<?php

namespace kmcf7_message_filter;
?>
    <h1><?php _e( "Basic Plugin Settings", KMCF7MS_TEXT_DOMAIN ) ?></h1>
	<?php settings_errors(); ?>
    <form method="post" action="options.php" id="basic_settings_form">
		<?php

		settings_fields( 'kmcfmf_message_filter_basic' );
		do_settings_sections( 'kmcf7-message-filter-options&tab=basic' );

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