<?php

namespace km_message_filter;

$pagination = isset( $_GET['pagination'] ) ? (int) $_GET['pagination'] : 0;

if ( $pagination <= 0 ) {
	$pagination = 1;
}

$start           = 0;
$end             = - 1;
$number_per_page = 10; // per page

$selected_form = isset( $_GET['form'] ) ? sanitize_text_field( $_GET['form'] ) : '';
$data          = explode( '-', $selected_form );
$form_id       = - 1;
$contact_form     = '';
if ( sizeof( $data ) > 1 ) {
	$contact_form = trim( $data[0] );
	$form_id   = $data[1];
	$form_id   = $form_id == '' ? - 1 : intval( $form_id );
}


// echo "<br>we will search from " . $start . " to " . ( $end - 1 ) . "<br>";
?>
    <style>
        #wpbody-content {
            overflow-x: scroll;
        }
    </style>
    <h3><?php echo get_option( 'kmcfmf_messages_blocked' ); ?> <?php _e( "messages have been blocked", KMCF7MS_TEXT_DOMAIN ) ?></h3>
    <form action="" class="form-inline">
        <input type="hidden" name="page" value="kmcf7-filtered-messages">
        <select name="form" id="" class="form-control form-control-sm">
            <option value=""><?php _e( "Select a form", KMCF7MS_TEXT_DOMAIN ) ?></option>
			<?php foreach ( MessagesModule::getForms() as $form ): ?>
                <option value="<?php echo $form[1] ?>" <?php echo $selected_form == $form[1] ? 'selected' : '' ?>><?php echo $form[0] ?></option>
			<?php endforeach; ?>
        </select>
        <input type="hidden" name="old" value="">

        <button class="btn btn-primary btn-inline ml-1 btn-sm"><?php _e( "Show Blocked Messages", KMCF7MS_TEXT_DOMAIN ) ?></button>

    </form>
    <!--<button class="btn btn-primary">Export to CSV</button>-->
	<?php if ( $form_id >= 0 && $contact_form == 'cf7' ) {
	$rows = MessagesModule::getRows( $form_id ) ?>
    <table class="kmcfmf_table table table-striped">
        <tr>
            <td><b>S/N</b></td>
			<?php foreach ( $rows as $row ): ?>
                <td>
                    <b><?php echo htmlspecialchars( strip_tags( $row ) ) ?></b>
                </td>
			<?php endforeach; ?>
            <!--            <td>-->
            <!--                actions-->
            <!--            </td>-->
        </tr>
		<?php
		$messages = MessagesModule::getColumns( $form_id );
		$messages = array_reverse( $messages, false );
		$size     = sizeof( $messages );
		if ( ( $pagination * $number_per_page ) > $size && ( ( $pagination * $number_per_page ) - $number_per_page ) < $size ) {
			$start = ( ( $pagination * $number_per_page ) - ( $number_per_page ) );
			$end   = ( $size );

		} elseif ( ( $pagination * $number_per_page ) <= $size ) {
			$start = ( ( $pagination * $number_per_page ) - ( $number_per_page ) );
			$end   = ( $pagination * $number_per_page );
		}
		for ( $i = $start; $i < $end; $i ++ ) {
			$data = $messages[ $i ];
			echo "<tr>";
			echo "<td>" . ( $i + 1 ) . "</td>";
			foreach ( $rows as $row ) {
				if ( property_exists( $data, $row ) ) {
					echo "<td>" . htmlspecialchars( strip_tags( MessagesModule::decodeUnicodeVars( $data->$row ) ) ) . "</td>";
				} else {
					echo "<td> </td>";
				}
			}
//            echo "<td><button class='btn btn-primary'>resubmit</button></td>";
			echo "</tr>";

		}
		?>
    </table>
    <br>
	<?php
	if ( $pagination > 1 ) {
		echo "<a href='?page=kmcf7-filtered-messages&form=" . $selected_form . "&pagination=" . ( $pagination - 1 ) . "&old' class='button button-primary'> < Prev page</a>";
	}
	if ( ( ( ( $pagination + 1 ) * $number_per_page ) - $number_per_page ) < $size ) {
		echo " <a href='?page=kmcf7-filtered-messages&form=" . $selected_form . "&pagination=" . ( $pagination + 1 ) . "&old' class='button button-primary'> Next page > </a>";
	}
} else { ?>
    <div class="jumbotron">
        <h2 class="display-5d"> <?php _e( "Blocked Messages Area", KMCF7MS_TEXT_DOMAIN ) ?></h2>
        <p class="lead"><?php _e( "Messages are now grouped per form. Select a form above to view all messages blocked for that
            form.", KMCF7MS_TEXT_DOMAIN ) ?></p>
        <p class="lead"><?php _e( "If you upgraded from a previous version, all old messages blocked are stored under
            uncategorized.", KMCF7MS_TEXT_DOMAIN ) ?></p>
        <hr class="my-4">
    </div>
	<?php
}
