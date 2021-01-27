<?php

namespace kmcf7_message_filter;
?>
    <h2>Please enter each word separated by white-spaces (spaces, newline, etc.) or comma in the boxes
        below kofi</h2>
    <?php settings_errors(); ?>
    <form method="post" action="options.php">
        <?php

        settings_fields('kmcfmf_message_filter_words');
        do_settings_sections('kmcf7-message-filter-options&tab=words');

        submit_button();
        ?>
    </form>
<?php
// $settings->run();