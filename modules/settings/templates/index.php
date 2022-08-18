<?php

namespace kmcf7_message_filter;
?>
    <h2><?php _e( "Advanced Settings", KMCF7MS_TEXT_DOMAIN ) ?> </h2>
	<?php settings_errors(); ?>
    <form method="post" action="options.php">
		<?php

		settings_fields( 'bcs-settings' );
		do_settings_sections( 'bcs-settings' );

		submit_button();
		?>
    </form>
<?php