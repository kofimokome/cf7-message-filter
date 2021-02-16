<?php

namespace kmcf7_message_filter;

echo "<pre>";
print_r(BlockedMessage::get_forms());
// die();
echo "</pre>";
$pagination = isset($_GET['pagination']) ? (int)$_GET['pagination'] : 0;

if ($pagination <= 0) {
    $pagination = 1;
}

$start = 0;
$end = -1;
$number_per_page = 10; // per page

// $messages = explode("]kmcfmf_message[", get_option('kmcfmf_messages'));
$messages = (array)json_decode(file_get_contents(CF7MessageFilter::get_log_file_path()));
function decodeUnicodeVars($message)
{
    return mb_convert_encoding($message, 'UTF-8',
        mb_detect_encoding($message, 'UTF-8, ISO-8859-1', true));
}


$messages = array_reverse($messages, false);
$size = sizeof($messages);
if (($pagination * $number_per_page) > $size && (($pagination * $number_per_page) - $number_per_page) < $size) {
    $start = (($pagination * $number_per_page) - ($number_per_page));
    $end = ($size);

} elseif (($pagination * $number_per_page) <= $size) {
    $start = (($pagination * $number_per_page) - ($number_per_page));
    $end = ($pagination * $number_per_page);
}

// echo "<br>we will search from " . $start . " to " . ( $end - 1 ) . "<br>";
?>
    <h3><?php echo get_option('kmcfmf_messages_blocked'); ?> messages have been blocked</h3>
    here is a select :<select name="" id="" class="form-control">
    <option value="">Select a form</option>
    <?php foreach (BlockedMessage::get_forms() as $form): ?>
        <option value="<?php echo $form[1] ?>"><?php echo $form[0] ?></option>
    <?php endforeach; ?>
</select>
    <button class="btn btn-primary btn-inline">Show Blocked Messages</button>
    <table class="kmcfmf_table table table-striped">
        <tr>
            <td><b>S/N</b></td>
            <td>
                <b>Time</b>
            </td>
            <td>
                <b>Email</b>
            </td>
            <td>
                <b>Message</b>
            </td>
        </tr>
        <?php

        for ($i = $start; $i < $end; $i++) {
            $data = $messages[$i];
            echo "<tr>";
            echo "<td>" . ($i + 1) . "</td>";
            echo "<td>" . $data->date . "</td>";
            echo "<td>" . $data->email . "</td>";
            echo "<td>" . decodeUnicodeVars($data->message) . "</td>";
            //echo $i . " message: " . $data[1] . " email: " . $data[2] . " time: " . $data[3] . "<br>";
            echo "</tr>";

        }
        ?>
    </table>
    <br>
    <?php
if ($pagination > 1) {
    echo "<a href='?page=kmcf7-filtered-messages&pagination=" . ($pagination - 1) . "' class='button button-primary'> < Prev page</a>";
}
if (((($pagination + 1) * $number_per_page) - $number_per_page) < $size) {
    echo " <a href='?page=kmcf7-filtered-messages&pagination=" . ($pagination + 1) . "' class='button button-primary'> Next page > </a>";
}