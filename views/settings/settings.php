<?php

namespace km_message_filter;

use WordPressTools;
$ajax_url = admin_url( "admin-ajax.php" );
$words = get_option( 'kmcfmf_restricted_words', '' );
$words = sizeof( explode( ',', $words ) );
$max_words = ( $words > 40 ? $words : 40 );
$is_free = !kmcf7ms_fs()->is_premium() || !kmcf7ms_fs()->is_plan_or_trial( 'pro' );
$tag_ui = get_option( 'kmcfmf_tag_ui', 'new_ui' );
$suggested_words = get_option( 'kmcfmf_suggested_words', '{}' );
if ( $suggested_words != '{}' && $suggested_words != '' ) {
    $suggested_words = json_decode( $suggested_words );
}
$upgrade_url = admin_url( 'admin.php' ) . '?page=kmcf7-message-filter-pricing';
update_option( "kmcfmf_messages_blocked_today_tmp", 0 );
?>
    <!-- <div class="notice notice-warning is-dismissible">
		 <p>
			 <strong>CF7 Form Filter</strong> is not yet tested with your current version of Contact Form 7.
			 <br>If you notice any problems with your forms, please instal Contact Form 7 <strong>version 5.7.7</strong>.
		 </p>
	 </div>-->

    <h1><?php 
esc_html_e( "Plugin Settings", KMCF7MS_TEXT_DOMAIN );
?></h1>

    <div id="km-upload" class="km-upload">
        <div>
			<?php 
_e( "Do you have a CSV file?", KMCF7MS_TEXT_DOMAIN );
?>
        </div>
        <div>
			<?php 
_e( "DRAG it here or CLICK here to upload it.", KMCF7MS_TEXT_DOMAIN );
?>
        </div>
        <div style="margin-top:20px">
            CSV FORMAT: word1, word2, word3, word4, etc...
        </div>
    </div>
    <!--	<?php 
/*if ( $is_free ): */
?>
    <div style="margin-top: 20px">
        <h2><?php 
/*_e( "Suggested Words", KMCF7MS_TEXT_DOMAIN ) */
?></h2>
		<?php 
/*_e( "Upgrade to premium and unlock powerful new suggestions every month. Majority of our clients saw a reduction in spam
  with these suggestions", KMCF7MS_TEXT_DOMAIN ) */
?>
    </div>
    <a href="<?php 
/*echo $upgrade_url */
?>">
        <img src="<?php 
/*echo KMCF7MS_IMAGES_URL . '/suggestions.png' */
?>" alt=""
             class="img-fluid" style=" width: 100%; margin-top: 20px;"/>
    </a>
--><?php 
/*endif; */
?>
	<?php 
if ( $suggested_words != '{}' ) {
    ?>
    <div id="km-suggestions-container">
        <h2><?php 
    _e( "Suggested Words", KMCF7MS_TEXT_DOMAIN );
    ?></h2>
        <div><?php 
    _e( "We have some suggested words and emails for you. 80% of our clients saw a 75% reduction in spam comments\n            within\n            a month by using these strategic word choices.", KMCF7MS_TEXT_DOMAIN );
    ?>
        </div>
		<?php 
    if ( trim( $suggested_words->words ?? '' ) > 0 ) {
        ?>
            <div style="margin-top: 20px">
                <b><?php 
        _e( "Words", KMCF7MS_TEXT_DOMAIN );
        ?>: </b> <br/><textarea type="text"
                                                                                    id="kmcfmf_suggested_words"
                                                                                    class="select2"><?php 
        echo $suggested_words->words;
        ?></textarea>
            </div>
		<?php 
    }
    ?>
		<?php 
    if ( trim( $suggested_words->emails ?? '' ) > 0 ) {
        ?>
            <div style="margin-top: 20px">
                <b><?php 
        _e( "Emails", KMCF7MS_TEXT_DOMAIN );
        ?></b> <br/><textarea type="text"
                                                                                   id="kmcfmf_suggested_emails"
                                                                                   class="select2"><?php 
        echo $suggested_words->emails;
        ?></textarea>
            </div>
		<?php 
    }
    ?>
		<?php 
    if ( trim( $suggested_words->words ?? '' ) > 0 || trim( $suggested_words->emails ?? '' ) > 0 ) {
        ?>
            <div style="margin-top: 10px" id="km-suggestions-loaded">
                <button class="button button-primary"
                        id="km-accept-suggestions"><?php 
        _e( "Add Suggested Words & Emails", KMCF7MS_TEXT_DOMAIN );
        ?></button>
                <button class="button button-delete"
                        id="km-ignore-suggestions"><?php 
        _e( "Ignore", KMCF7MS_TEXT_DOMAIN );
        ?></button>
            </div>
            <div style="margin-top: 10px; display:none" id="km-suggestions-loading">
                <button class="button button-primary"
                        disabled><?php 
        _e( "Please wait...", KMCF7MS_TEXT_DOMAIN );
        ?></button>
            </div>
		<?php 
    }
    ?>
    </div>
<?php 
}
?>

    <div style="font-size:15px; margin-top: 20px">
		<?php 
_e( "We have added 8 new filter modifiers", KMCF7MS_TEXT_DOMAIN );
?>: <strong>endsWith, endsWithExcluding,
            startsWith, startsWithExcluding, contains,
            containsExcluding, containsExcludingEnd </strong> and <strong>containsExcludingStart.</strong>
        <ol>
            <li>
                <strong>endsWith:ing</strong> => <?php 
_e( 'Searches only for words ending with “ing” eg. eating,
                walking, talking
                etc…, including the word “ing”.', KMCF7MS_TEXT_DOMAIN );
?>
            </li>
            <li>
                <strong>endsWithExcluding:ing</strong> => <?php 
_e( 'Searches only for words ending with “ing” eg.
                eating, walking,
                talking etc…, excluding the word “ing”.', KMCF7MS_TEXT_DOMAIN );
?>
            </li>
            <li>
                <strong>startsWith:app</strong> => <?php 
_e( 'Searches only for words starting with “app”. eg
                application, apple,
                including the word “app”', KMCF7MS_TEXT_DOMAIN );
?>
            </li>
            <li>
                <strong>startsWithExcluding:app</strong> => <?php 
_e( 'Searches only for words starting with “app”. eg
                application,
                apple, excluding the word “app”', KMCF7MS_TEXT_DOMAIN );
?>
            </li>
            <li>
                <strong> contains:ver</strong> => <?php 
_e( 'Searches only for words containing “ver”. eg every, Elvera,
                neverland,
                including the word “ver”, words starting with “ver” and words ending with “ver”', KMCF7MS_TEXT_DOMAIN );
?>
            </li>

            <li>
                <strong>containsExcluding:ver</strong> => <?php 
_e( 'Searches only for words containing “ver”. eg every,
                Elvera,
                neverland, excluding the word “ver”, words starting with “ver” and words ending with “ver”', KMCF7MS_TEXT_DOMAIN );
?>
            </li>
            <li>
                <strong>containsExcludingEnd:ver</strong> =><?php 
_e( ' Searches only for words containing “ver”. eg
                every, Elvera,
                neverland, excluding the word “ver” and words ending with “ver”', KMCF7MS_TEXT_DOMAIN );
?>
            </li>
            <li>
                <strong>containsExcludingStart:ver</strong> => <?php 
_e( 'Searches only for words containing “ver”. eg
                every, Elvera,
                neverland, excluding words starting with “ver” and the word “ver”', KMCF7MS_TEXT_DOMAIN );
?>
            </li>
        </ol>
    </div>
	<?php 
settings_errors();
?>
    <form method="post" action="options.php" id="basic_settings_form">
		<?php 
settings_fields( 'kmcfmf_basic' );
do_settings_sections( 'kmcf7-message-filter-options&tab=basic' );
submit_button();
?>
    </form>
    <div id="km-filters-container"
         style="display:none; position:absolute; z-index: 9; left:0;top:0; width: 100%; height: 100%; align-content: center; align-items: center; justify-content: center; background: rgba(0,0,0,0.2)">
        <div style="background: white; width: 500px; height:500px; overflow-y:auto; position: relative; padding-left: 10px; padding-right: 10px;">
			<?php 
WordPressTools::getInstance( __FILE__ )->renderView( 'settings.filters', true );
?>
        </div>
    </div>
    <script>
        jQuery(document).ready(function ($) {
				<?php 
if ( $tag_ui == 'old_ui' ) {
    ?>
                $('.select2').selectize({
                    delimiter: ',',
					<?php 
    if ( $is_free ) {
        ?>
                    maxItems: <?php 
        echo $max_words;
        ?>,
					<?php 
    }
    ?>
                    persist: false,
                    create: function (input) {
                        return {
                            value: input,
                            text: input
                        }
                    }
                });
				<?php 
} elseif ( $tag_ui == 'new_ui' ) {
    ?>
                const restricted_words_input = new KmTag('#kmcfmf_restricted_words', {
                    delimiter: ',',
					<?php 
    if ( $is_free ) {
        ?>
                    maxItems: <?php 
        echo $max_words;
        ?>,
					<?php 
    }
    ?>
                });
                const restricted_emails_input = new KmTag('#kmcfmf_restricted_emails', {
                    delimiter: ',',
					<?php 
    if ( $is_free ) {
        ?>
                    maxItems: <?php 
        echo $max_words;
        ?>,
					<?php 
    }
    ?>
                });
				<?php 
    if ( $suggested_words != '{}' ) {
        ?>
                new KmTag('#kmcfmf_suggested_words', {
                    delimiter: ',',
					<?php 
        if ( $is_free ) {
            ?>
                    maxItems: <?php 
            echo $max_words;
            ?>,
					<?php 
        }
        ?>
                });
                new KmTag('#kmcfmf_suggested_emails', {
                    delimiter: ',',
					<?php 
        if ( $is_free ) {
            ?>
                    maxItems: <?php 
            echo $max_words;
            ?>,
					<?php 
        }
        ?>
                });
				<?php 
    }
    ?>
				<?php 
}
?>


                $("#km-filters").submit(function (e) {
                    e.preventDefault()
                    const data = $(this).serializeArray()
                    for (let i = 0; i < data.length; i++) {
                        const filter = data[i].value;
						<?php 
if ( $tag_ui == 'old_ui' ) {
    ?>

                        $('#kmcfmf_restricted_words')[0].selectize.addOption({value: filter, text: filter})
                        $('#kmcfmf_restricted_words')[0].selectize.addItem(filter)
						<?php 
} elseif ( $tag_ui == 'new_ui' ) {
    ?>
                        restricted_words_input.addValue(filter)
						<?php 
} else {
    ?>
                        const currentWords = $('#kmcfmf_restricted_words').val()
                        $('#kmcfmf_restricted_words').val(currentWords + ',' + filter)
						<?php 
}
?>
                    }
                    $("#km-filters-container").hide()
                    $(this).trigger('reset');
                })
                $("#km-ignore-suggestions").click(function (e) {
                    e.preventDefault()
                    let formData = new FormData();
                    formData.append("action", 'kmcf7_clear_suggested_spam_words');

                    $("#km-suggestions-loading").show()
                    $("#km-suggestions-loaded").hide()
                    fetch("<?php 
echo $ajax_url;
?>", {
                        method: 'POST',
                        body: formData
                    })
                        .then(async response => {
                            if (!response.ok) {
                                alert(`An error occurred. Please try again later`)
                                $("#km-suggestions-loading").hide()
                                $("#km-suggestions-loaded").show()
                            } else
                                $('#km-suggestions-container').hide()
                        })
                        .catch(error => {
                            alert(`An error occurred. Please try again later`)
                            $("#km-suggestions-loading").hide()
                            $("#km-suggestions-loaded").show()
                        })

                });
                $("#km-accept-suggestions").click(function (e) {
                    e.preventDefault()
                    let formData = new FormData();
                    formData.append("action", 'kmcf7_clear_suggested_spam_words');
                    $("#km-suggestions-loading").show()
                    $("#km-suggestions-loaded").hide()
                    return fetch("<?php 
echo $ajax_url;
?>", {
                        method: 'POST',
                        body: formData
                    })
                        .then(async response => {
                            if (!response.ok) {
                                alert(`An error occurred. Please try again later`)
                                $("#km-suggestions-loading").hide()
                                $("#km-suggestions-loaded").show()
                            } else {
                                const words = $("#kmcfmf_suggested_words").val().split(',')
                                const emails = $("#kmcfmf_suggested_emails").val().split(',')
								<?php 
if ( $tag_ui == 'old_ui' ) {
    ?>
                                for (let i = 0; i < words.length; i++) {
                                    const word = words[i];
                                    $('#kmcfmf_restricted_words')[0].selectize.addOption({
                                        value: word,
                                        text: word
                                    })
                                    $('#kmcfmf_restricted_words')[0].selectize.addItem(word)

                                }
                                for (let i = 0; i < emails.length; i++) {
                                    const email = emails[i];
                                    $('#kmcfmf_restricted_emails')[0].selectize.addOption({
                                        value: email,
                                        text: email
                                    })
                                    $('#kmcfmf_restricted_emails')[0].selectize.addItem(email)

                                }
								<?php 
} elseif ( $tag_ui == 'new_ui' ) {
    ?>
                                restricted_words_input.addValues(words);
                                restricted_emails_input.addValues(emails)
								<?php 
} else {
    ?>
                                const currentWords = $('#kmcfmf_restricted_words').val()
                                $('#kmcfmf_restricted_words').val(currentWords + ',' + words.join(','))

                                const currentEmails = $('#kmcfmf_restricted_emails').val()
                                $('#kmcfmf_restricted_emails').val(currentEmails + ',' + words.join(','))

								<?php 
}
?>
                                $('#km-suggestions-container').hide()
                                alert("Suggestions Added. Please click the Save Changes button to save your changes")
                            }
                        })
                        .catch(error => {
                            alert(`An error occurred. Please try again later`)
                            $("#km-suggestions-loading").hide()
                            $("#km-suggestions-loaded").show()
                        })
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

                const km_upload = $("#km-upload")
                km_upload.on('dragover', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    km_upload.addClass('km-upload__dragover')
                })

                km_upload.on('ondragleave', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    km_upload.removeClass('km-upload__dragover')
                })

                km_upload.on('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const input = document.createElement('input');
                    input.type = 'file';
                    input.accept = '.csv'
                    input.onchange = e => {
                        const file = e.target.files[0];
                        processFile(file)
                    }
                    input.click();
                })

                km_upload.on('drop', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    km_upload.removeClass('km-upload__dragover')
                    const files = e.originalEvent.dataTransfer.files;
                    const file = files[0];
                    processFile(file)
                })

                function processFile(file) {
					<?php 
if ( kmcf7ms_fs()->is_free_plan() || !kmcf7ms_fs()->is_premium() ) {
    ?>
                    Swal.fire({
                        title: '<?php 
    _e( "File Import", KMCF7MS_TEXT_DOMAIN );
    ?>',
                        text: "Import is only available in the pro plan",
                        icon: 'danger',
                        showCancelButton: true,
                        confirmButtonText: 'Ok',
                        showLoaderOnConfirm: true,
                    })
					<?php 
}
?>
					<?php 
?>
                }
            }
        )


    </script>
<?php 
// $settings->run();