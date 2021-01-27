<?php

namespace kmcf7_message_filter;
?>
    <h2>Displayed when someone tries to submit a message marked as spam </h2>
    <?php settings_errors(); ?>
    <form method="post" action="options.php">
        <?php

        settings_fields('kmcfmf_message_filter_messages');
        do_settings_sections('kmcf7-message-filter-options&tab=messages');

        submit_button();
        ?>
    </form>
<?php
// $settings->run();