<?php

namespace km_message_filter;

?>
    <h1 style="position:sticky; top:0; margin-bottom: 10px; background: white; text-align: center"><?php 
esc_html_e( "List of filters", KMCF7MS_TEXT_DOMAIN );
?></h1>
    <h2>Select the filters to add</h2>
    <form id="km-filters">
        <table class="wp-list-table widefat fixed striped table-view-list posts">
            <tr>
                <th></th>
                <th><?php 
esc_html_e( "Name", KMCF7MS_TEXT_DOMAIN );
?></th>
                <th><?php 
esc_html_e( "Description", KMCF7MS_TEXT_DOMAIN );
?></th>
            </tr>
            <tr>
                <td><input type="checkbox" name="filter" value="[link]"></td>
                <td><code>[link]</code></td>
                <td><?php 
esc_html_e( "Filters messages having links", KMCF7MS_TEXT_DOMAIN );
?></td>
            </tr>
            <tr>
                <td><input type="checkbox" name="filter" value="[russian]"></td>
                <td><code>[russian]</code></td>
                <td><?php 
esc_html_e( "Filters messages having russian (cyrillic) characters", KMCF7MS_TEXT_DOMAIN );
?></td>
            </tr>
            <tr>
                <td>
					<?php 
_e( "pro version", KMCF7MS_TEXT_DOMAIN );
?>
                </td>

                <td><code>[hiragana]</code></td>
                <td><?php 
esc_html_e( "Filters messages having japanese (hiragana) characters", KMCF7MS_TEXT_DOMAIN );
?></td>
            </tr>
            <tr>
                <td>
					<?php 
_e( "pro version", KMCF7MS_TEXT_DOMAIN );
?>
                </td>

                <td><code>[katakana]</code></td>
                <td><?php 
esc_html_e( "Filters messages having japanese (katakana) characters", KMCF7MS_TEXT_DOMAIN );
?></td>
            </tr>
            <tr>
                <td>
					<?php 
_e( "pro version", KMCF7MS_TEXT_DOMAIN );
?>
                </td>

                <td><code>[kanji]</code></td>
                <td><?php 
esc_html_e( "Filters messages having japanese (kanji) characters", KMCF7MS_TEXT_DOMAIN );
?></td>
            </tr>
            <tr>
                <td>
					<?php 
_e( "pro version", KMCF7MS_TEXT_DOMAIN );
?>
                </td>

                <td><code>[japanese]</code></td>
                <td><?php 
esc_html_e( "Filter messages having japanese characters. Calls the following filters: <code>[hiragana]</code>,<code>[katakana]</code>\n                and <code>[kanji]</code>", KMCF7MS_TEXT_DOMAIN );
?></td>
            </tr>
            <tr>
                <td>
					<?php 
_e( "pro version", KMCF7MS_TEXT_DOMAIN );
?>
                </td>

                <td><code>[emoji]</code></td>
                <td><?php 
esc_html_e( "Filters messages having emojis", KMCF7MS_TEXT_DOMAIN );
?> 😀😜</td>
            </tr>
        </table>
        <div style="display: flex; justify-content: space-between; padding-top: 10px;padding-bottom: 10px; position: sticky; bottom: 0; background: white">
            <button type="button" class="button"
                    id="km-hide-filters"><?php 
_e( "Cancel", KMCF7MS_TEXT_DOMAIN );
?></button>
            <button type="submit" class="button button-primary"><?php 
_e( "Add", KMCF7MS_TEXT_DOMAIN );
?></button>
        </div>

    </form>

<?php 
// $settings->run();