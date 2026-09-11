<?php

namespace km_message_filter;
$extensions       = apply_filters( 'kmcf7_extensions', array() );
$link_to_messages = admin_url( 'admin.php' ) . '?page=kmcf7-message-filter-options&tab=messages';

?>
<h2><?php _e( "Extensions", 'cf7-message-filter' ) ?> </h2>

<?php _e( "The following extensions are available:", 'cf7-message-filter' ) ?>
<div class="card">
    <table class="table table-striped">
        <tr>
            <td>
                <h2><?php _e( "Hide Error Messages (Free Trial)", 'cf7-message-filter' ) ?></h2>
				<?php _e( "This extension hides the error message and shows a success message if a spam is found in the submitted form", 'cf7-message-filter' ) ?>
            </td>
            <td>
				<?php if ( in_array( 'hide_error_messages', $extensions ) ) { ?>
                    <a class="button"
                       href="<?php echo $link_to_messages ?>"><?php _e( "Configure", 'cf7-message-filter' ) ?></a>
				<?php } else { ?>
                    <a class="button"
                       href="https://kofimokome.stream"
                       target="_blank"><?php _e( "Add Extension", 'cf7-message-filter' ) ?></a>
				<?php } ?>
            </td>
        </tr>
    </table>
</div>
