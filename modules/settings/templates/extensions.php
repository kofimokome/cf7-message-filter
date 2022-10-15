<?php

namespace km_message_filter;
$extensions       = apply_filters( 'kmcf7_extensions', array() );
$link_to_messages = admin_url( 'admin.php' ) . '?page=kmcf7-message-filter-options&tab=messages';

?>
<h2><?php _e( "Extensions", KMCF7MS_TEXT_DOMAIN ) ?> </h2>

<?php _e( "The following extensions are available:", KMCF7MS_TEXT_DOMAIN ) ?>
<div class="card">
    <table class="table table-striped">
        <tr>
            <td>
                <h2><?php _e( "Hide Error Messages (Free Trial)", KMCF7MS_TEXT_DOMAIN ) ?></h2>
				<?php _e( "This extension hides the error message and shows a success message if a spam is found in the submitted form", KMCF7MS_TEXT_DOMAIN ) ?>
            </td>
            <td>
				<?php if ( in_array( 'hide_error_messages', $extensions ) ) { ?>
                    <a class="button"
                       href="<?php echo $link_to_messages ?>"><?php _e( "Configure", KMCF7MS_TEXT_DOMAIN ) ?></a>
				<?php } else { ?>
                    <a class="button"
                       href="https://kofimokome.stream"
                       target="_blank"><?php _e( "Add Extension", KMCF7MS_TEXT_DOMAIN ) ?></a>
				<?php } ?>
            </td>
        </tr>
    </table>
</div>
