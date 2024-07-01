<?php

namespace km_message_filter;

$wp_forms_module = WpFormsModule::getInstance();
$tags            = $wp_forms_module->getTags();

$text_fields          = $tags['text'];
$selected_text_fields = get_option( 'kmcfmf_wp_forms_text_fields' );
$selected_text_fields = explode( ',', $selected_text_fields );
$selected_text_fields = array_map( function ( $e ) {
	return array( 'text' => $e, 'value' => $e );
}, $selected_text_fields );
$text_fields          = array_merge( $text_fields, $selected_text_fields );

$textarea_fields          = $tags['textarea'];
$selected_textarea_fields = get_option( 'kmcfmf_wp_forms_textarea_fields' );
$selected_textarea_fields = explode( ',', $selected_textarea_fields );
$selected_textarea_fields = array_map( function ( $e ) {
	return array( 'text' => $e, 'value' => $e );
}, $selected_textarea_fields );
$textarea_fields          = array_merge( $textarea_fields, $selected_textarea_fields );

$email_fields          = $tags['email'];
$selected_email_fields = get_option( 'kmcfmf_wp_forms_email_fields' );
$selected_email_fields = explode( ',', $selected_email_fields );
$selected_email_fields = array_map( function ( $e ) {
	return array( 'text' => $e, 'value' => $e );
}, $selected_email_fields );
$email_fields          = array_merge( $email_fields, $selected_email_fields );

$contact_forms = $wp_forms_module->getForms();
$tag_ui        = get_option( 'kmcfmf_tag_ui', 'new_ui' );

?>
    <h1><?php esc_html_e( "WP Forms Settings ", KMCF7MS_TEXT_DOMAIN ) ?></h1>
	<?php /*if ( ! is_plugin_active( 'wpforms-lite/wpforms.php' ) && ! is_plugin_active( 'wpforms/wpforms.php' ) ): */
	?><!--
    <div class="alert alert-danger alert-dismissible">
        <p><?php /*esc_html_e( 'Please Install & Activate WPForms Plugin First!', KMCF7MS_TEXT_DOMAIN ); */
	?></p>
    </div>
--><?php /*else: */
?>
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
			<?php if($tag_ui == 'old_ui'):?>

            $('#kmcfmf_wp_forms_text_fields').selectize({
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
            $('#kmcfmf_wp_forms_textarea_fields').selectize({
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
            $('#kmcfmf_wp_forms_email_fields').selectize({
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
			<?php elseif($tag_ui == 'new_ui'):?>
            new KmTag('#kmcfmf_wp_forms_text_fields', {
                delimiter: ',',
                withModifiers: false
            });
            new KmTag('#kmcfmf_wp_forms_textarea_fields', {
                delimiter: ',',
                withModifiers: false
            });
            new KmTag('#kmcfmf_wp_forms_email_fields', {
                delimiter: ',',
                withModifiers: false
            });
			<?php endif;?>

            const filter_forms = $('#kmcfmf_wp_forms_filter_forms');
            filter_forms.selectize({
                delimiter: ',',
                persist: true,
                options: <?php echo json_encode( $contact_forms )?>,
            });

        })
    </script>
<?php
