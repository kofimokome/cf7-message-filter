<?php

namespace kmcf7_message_filter;
?>
    <h2>Advanced Settings </h2>
    These settings will work only when auto delete is activated
    <h2 style="color: red">Note: These are experimental features. Please <a href="https://github.com/kofimokome/cf7-message-filter/discussions/9" target="_blank">submit any feedback here</a></h2>
    <?php settings_errors(); ?>
    <form method="post" action="options.php">
        <?php

        settings_fields('kmcfmf_message_filter_advanced');
        do_settings_sections('kmcf7-message-filter-options&tab=advanced');

        submit_button();
        ?>
    </form>
<?php
// $settings->run();