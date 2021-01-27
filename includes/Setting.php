<?php
/**
 * Created by PhpStorm.
 * User: kofi
 * Date: 6/5/19
 * Time: 12:41 PM
 */

namespace kmcf7_message_filter;


class Setting
{
    private $page_title;
    private $menu_slug;
    private $function;
    private $default_content;
    private $fields;
    private $section_id;
    private $sections;

    public function __construct($menu_slug, $use_default_menu = true)
    {
        $this->menu_slug = $menu_slug;

        if ($use_default_menu) {
            $this->function = array(&$this, 'default_function');
        }
        $this->default_content = '';
        $this->fields = array();
        $this->sections = array();
    }

    public function default_function()
    {
        $current_tab = isset($_GET['tab']) ? $_GET['tab'] : null;
        ?>
        <div class="wrap">
            <div id="icon-options-general" class="icon32"></div>
            <h1><?php echo $this->page_title ?></h1>
            <strong>Please enter each word separated by white-spaces (spaces, newline, etc.) or comma in the boxes
                below</strong>
            <?php settings_errors(); ?>
            <form method="post" action="options.php">
                <?php
                foreach ($this->sections as $section):
                    settings_fields($section[0]);
                    do_settings_sections($this->menu_slug);
                endforeach;
                submit_button();
                ?>
            </form>

        </div>
        <?php
        //echo $this->default_content;
    }

    public function run()
    {
        add_action('admin_init', array($this, 'add_settings'));
    }

    public function add_settings()
    {
        foreach ($this->sections as $section) {
            add_settings_section(
                $section[0],
                $section[1],
                array($this, 'default_section_callback'),
                $this->menu_slug);
        }

        foreach ($this->fields as $field) {
            add_settings_field(
                $field['id'],
                $field['label'],
                array($this, 'default_field_callback'),
                $this->menu_slug,
                $field['section_id'],
                $field
            );
            register_setting($field['section_id'], $field['id']);
        }
    }

    public function add_field($data)
    {
        // todo: compare two arrays
        $data['section_id'] = $this->section_id;
        array_push($this->fields, $data);


    }

    public function default_field_callback($data)
    {
        switch ($data['type']) {
            case 'text':
                echo "<p><input type='text' name='{$data['id']}' value='" . get_option($data['id']) . "'></p>";
                echo "<strong>{$data['tip']} </strong>";
                break;
            case 'textarea':
                echo "<p><textarea name='{$data['id']}' id='{$data['id']}' cols='80'
                  rows='8'
                  placeholder='{$data['placeholder']}'>" . get_option($data['id']) . "</textarea></p>";
                echo "<strong>{$data['tip']} </strong>";
                break;
            case 'checkbox':
                $state = get_option($data['id']) == 'on' ? 'checked' : '';
                echo "<p><input type='checkbox' name='{$data['id']}' id='{$data['id']}' " . $state . " ></p>";
                echo "<strong>{$data['tip']} </strong>";
                break;
        }
    }

    public function add_section($id, $title = '')
    {
        array_push($this->sections, array($id, $title));
        $this->section_id = $id;
    }

    public function default_section_callback()
    {

    }
}
