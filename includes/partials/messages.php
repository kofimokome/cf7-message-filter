<?php

namespace kmcf7_message_filter;

$pagination = (int)$_GET['pagination'];

if ($pagination <= 0) {
    $pagination = 1;
}

$start = 0;
$end = -1;
$number_per_page = 10;

// $messages = explode("]kmcfmf_message[", get_option('kmcfmf_messages'));
$messages = (array)json_decode(file_get_contents($this->log_file));
$messages = array_reverse($messages, false);
$size = (sizeof($messages) - 1);
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
    <div class="alert alert-info alert-dismissible">
        <p><b>Note: </b> If you can't view your messages after an update, please reset the plugin from the settings/options page
        </p>
    </div>
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
        for ($i = $start; $i <= $end; $i++) {
            $data = $messages[$i];
            echo "<tr>";
            echo "<td>" . ($i + 1) . "</td>";
            echo "<td>" . $data->date . "</td>";
            echo "<td>" . $data->email . "</td>";
            echo "<td>" . $data->message . "</td>";
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