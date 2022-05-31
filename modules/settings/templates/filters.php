<?php

namespace kmcf7_message_filter;
?>
    <h1>List of filters</h1>
    <table class="wp-list-table widefat fixed striped table-view-list posts">
        <tr>
            <th>S/N</th>
            <th>Name</th>
            <th>Description</th>
        </tr>
        <tr>
            <td>1</td>
            <td><code>[links]</code></td>
            <td>Filters messages having links</td>
        </tr>
        <tr>
            <td>2</td>
            <td><code>[russian]</code></td>
            <td>Filters messages having russian (cyrillic) characters</td>
        </tr>
        <tr>
            <td>3</td>
            <td><code>[hiragana]</code></td>
            <td>Filters messages having japanese (hiragana) characters</td>
        </tr>
        <tr>
            <td>4</td>
            <td><code>[katakana]</code></td>
            <td>Filters messages having japanese (katakana) characters</td>
        </tr>
        <tr>
            <td>5</td>
            <td><code>[kanji]</code></td>
            <td>Filters messages having japanese (kanji) characters</td>
        </tr>
        <tr>
            <td>6</td>
            <td><code>[japanese]</code></td>
            <td>Filters messages having japanese characters. Calls the following filters: <code>[hiragana]</code>,<code>[katakana]</code>
                and <code>[kanji]</code></td>
        </tr>
    </table>

<?php
// $settings->run();