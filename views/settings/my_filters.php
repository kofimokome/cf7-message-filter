<?php

namespace km_message_filter;
$ajax_url = admin_url( "admin-ajax.php" );

$my_filters           = MyFilter::all();
$can_add_more_filters = ( kmcf7ms_fs()->is_premium() && kmcf7ms_fs()->is_plan_or_trial( 'pro' ) ) || ( ! kmcf7ms_fs()->is_premium() && count( $my_filters ) < 1 )
?>
<h1><?php esc_html_e( "My Filters ", KMCF7MS_TEXT_DOMAIN ) ?></h1>
<div>

	<?php _e( "Here, you can create your own custom spam filter", KMCF7MS_TEXT_DOMAIN ) ?>
</div>

<div style="margin-top: 10px">
	<?php _e( "Creating a custom filter requires knowledge of <a href='https://developer.mozilla.org/en-US/docs/Web/JavaScript/Guide/Regular_expressions' target='_blank'>Regular Expressions</a>. You can contact us (Pro users only) to write a custom filter for you.", KMCF7MS_TEXT_DOMAIN ) ?>
</div>
<div>
	<?php _e( "A Filter can take variables in the form of<code>{{variable}}</code>. For example, if you want to filter multiple words with the same custom filter, you can add a variable, say  'name'  in the regular expression. <code>\S+{{name}}\S+</code>.<br/> You can pass the variable when calling the filter as shown below: <code>[my-filter-name name=John Doe]</code>", KMCF7MS_TEXT_DOMAIN ) ?>
</div>
<?php if ( ! $can_add_more_filters ): ?>
    <div style="margin-top: 10px">
        <strong><?php _e( "Note: As a free user, you can add up to 1 custom filter.", KMCF7MS_TEXT_DOMAIN ) ?></strong>
    </div>
<?php endif; ?>
<table class="wp-list-table widefat fixed striped table-view-list posts" style="margin-top: 40px">
    <thead>
    <tr>
        <th>S/N</th>
        <th scope="col">
			<?php _e( "Name", KMCF7MS_TEXT_DOMAIN ) ?>
        </th>
        <th scope="col">
			<?php _e( "Description", KMCF7MS_TEXT_DOMAIN ) ?>
        </th>
        <th scope="col">
			<?php _e( "Short Code", KMCF7MS_TEXT_DOMAIN ) ?>
        </th>
        <th scope="col">
			<?php _e( "Expression", KMCF7MS_TEXT_DOMAIN ) ?>
        </th>
        <th scope="col">
			<?php _e( "Created At", KMCF7MS_TEXT_DOMAIN ) ?>
        </th>
        <th scope="col">
			<?php _e( "Actions", KMCF7MS_TEXT_DOMAIN ) ?>
        </th>
    </tr>
    </thead>

    <tbody>
    <tr>
        <td>1</td>
        <td>Link</td>
        <td><?php esc_html_e( "Filters messages having links", KMCF7MS_TEXT_DOMAIN ) ?></td>
        <td><code>[link]</code></td>
        <td>-</td>
        <td>-</td>
        <td>-</td>
    </tr>
    <tr>
        <td>2</td>
        <td>Russian</td>
        <td><?php esc_html_e( "Filters messages having russian (cyrillic) characters", KMCF7MS_TEXT_DOMAIN ) ?></td>
        <td><code>[russian]</code></td>
        <td>-</td>
        <td>-</td>
        <td>-</td>
    </tr>
    <tr>
        <td>3</td>
        <td>Hiragana</td>
        <td><?php esc_html_e( "Filters messages having japanese (hiragana) characters", KMCF7MS_TEXT_DOMAIN ) ?></td>
        <td><code>[hiragana]</code></td>
        <td>-</td>
        <td>-</td>
        <td>-</td>
    </tr>
    <tr>
        <td>4</td>
        <td>Katakana</td>
        <td><?php esc_html_e( "Filters messages having japanese (katakana) characters", KMCF7MS_TEXT_DOMAIN ) ?></td>
        <td><code>[katakana]</code></td>
        <td>-</td>
        <td>-</td>
        <td>-</td>
    </tr>
    <tr>
        <td>
            5
        </td>
        <td>Kanji</td>
        <td><?php esc_html_e( "Filters messages having japanese (kanji) characters", KMCF7MS_TEXT_DOMAIN ) ?></td>
        <td><code>[kanji]</code></td>
        <td>-</td>
        <td>-</td>
        <td>-</td>
    </tr>
    <tr>
        <td>
            6
        </td>
        <td>Japanese</td>
        <td><?php _e( "Filter messages having japanese characters. Calls the following filters: <code>[hiragana]</code>,<code>[katakana]</code>
                and <code>[kanji]</code>", KMCF7MS_TEXT_DOMAIN ) ?></td>
        <td><code>[japanese]</code></td>
        <td>-</td>
        <td>-</td>
        <td>-</td>

    </tr>
    <tr>
        <td>
            7
        </td>
        <td>Emoji</td>
        <td><?php esc_html_e( "Filters messages having emojis", KMCF7MS_TEXT_DOMAIN ) ?> 😀😜</td>
        <td><code>[emoji]</code></td>
        <td>-</td>
        <td>-</td>
        <td>-</td>
    </tr>
	<?php foreach (
		$my_filters

		as $index => $my_filter
	): ?>
        <tr>
            <td>
				<?php echo $index + 8 ?>
            </td>
            <td>
                <input type="text" name="name" class="km-new-filter-name"
                       id="km-new-filter-name-<?php esc_html_e( $my_filter->id ); ?>"
                       value=" <?php echo esc_html( $my_filter->name ) ?>"
                       placeholder="<?php _e( 'Name of filter', KMCF7MS_TEXT_DOMAIN ) ?>">
            </td>
            <td>
                   <textarea name="description" class="km-new-filter-description"
                             id="km-new-filter-description-<?php esc_html_e( $my_filter->id ); ?>"
                             placeholder="<?php _e( 'Filter description (optional)', KMCF7MS_TEXT_DOMAIN ) ?>"> <?php echo esc_html( $my_filter->description ) ?></textarea>
            </td>
            <td><kbd>[<?php echo esc_html( FiltersModule::getInstance()->buildShortCode( $my_filter, true ) ) ?>]</kbd></td>
            <td>
                /&nbsp;<textarea name="filter" id="km-new-filter-expression-<?php esc_html_e( $my_filter->id ); ?>"
                                 placeholder="[a-zA-z0-9_+]"><?php echo esc_html( $my_filter->expression ) ?></textarea>&nbsp;/mui
            </td>
            <td><?php echo date( "Y-m-d", $my_filter->created_at ) ?></td>
            <td>
                <button style="display:none" class="button button-primary disabled km-loading-btn" disabled>loading
                    ...
                </button>
                <input type="button" name="update" id="km-update-<?php esc_html_e( $my_filter->id ); ?>"
                       class="button button-primary km-update"
                       value="<?php _e( "Update", KMCF7MS_TEXT_DOMAIN ) ?>">
                <input style="background: orangered; border-color: orangered"
                       id="km-delete-<?php esc_html_e( $my_filter->id ); ?>" type="button" name="delete"
                       class="button button-primary km-delete" value="<?php _e( "Delete", KMCF7MS_TEXT_DOMAIN ) ?>">
            </td>
        </tr>
	<?php endforeach; ?>
    </tbody>
    <tfoot>
	<?php if ( $can_add_more_filters ): ?>
        <tr>
            <td></td>
            <td>
                <input type="text" name="name" id="km-new-filter-name"
                       placeholder="<?php _e( 'Name of filter', KMCF7MS_TEXT_DOMAIN ) ?>">
            </td>
            <td>
            <textarea name="description" id="km-new-filter-description"
                      placeholder="<?php _e( 'Filter description (optional)', KMCF7MS_TEXT_DOMAIN ) ?>"></textarea>
            </td>
            <td></td>
            <td>
                /&nbsp;<textarea name="filter" id="km-new-filter-expression" placeholder="[a-zA-z0-9_+]"></textarea>&nbsp;/mui
            </td>
            <td>
				<?php echo date( "Y-m-d" ) ?>
            </td>
            <td>
                <button style="display:none" class="button button-primary disabled km-loading-btn" disabled>loading ...
                </button>
                <input type="button" name="save" id="km-save" class="button button-primary"
                       value="<?php _e( "Save", KMCF7MS_TEXT_DOMAIN ) ?>">
            </td>
        </tr>
	<?php else: ?>
        <tr>
            <td colspan="7">
                <h3> <?php _e( "You can add only 1 custom filter. Upgrade to Pro to add more custom filters", KMCF7MS_TEXT_DOMAIN ) ?>
                </h3>
            </td>
        </tr>
	<?php endif; ?>

    </tfoot>
</table>
<script>

    jQuery(document).ready(function ($) {
        function showLoadingBtn() {
            $(".km-loading-btn").show();
            $(".km-update").hide();
            $(".km-delete").hide();
            $("#km-save").hide();
        }

        function hideLoadingBtn() {
            $(".km-loading-btn").hide();
            $(".km-update").show();
            $(".km-delete").show();
            $("#km-save").show();
        }

        $(".km-delete").click(function () {
            const id = $(this).attr('id').split('-')[2];
            let formData = new FormData();
            formData.append("action", 'kmcf7_delete_filter');
            formData.append("id", id);
            Swal.fire({
                title: 'Delete Filter',
                text: '<?php _e( "Are you sure you want to delete this filter?", KMCF7MS_TEXT_DOMAIN ) ?>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete',
                showLoaderOnConfirm: true,
                preConfirm: (login) => {
                    showLoadingBtn();
                    return fetch("<?php echo $ajax_url?>", {
                        method: 'POST',
                        body: formData
                    })
                        .then(async response => {
                            if (!response.ok) {
                                const e = await response.text();
                                let message = "Something went wrong";
                                try {
                                    const response_json = JSON.parse(e)
                                    if (response_json.data)
                                        message = response_json.data.message ?? response_json.data.toString()
                                } catch (e) {
                                    // Silence is golden
                                }
                                throw new Error(message)
                            } else
                                return response.json()
                        })
                        .catch(error => {
                            hideLoadingBtn();
                            Swal.fire({
                                title: "Opps!",
                                text: error,
                                icon: "warning"
                            });
                        })
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.reload()
                }
            })
        })

        $(".km-update").click(function () {
            const id = $(this).attr('id').split('-')[2];
            const name = $('#km-new-filter-name-' + id).val();
            const description = $('#km-new-filter-description-' + id).val();
            const expression = $('#km-new-filter-expression-' + id).val();

            // validate name and filter
            if (name.trim() === '' || expression.trim() === '') {
                Swal.fire({
                    title: "Opps!",
                    text: "Name and expression cannot be empty",
                    icon: "warning"
                });
                return;
            }

            let formData = new FormData();
            formData.append("action", 'kmcf7_update_filter');
            formData.append("id", id);
            formData.append("name", name);
            formData.append("description", description);
            formData.append("expression", expression);
            showLoadingBtn();
            fetch("<?php echo $ajax_url?>", {
                method: 'POST',
                body: formData
            })
                .then(async response => {
                    if (!response.ok) {
                        const e = await response.text();
                        let message = "Something went wrong";
                        try {
                            const response_json = JSON.parse(e)
                            if (response_json.data)
                                message = response_json.data.message ?? response_json.data.toString()
                        } catch (e) {
                            // Silence is golden
                        }
                        throw new Error(message)
                    } else {
                        window.location.reload()
                        return response.json()
                    }
                })
                .catch(error => {
                    hideLoadingBtn()
                    Swal.fire({
                        title: "Opps!",
                        text: error,
                        icon: "warning"
                    });
                })
        });
        $('#km-save').click(function () {
            const name = $('#km-new-filter-name').val();
            const description = $('#km-new-filter-description').val();
            const expression = $('#km-new-filter-expression').val();

            // validate name and filter
            if (name.trim() === '' || expression.trim() === '') {
                Swal.fire({
                    title: "Opps!",
                    text: "Name and expression cannot be empty",
                    icon: "warning"
                });
                return;
            }
            let formData = new FormData();
            formData.append("action", 'kmcf7_save_filter');
            formData.append("name", name);
            formData.append("description", description);
            formData.append("expression", expression);
            showLoadingBtn()
            fetch("<?php echo $ajax_url?>", {
                method: 'POST',
                body: formData
            })
                .then(async response => {
                    if (!response.ok) {
                        const e = await response.text();
                        let message = "Something went wrong";
                        try {
                            const response_json = JSON.parse(e)
                            if (response_json.data)
                                message = response_json.data.message ?? response_json.data.toString()
                        } catch (e) {
                            // Silence is golden
                        }
                        throw new Error(message)
                    } else {
                        window.location.reload()
                        return response.json()
                    }
                })
                .catch(error => {
                    hideLoadingBtn()
                    Swal.fire({
                        title: "Opps!",
                        text: error,
                        icon: "warning"
                    });
                })
        });

        $('#km-delete').click(function () {
            var name = $('#km-new-filter-name').val();
            var data = {
                action: 'km_delete_filter',
                name: name
            };
            $.post(ajaxurl, data, function (response) {
                console.log(response);
            });
        });
    })
    ;
</script>