<?php

namespace kmcf7_message_filter;

use WPCF7_ContactForm;

$pagination           = isset( $_GET['pagination'] ) ? (int) $_GET['pagination'] : 0;
$link_to_old_messages = admin_url( 'admin.php' ) . '?page=kmcf7-filtered-messages&old';

if ( $pagination <= 0 ) {
	$pagination = 1;
}
$number_per_page = 10; // per page
$form_id         = isset( $_GET['form'] ) ? $_GET['form'] : 0;
$form_id         = $form_id == '' ? - 1 : intval( $form_id );

function decodeUnicodeVars( $message ) {
	$message = is_array( $message ) ? implode( " ", $message ) : $message;

	return mb_convert_encoding( $message, 'UTF-8',
		mb_detect_encoding( $message, 'UTF-8, ISO-8859-1', true ) );
}

?>
    <style>
        #wpbody-content {
            overflow-x: scroll;
        }
    </style>
    <h3><?php echo get_option( 'kmcfmf_messages_blocked' ); ?> messages have been blocked</h3>

    <form action="" class="form-inline">
        <input type="hidden" name="page" value="kmcf7-filtered-messages">
        <select name="form" id="" class="form-control form-control-sm">
            <option value="">Select a form</option>
			<?php foreach ( MessagesModule::getForms() as $form ): ?>
                <option value="<?php echo $form[1] ?>" <?php echo $form_id == $form[1] ? 'selected' : '' ?>><?php echo $form[0] ?></option>
			<?php endforeach; ?>
        </select>
        <button class="btn btn-primary btn-inline ml-1 btn-sm">Show Blocked Messages</button>

    </form>
    <!--<button class="btn btn-primary">Export to CSV</button>-->
	<?php if ( $form_id >= 0 ) {
	$contact_form = WPCF7_ContactForm::get_instance( $form_id );
	$rows         = $contact_form->scan_form_tags();
	?>
    <table class="kmcfmf_table table table-striped">
        <tr>
            <td><b>S/N</b></td>
			<?php foreach ( $rows as $row ): $row = $row->name ?>
                <td>
                    <b><?php echo $row ?></b>
                </td>
			<?php endforeach; ?>
            <!--            <td>-->
            <!--                actions-->
            <!--            </td>-->
        </tr>
		<?php
		$results  = Message::where( 'contact_form', '=', 'contact_form_7' )->where( 'form_id', '=', $form_id )->orderBy( 'id', 'desc' )->paginate( $number_per_page, $pagination )->get();
		$messages = $results['data'];
		$size     = $results['totalPages'];

		foreach ( $messages as $message ) {
			$data = json_decode( $message->message );
			echo "<tr>";
			echo "<td>" . ( $message->id ) . "</td>";
			foreach ( $rows as $row ) {
				$row = $row->name;
				if ( property_exists( $data, $row ) ) {
					echo "<td>" . htmlspecialchars( strip_tags( decodeUnicodeVars( $data->$row ) ) ) . "</td>";
				} else {
					echo "<td> </td>";
				}
			}
			echo "</tr>";

		}
		?>
    </table>
    <br>
	<?php
	if ( $pagination > 1 ) {
		echo "<a href='?page=kmcf7-filtered-messages&form=" . $form_id . "&pagination=" . ( $pagination - 1 ) . "' class='button button-primary'> < Prev page</a>";
	}
	if ( ( $pagination + 1 ) <= $size ) {
		echo " <a href='?page=kmcf7-filtered-messages&form=" . $form_id . "&pagination=" . ( $pagination + 1 ) . "' class='button button-primary'> Next page > </a>";
	}
} else { ?>
    <div class="jumbotron">
		<?php if ( is_file( MessagesModule::getLogFile() ) ): ?>
            <div class="mb-2 border-info">
                <h5>Note: Message storage location has changed.
                    <a href="<?php echo $link_to_old_messages ?>" class="btn btn-primary btn-sm">
                        Click here to view your old messages
                    </a>
                </h5>
            </div>
		<?php endif; ?>
        <h2 class="display-5d">Blocked Messages Area</h2>
        <p class="lead">Messages are now grouped per form. Select a form above to view all messages blocked for that
            form.</p>
        <p class="lead">If you upgraded from a previous version, all old messages blocked are stored under
            uncategorized.</p>

        <hr class="my-4">
    </div>
	<?php
}
