<?php

namespace km_message_filter;
$plugin_instance = KMCFMessageFilter::getInstance();

global $wp_version;

function parseDate( $time ) {
	if ( $time == 0 ) {
		return 'Never';
	}

	return date( 'Y-m-d H:i:s', $time );
}

$can_sync = get_option( 'kmcfmf_enable_collection', '' ) == 'on'; // for users who did not accept the freemius policy after installation


$debug_info = [
	'spam_words'  => sanitize_text_field( get_option( 'kmcfmf_restricted_words', '' ) ),
	'spam_emails' => sanitize_text_field( get_option( 'kmcfmf_restricted_emails', '' ) ),

	'is_contact_form_7_filter_enabled' => get_option( 'kmcfmf_enable_contact_form_7_toggle' ) == 'on' ? 'Yes' : "No",
	'is_spam_filter_enabled'           => get_option( 'kmcfmf_email_filter_toggle' ) == 'on' ? 'Yes' : "No",
	'is_message_filter_enabled'        => get_option( 'kmcfmf_message_filter_toggle' ) == 'on' ? 'Yes' : "No",
	'is_wp_forms_filter_enabled'       => get_option( 'kmcfmf_enable_wp_forms_toggle' ) == 'on' ? 'Yes' : "No",
	'is_sync_allowed'                  => $can_sync ? 'Yes' : 'No',

	'plugin_version'                => $plugin_instance->getVersion(),
	'contact_form_7_version' => defined( 'WPCF7_VERSION' ) ? WPCF7_VERSION : '',
	'wp_forms_version'       => defined( 'WPFORMS_VERSION' ) ? WPFORMS_VERSION : '',
	'wordpress_version'      => $wp_version,
	'php_version'            => phpversion(),

];

if ( $can_sync ) {
	$max_id         = '';
	$last_id_synced = get_option( 'kmcfmf_collection_last_id_synced', 0 );
	$result         = Message::select( 'max(id) as max_id' )->first();
	if ( $result ) {
		$max_id = $result->max_id;
	}
	$progress = round( ( $last_id_synced / $max_id ) * 100, 2 );

	$sync_status = get_option( 'kmcfmf_collection_status', 'not_running' );
	$debug_info  = array_merge( $debug_info, [
		'sync_interval' => get_option( 'kmcfmf_collection_interval', 7 ) . ' days',
		'sync_status'   => $sync_status,
		'last_sync'     => parseDate( get_option( 'kmcfmf_collection_last_sync', 0 ) ),
		'next_sync'     => parseDate( get_option( 'kmcfmf_collection_next_sync', 0 ) ),

	] );

	if ( $sync_status == 'running' ) {
		$debug_info['syncing']              = get_option( 'kmcfmf_collection_syncing_now', 'spam_words' );
		$debug_info['failed_sync_attempts'] = get_option( 'kmcfmf_collection_retries', 0 );
		$debug_info['sync_progress']        = $last_id_synced . ' (' . $progress . '%)';
	}
}
?>
    <h1><?php esc_html_e( "Debug Settings ", KMCF7MS_TEXT_DOMAIN ) ?></h1>
    <div>

		<?php _e( "Please copy the information below and send to the support team if asked.", KMCF7MS_TEXT_DOMAIN ) ?>
    </div>

    <button onclick="copyInfo()" class="button button-primary"><?php _e( "Copy text", KMCF7MS_TEXT_DOMAIN ) ?></button>

    <div style="overflow:scroll">
    <pre onclick="copyInfo()" style="cursor:pointer">
        <?php echo print_r( $debug_info ) ?>
    </pre>
    </div>
    <textarea id="myInput" style="display:none" readonly>
        <?php echo json_encode( $debug_info ) ?>
    </textarea>

    <!-- The button used to copy the text -->
    <button onclick="copyInfo()" class="button button-primary"><?php _e( "Copy text", KMCF7MS_TEXT_DOMAIN ) ?></button>

    <script>
        function copyInfo() {
            // Get the text field
            const copyText = document.getElementById("myInput");

            // Select the text field
            copyText.focus();

            copyText.select();
            copyText.setSelectionRange(0, 99999); // For mobile devices

            navigator.clipboard.writeText(copyText.value);
            alert('<?php _e( "Text copied", KMCF7MS_TEXT_DOMAIN ) ?>')
        }
    </script>
<?php
// $settings->run();

