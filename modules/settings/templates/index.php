<?php

?>
    <h2>Advanced Settings </h2>
<?php settings_errors(); ?>
    <form method="post" action="options.php">
		<?php

		settings_fields( 'bcs-settings' );
		do_settings_sections( 'bcs-settings' );

		submit_button();
		?>
    </form>
<?php