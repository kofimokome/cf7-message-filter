<?php

namespace kmcf7_message_filter;
?>
    <h1><?php esc_html_e( "Error Messages Settings", KMCF7MS_TEXT_DOMAIN ) ?></h1>
	<?php settings_errors(); ?>
    <form method="post" action="options.php">
		<?php

		settings_fields( 'kmcfmf_message_filter_messages' );
		do_settings_sections( 'kmcf7-message-filter-options&tab=messages' );

		submit_button();
		?>
    </form>
<?php
// $settings->run();