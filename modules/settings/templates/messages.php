<?php

namespace km_message_filter;
?>
    <h1><?php esc_html_e( "Error Messages Settings", KMCF7MS_TEXT_DOMAIN ) ?></h1>
	<?php settings_errors(); ?>
	<?php if ( ! KMCFMFs()->is_premium() || ! KMCFMFs()->is_plan_or_trial( 'pro' ) ): ?>
    <h2 style="color: red"><?php _e( "Note: Please upgrade to pro to edit the fields below.", KMCF7MS_TEXT_DOMAIN ) ?></h2>
<?php endif; ?>
    <form method="post" action="options.php">
		<?php

		settings_fields( 'kmcfmf_messages' );
		do_settings_sections( 'kmcf7-message-filter-options&tab=messages' );

		submit_button();
		?>
    </form>
<?php
// $settings->run();