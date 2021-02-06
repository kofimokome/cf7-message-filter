<?php

namespace kmcf7_message_filter;
?>
    <h1>Basic Plugin Settings</h1>
    <?php settings_errors(); ?>
    <form method="post" action="options.php">
        <?php

        settings_fields('kmcfmf_message_filter_basic');
        do_settings_sections('kmcf7-message-filter-options&tab=basic');

        submit_button();
        ?>
    </form>
<?php
// $settings->run();