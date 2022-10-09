<?php

namespace kmcf7_message_filter;
?>
    <h1><?php esc_html_e( "Plugin Settings", KMCF7MS_TEXT_DOMAIN ) ?></h1>
	<?php settings_errors(); ?>
    <form method="post" action="options.php" id="basic_settings_form">
		<?php

		settings_fields( 'kmcfmf_message_filter_basic' );
		do_settings_sections( 'kmcf7-message-filter-options&tab=basic' );

		submit_button();
		?>
    </form>
    <div id="km-filters-container"
         style="display:none; position:absolute; z-index: 9; left:0;top:0; width: 100%; height: 100%; align-content: center; align-items: center; justify-content: center; background: rgba(0,0,0,0.2)">
        <div style="background: white; width: 500px; height:500px; overflow-y:auto; position: relative; padding-left: 10px; padding-right: 10px;">
			<?php $this->renderContent( 'filters', true ) ?>
        </div>
    </div>
    <script>
        jQuery(document).ready(function ($) {
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

            $("#km-filters").submit(function (e) {
                e.preventDefault()
                const data = $(this).serializeArray()
                for (let i = 0; i < data.length; i++) {
                    const filter = data[i].value;
                    $('#kmcfmf_restricted_words')[0].selectize.addOption({value: filter, text: filter})
                    $('#kmcfmf_restricted_words')[0].selectize.addItem(filter)
                }
                $("#km-filters-container").hide()
                $(this).trigger('reset');
            })
            $("#km-show-filters").click(function (e) {
                e.preventDefault()
                $("#km-filters-container").show()
                $("#km-filters-container").css('display', 'flex')
            })
            $("#km-hide-filters").click(function (e) {
                e.preventDefault()
                $("#km-filters-container").hide()
            })
        })


    </script>
<?php
// $settings->run();