<?php

namespace kmcf7_message_filter;
?>
    <h1>Basic Plugin Settings</h1>
    <?php settings_errors(); ?>
    <form method="post" action="options.php" id="basic_settings_form">
        <?php

        settings_fields('kmcfmf_message_filter_basic');
        do_settings_sections('kmcf7-message-filter-options&tab=basic');

        submit_button();
        ?>
    </form>
    <script>
        jQuery(document).ready(function ($) {
            const fields = ['kmcfmf_restricted_words', 'kmcfmf_restricted_emails', 'kmcfmf_tags_by_name'];
            let update_form = false;
            fields.forEach((val) => {
                const restricted_words = $("#" + val).val().trim();
                if ((/\s+/).test(restricted_words)) {
                    $("#" + val).val(restricted_words.replaceAll(/ +/g, ' ').replaceAll(/\s+/g, ',').replaceAll(/,+/g, ','));
                    update_form = true;
                }
            });
            // alert(update_form);
            if (update_form) {
                $("#submit").click();
            }
            $('.select2').selectize({
                delimiter: ',',
                persist: false,
                create: function (input) {
                    return {
                        value: input,
                        text: input
                    }
                }
            });

        })
    </script>
<?php
// $settings->run();