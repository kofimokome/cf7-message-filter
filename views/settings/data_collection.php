<?php

namespace km_message_filter;
?>
    <h1><?php esc_html_e( "Data Collection Settings ", 'cf7-message-filter' ) ?></h1>
    <div>

		<?php _e( "To improve this plugin, we collect data on words in your spam list and spam messages blocked by the plugin. You can enable/disable this data collection here.", 'cf7-message-filter' ) ?>
    </div>
    <div>
        <strong><?php _e( "Note: We do not collect your private information", 'cf7-message-filter' ) ?></strong>

    </div>
	<?php settings_errors(); ?>
    <form method="post" action="options.php" id="data_collection_form">
		<?php

		settings_fields( 'kmcfmf_data_collection' );
		do_settings_sections( 'kmcf7-message-filter-options&tab=data_collection' );

		submit_button();
		?>
    </form>
<?php
// $settings->run();