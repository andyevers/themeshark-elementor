<?php

namespace Themeshark_Elementor\Inc;

/**
 * Creates an option array in wp_options table. Add this trait to your page class and create an use register_settings_fields() on admin_menu action when adding the menu page
 */
trait WP_Settings_Page_Trait
{
    // PRIVATE PROPERTIES
    //-----------------------------------------------

    private $_current_settings        = [];    // holds current option settings from database
    private $_current_section_id      = null;  // holds the section id that settings are being added to
    private $_registered_fields       = [];    // holds field IDs that have been registered to ensure no duplicates.
    private $_current_section_options = [];    // the third arg given in start_fields_section for the current section
    private $_is_first_fields_section = true;  // used to know if settings_field() function needs to be fired
    private $_is_registering_fields   = false; // used to know whether to allow adding fields
    private $_did_register_fields     = false; // prevents fields from being registered twice


    // REQUIRED METHODS
    //-----------------------------------------------

    /** Name of the option that will be stored in the wp_options table */
    abstract public static function get_option();

    /** Slug for the settings page */
    abstract public static function get_slug();

    /** Registers option fields */
    abstract protected function register_fields();


    /** 
     * Registers wp_option and settings fields. Fire this on admin_init action. 
     */
    public function register_settings_fields()
    {
        if ($this->_did_register_fields) return;

        // will be used to set the current value of each option
        $this->_current_settings = get_option(self::get_option());

        // we're using the option as the group name too. 
        $wp_option  = self::get_option();
        $group_name = self::get_option();

        // add wp_option to DB. get_option($wp_option) returns an assoc array of the settings registered on the page
        register_setting($group_name, $wp_option);

        $this->_is_registering_fields = true;
        $this->register_fields();
        $this->_is_registering_fields = false;

        $this->_did_register_fields = true;
    }


    /**
     * Gets the current settings for the options
     */
    public function get_settings($field_id = null)
    {
        $settings = $this->_current_settings;

        if ($field_id === null) {
            return $settings;
        }

        return isset($settings[$field_id]) ? $settings[$field_id] : null;
    }


    /**
     * Creates a new group of fields that can be echoed together using do_fields_section
     */
    protected function start_fields_section($section_id, $label, $section_options = [])
    {
        // Error Handling
        if ($this->_current_section_id !== null) wp_die("you must end fields section $this->_current_section_id before starting $section_id");
        if (!$this->_is_registering_fields)      wp_die('start_fields_section can only be called inside register_fields()');

        // Echo description before doing callback
        $callback = function ($section) use ($section_options) {

            $allowed_html = [
                'p' => [],
                'div' => [],
                'span' => [],
                'a' => [
                    'href' => [],
                    'class' => [],
                    'target' => [],
                ]
            ];

            if (isset($section_options['description'])) echo wp_kses($section_options['description'], $allowed_html);
            if (isset($section_options['callback']))    call_user_func($section_options['callback'], $section);
        };

        add_settings_section(
            $section_id,
            $label,
            $callback,
            self::get_slug()
        );

        $this->_current_section_options = $section_options;
        $this->_current_section_id      = $section_id;
    }

    /**
     * Ends the current fields section 
     */
    protected function end_fields_section()
    {
        if (!$this->_is_registering_fields) wp_die('end_fields_section can only be called inside register_fields()');

        $this->_current_section_options = [];
        $this->_current_section_id = null;
    }


    /**
     * Echos HTML for the fields in the section provided
     */
    protected function do_fields_section($section_id)
    {
        global $wp_settings_sections;

        $page      = self::get_slug();
        $sections  = self::get_arr_key((array)$wp_settings_sections, $page, null);
        $section   = self::get_arr_key($sections, $section_id, null);

        // Error handling
        if ($sections === null) wp_die("$page does not have any registered sections. Call register_settings_fields to register.");
        if ($section === null)  wp_die("$section_id is not a registered section.");

        if ($this->_is_first_fields_section === true) {

            $group_name = self::get_option();

            settings_fields($group_name); // prevents directing to wp options page after submit

            $this->_is_first_fields_section = false;
        }

        // Output Fields
        if ($section['title'])    echo "<h2>{$section['title']}</h2>\n";
        if ($section['callback']) call_user_func($section['callback'], $section);

        echo "<table class=\"form-table settings-$section_id\" role=\"presentation\">";
        do_settings_fields($page, $section_id);
        echo '</table>';
    }



    // HELPERS
    //-----------------------------------------------

    /**
     * Gets val from array if set, otherwise returns the $default arg.
     */
    private static function get_arr_key($arr, $key, $default)
    {
        if (!is_array($arr)) return $default;
        return isset($arr[$key]) ? $arr[$key] : $default;
    }


    /**
     * Creates string to be echoed into HTML attributes
     */
    private static function create_attribute_string($atts_arr)
    {
        $atts_string = '';

        foreach ($atts_arr as $att => $val) {

            if (empty($att)) continue;

            $atts_string .= sprintf('%1$s="%2$s" ', $att, esc_attr($val));
        }
        return $atts_string;
    }


    /**
     * Gets the HTML name attribute for a setting
     * @param string $field_id id of the field to get the value of
     * @param boolean $is_multiple whether the field contains multiple values
     */
    private function get_name_attribute($field_id, $is_multiple = false)
    {
        $wp_option = self::get_option();
        $name = "{$wp_option}[$field_id]";
        return $is_multiple ? $name . '[]' : $name;
    }


    /**
     * Kills the script if any of the specified keys are not found in $arr
     */
    private function verify_keys($field_id, $arr, $keys)
    {
        if (!is_array($keys)) $keys = [$keys];

        foreach ($keys as $key) {
            if (!array_key_exists($key, $arr)) {
                wp_die("key $key is required for field $field_id");
            }
        }
    }


    private static function print_text($text, $tag = null, $atts = [])
    {
        // HTML and attributes that will not be filtered out of $text string
        $allowed_html = [
            'p' => [],
            'div' => [],
            'span' => [],
            'a' => [
                'href' => [],
                'class' => [],
                'target' => [],
            ]
        ];

        if (!is_string($text) || empty($text)) return;

        $tag_open  = $tag === null ? '' : '<' . wp_kses($tag, $allowed_html) . ' ' . self::create_attribute_string($atts) . '>';
        $tag_close = $tag === null ? '' : '</' . wp_kses($tag, $allowed_html) . '>';

        echo $tag_open . wp_kses($text, $allowed_html) . $tag_close;
    }

    /**
     * Merges $this->_current_section_options['field_options'] with the $options array provided
     */
    private function merge_shared_field_options($options)
    {
        $section_options = $this->_current_section_options;
        $shared_options = isset($section_options['field_options'])
            ? $section_options['field_options'] : [];

        return array_merge($shared_options, $options);
    }


    /** 
     * method and post atts automatically included 
     */
    protected static function get_form_attribute_string($addon_atts = [])
    {
        //method and action required for forms
        $atts = array_merge($addon_atts, [
            'method' => 'post',
            'action' => 'options.php',
        ]);

        $att_string = '';
        foreach ($atts as $att => $val) {
            $att_string .= sprintf('%1$s="%2$s" ', $att, esc_attr($val));
        }
        return $att_string;
    }


    /**
     * Takes keys from options and sets as attribute if it is an allowed attribute for the input type
     * Attributes not listed in here for the type provided will not be added to the HTML input.
     * @param array $options argument in field function.
     * @param string $type the input type
     */
    private static function extract_atts_by_type($options, $type)
    {
        // attributes allowed for inputs by type
        $input_attributes = [
            'min'         => ['number', 'range', 'date', 'datetime', 'datetime-local', 'time'],
            'max'         => ['number', 'range', 'date', 'datetime', 'datetime-local', 'time'],
            'step'        => ['number', 'range', 'date', 'datetime', 'datetime-local', 'time'],
            'minlength'   => ['email', 'password', 'tel', 'text', 'url', 'search', 'textarea'],
            'maxlength'   => ['email', 'password', 'tel', 'text', 'url', 'search', 'textarea'],
            'placeholder' => ['email', 'password', 'tel', 'text', 'url', 'search', 'textarea'],
            'pattern'     => ['password', 'text', 'tel'],
            'checked'     => ['radio', 'checkbox'],
            'multiple'    => ['email', 'file'],
            'rows'        => ['textarea'],
            'capture'     => ['file'],
            'readonly'    => ['ALL'],
            'required'    => ['ALL'],
            'disabled'    => ['ALL'],
            'type'        => ['ALL'],
            'id'          => ['ALL'],
            'name'        => ['ALL'],
            'value'       => ['ALL'],
            'class'       => ['ALL']
        ];

        $atts = [];
        //default atts
        foreach ($input_attributes as $attribute => $types) {
            $allowed_attribute = $types === ['ALL'] || in_array($type, $types);
            $has_attribute     = isset($options[$attribute]);

            if ($has_attribute && $allowed_attribute) {
                $atts[$attribute] = $options[$attribute];
            }
        }

        return $atts;
    }

    /**
     * Adds field data to option value in wp_options table
     */
    private function register_field($field_id, $label, $options, $field_callback)
    {
        $already_registered = in_array($field_id, $this->_registered_fields);

        // Error handling
        if (!$this->_is_registering_fields) wp_die('field can only be called inside register_fields()');
        if (!is_string($field_id))          wp_die("field_id must be a string");
        if (!$this->_current_section_id)    wp_die("You cannot add field $field_id outside of a field section");
        if ($already_registered)            wp_die("field $field_id has already been registered");

        // 'label_for' and 'class' are default settings args. 'class' checked as 'row_class' not to mix up field class att.
        $settings  = [];
        $label_for = isset($options['label_for']) ? $options['label_for'] : $field_id;
        $row_class = isset($options['row_class']) ? $options['row_class'] : null;

        if ($label_for !== null) $settings['label_for'] = $label_for;
        if ($row_class !== null) $settings['class'] = $row_class;

        // Setting added to DB returned in option array
        add_settings_field(
            $field_id,
            $label,
            $field_callback,
            self::get_slug(),
            $this->_current_section_id,
            $settings
        );

        // Prevents duplicates
        $this->_registered_fields[] = $field_id;
    }


    // FIELD METHODS
    //-----------------------------------------------

    /**
     * Create an <input> field
     * 
     * @param array $options required: type
     */
    protected function field_input($id, $label, $options)
    {
        $this->verify_keys($id, $options, ['type']);
        $options = $this->merge_shared_field_options($options);

        // set id, name and value in options array to become attributes
        $default          = self::get_arr_key($options, 'default', '');
        $options['id']    = $id;
        $options['name']  = $this->get_name_attribute($id);
        $options['value'] = self::get_arr_key($this->get_settings(), $id, $default);

        // create HTML field
        $field_callback = function () use ($options) {

            $type        = $options['type'];
            $atts        = self::extract_atts_by_type($options, $type);
            $description = self::get_arr_key($options, 'description', '');

            //adds 'regular-text' class to textual fields to set the width
            $textual_fields = ['email', 'password', 'tel', 'text', 'url', 'search'];
            if (in_array($type, $textual_fields)) {
                $atts['class'] = isset($atts['class']) ? $atts['class'] : '';
                $atts['class'] .= ' regular-text';
            } ?>

            <input <?php echo self::create_attribute_string($atts); ?>><br>
            <?php self::print_text($description, 'p'); ?>

            <?php };

        // register setting field
        $this->register_field($id, $label, $options, $field_callback);
    }

    /**
     * Creates multiple <input>. checkbox or radio.
     * 
     * @param array $options required: type, options
     */
    protected function field_inputs($id, $label, $options)
    {
        $this->verify_keys($id, $options, ['type', 'options']);
        $options = $this->merge_shared_field_options($options);

        //set id to become attribute
        $default          = self::get_arr_key($options, 'default', '');
        $options['id']    = $id;
        $options['value'] = self::get_arr_key($this->get_settings(), $id, $default);

        $field_callback = function () use ($options) {

            $id            = $options['id'];
            $type          = $options['type'];
            $input_options = $options['options'];
            $description   = self::get_arr_key($options, 'description', '');

            self::print_text($description, 'p');

            //create each input
            foreach ($input_options as $value => $label) :

                $is_multiple_values = $type === 'checkbox';

                //if there are multiple values, check if the value is in the array to see if it's checked.
                $is_checked = is_array($options['value'])
                    ? in_array($value, $options['value'])
                    : checked($value, $options['value'], false);

                $atts = [
                    'type'  => $type,
                    'name'  => $this->get_name_attribute($id, $is_multiple_values),
                    'value' => $value,
                ];

                if ($is_checked) $atts['checked'] = 'checked'; ?>

                <label>
                    <input <?php echo self::create_attribute_string($atts); ?> /><?php esc_html_e($label); ?>
                </label>
                <br>

            <?php endforeach; ?>
        <?php };

        $this->register_field($id, $label, $options, $field_callback);
    }



    /**
     * Create <select> dropdown field
     * 
     * @param array $options required: options
     */
    protected function field_select($id, $label, $options)
    {
        $this->verify_keys($id, $options, ['options']);
        $options = $this->merge_shared_field_options($options);

        $default          = self::get_arr_key($options, 'default', '');
        $options['id']    = $id;
        $options['name']  = $this->get_name_attribute($id);
        $options['value'] = self::get_arr_key($this->get_settings(), $id, $default);

        // create HTML field
        $field_callback = function () use ($options) {

            $select_options = $options['options'];
            $cur_value      = $options['value'];
            $atts           = self::extract_atts_by_type($options, 'select');
            $description    = self::get_arr_key($options, 'description', ''); ?>

            <select <?php echo self::create_attribute_string($atts); ?>>

                <?php foreach ($select_options as $value => $label) : ?>

                    <option value="<?php esc_attr_e($value); ?>" <?php selected($value, $cur_value); ?>>
                        <?php esc_html_e($label); ?>
                    </option>

                <?php endforeach; ?>
            </select>
        <?php self::print_text($description, 'p');
        };

        $this->register_field($id, $label, $options, $field_callback);
    }


    /**
     * Create single <input type="checkbox"/> field
     */
    protected function field_check($id, $label, $options = [])
    {
        $options = $this->merge_shared_field_options($options);

        // keys added to options to be added as HTML attributes
        $options['id']    = $id;
        $options['name']  = $this->get_name_attribute($id);
        $options['value'] = self::get_arr_key($options, 'value', 1);
        $options['type']  = 'checkbox';

        $field_callback = function () use ($options) {

            $id          = $options['id'];
            $value       = $options['value'];
            $cur_value   = self::get_arr_key($this->get_settings(), $id, null);
            $atts        = self::extract_atts_by_type($options, 'checkbox');
            $description = self::get_arr_key($options, 'description', ''); ?>

            <label>
                <input <?php echo self::create_attribute_string($atts) ?> <?php checked($value, $cur_value) ?> /><?php self::print_text($description); ?>
            </label>
        <?php };

        $this->register_field($id, $label, $options, $field_callback);
    }

    /**
     * Create <textarea> field
     */
    protected function field_textarea($id, $label, $options = [])
    {
        $options = $this->merge_shared_field_options($options);

        // keys added to options to be added as HTML attributes
        $options['id']    = $id;
        $options['name']  = $this->get_name_attribute($id);
        $options['class'] = isset($options['class']) ? $options['class'] . ' regular-text' : 'regular-text';

        $field_callback = function () use ($options) {

            $id          = $options['id'];
            $default     = self::get_arr_key($options, 'default', '');
            $value       = self::get_arr_key($this->get_settings(), $id, $default);
            $atts        = self::extract_atts_by_type($options, 'textarea');
            $description = self::get_arr_key($options, 'description', ''); ?>

            <textarea <?php echo self::create_attribute_string($atts) ?>><?php esc_html_e($value); ?></textarea>
<?php
            self::print_text($description, 'p');
        };

        $this->register_field($id, $label, $options, $field_callback);
    }


    /**
     * Creates WYSIWYG field. $options are for wp_editor() function. $options are for third arg in wp_editor() function.
     * See options at: https://developer.wordpress.org/reference/classes/_wp_editors/parse_settings/
     */
    protected function field_wysiwyg($id, $label, $options)
    {
        $options = $this->merge_shared_field_options($options);

        $options['id'] = $id;
        $options['textarea_name'] = $this->get_name_attribute($id);

        $field_callback = function () use ($options) {
            $id          = $options['id'];
            $description = self::get_arr_key($options, 'description', '');

            wp_editor($this->get_settings($id), $id, $options);
            self::print_text($description, 'p');
        };

        $this->register_field($id, $label, $options, $field_callback);
    }
}
