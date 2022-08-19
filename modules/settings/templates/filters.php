<?php

namespace kmcf7_message_filter;
?>
    <h1><?php _e( "List of filters", KMCF7MS_TEXT_DOMAIN ) ?></h1>
    <table class="wp-list-table widefat fixed striped table-view-list posts">
        <tr>
            <th>S/N</th>
            <th><?php _e( "Name", KMCF7MS_TEXT_DOMAIN ) ?></th>
            <th><?php _e( "Description", KMCF7MS_TEXT_DOMAIN ) ?></th>
        </tr>
        <tr>
            <td>1</td>
            <td><code>[link]</code></td>
            <td><?php _e( "Filters messages having links", KMCF7MS_TEXT_DOMAIN ) ?></td>
        </tr>
        <tr>
            <td>2</td>
            <td><code>[russian]</code></td>
            <td><?php _e( "Filters messages having russian (cyrillic) characters", KMCF7MS_TEXT_DOMAIN ) ?></td>
        </tr>
        <tr>
            <td>3</td>
            <td><code>[hiragana]</code></td>
            <td><?php _e( "Filters messages having japanese (hiragana) characters", KMCF7MS_TEXT_DOMAIN ) ?></td>
        </tr>
        <tr>
            <td>4</td>
            <td><code>[katakana]</code></td>
            <td><?php _e( "Filters messages having japanese (katakana) characters", KMCF7MS_TEXT_DOMAIN ) ?></td>
        </tr>
        <tr>
            <td>5</td>
            <td><code>[kanji]</code></td>
            <td><?php _e( "Filters messages having japanese (kanji) characters", KMCF7MS_TEXT_DOMAIN ) ?></td>
        </tr>
        <tr>
            <td>6</td>
            <td><code>[japanese]</code></td>
            <td><?php _e( "Filters messages having japanese characters. Calls the following filters: <code>[hiragana]</code>,<code>[katakana]</code>
                and <code>[kanji]</code>", KMCF7MS_TEXT_DOMAIN ) ?></td>
        </tr>
        <tr>
            <td>7</td>
            <td><code>[emoji]</code></td>
            <td><?php _e( "Filters messages having emojis", KMCF7MS_TEXT_DOMAIN ) ?> 😀😜</td>
        </tr>
    </table>

<?php
// $settings->run();