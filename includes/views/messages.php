<?php

namespace kmcf7_message_filter;

$pagination = isset($_GET['pagination']) ? (int)$_GET['pagination'] : 0;

if ($pagination <= 0) {
    $pagination = 1;
}

$start = 0;
$end = -1;
$number_per_page = 10; // per page
$form_id = isset($_GET['form']) ? $_GET['form'] : 0;
$form_id = $form_id == '' ? -1 : intval($form_id);

function decodeUnicodeVars($message)
{
    return mb_convert_encoding($message, 'UTF-8',
        mb_detect_encoding($message, 'UTF-8, ISO-8859-1', true));
}

// echo "<br>we will search from " . $start . " to " . ( $end - 1 ) . "<br>";
?>
    <h3><?php echo get_option('kmcfmf_messages_blocked'); ?> messages have been blocked</h3>
    <form action="" class="form-inline">
        <input type="hidden" name="page" value="kmcf7-filtered-messages">
        <select name="form" id="" class="form-control">
            <option value="">Select a form</option>
            <?php foreach (BlockedMessage::get_forms() as $form): ?>
                <option value="<?php echo $form[1] ?>" <?php echo $form_id == $form[1] ? 'selected' : '' ?>><?php echo $form[0] ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-primary btn-inline ml-1">Show Blocked Messages</button>

    </form>
    <?php if ($form_id >= 0) {
    $rows = BlockedMessage::get_rows($form_id) ?>
    <table class="kmcfmf_table table table-striped">
        <tr>
            <td><b>S/N</b></td>
            <?php foreach ($rows as $row): ?>
                <td>
                    <b><?php echo $row ?></b>
                </td>
            <?php endforeach; ?>
<!--            <td>-->
<!--                actions-->
<!--            </td>-->
        </tr>
        <?php
        $messages = BlockedMessage::get_columns($form_id);
        $messages = array_reverse($messages, false);
        $size = sizeof($messages);
        if (($pagination * $number_per_page) > $size && (($pagination * $number_per_page) - $number_per_page) < $size) {
            $start = (($pagination * $number_per_page) - ($number_per_page));
            $end = ($size);

        } elseif (($pagination * $number_per_page) <= $size) {
            $start = (($pagination * $number_per_page) - ($number_per_page));
            $end = ($pagination * $number_per_page);
        }
        for ($i = $start; $i < $end; $i++) {
            $data = $messages[$i];
            echo "<tr>";
            echo "<td>" . ($i + 1) . "</td>";
            foreach ($rows as $row) {
                if (property_exists($data, $row)) {
                    echo "<td>" . decodeUnicodeVars($data->$row) . "</td>";
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
    if ($pagination > 1) {
        echo "<a href='?page=kmcf7-filtered-messages&form=" . $form_id . "&pagination=" . ($pagination - 1) . "' class='button button-primary'> < Prev page</a>";
    }
    if (((($pagination + 1) * $number_per_page) - $number_per_page) < $size) {
        echo " <a href='?page=kmcf7-filtered-messages&form=" . $form_id . "&pagination=" . ($pagination + 1) . "' class='button button-primary'> Next page > </a>";
    }
} else { ?>
    <div class="jumbotron">
        <h2 class="display-5d">Blocked Messages Area</h2>
        <p class="lead">Messages are now grouped per form. Select a form above to view all messages blocked for that
            form.</p>
        <p class="lead">If you upgraded from a previous version, all old messages blocked are stored under
            uncategorized.</p>
        <hr class="my-4">
    </div>
    <?php
}
