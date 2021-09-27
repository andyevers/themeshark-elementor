<?php

namespace Themeshark_Elementor\Inc;

use Elementor\Group_Control_Image_Size;
use \Themeshark_Elementor\Inc\TS_Error;
use \Themeshark_Elementor\Controls\Controls_Handler as CH;
use Elementor\Icons_Manager;
use Elementor\Utils;


if (!defined('ABSPATH')) exit;

class Shorthand_Controls
{

    /**
     * Elementor Element_Base instance
     */
    public $controls_stack = null;

    /**
     * Accepts an elementor Controls_Stack instance and provides shorthand functions for adding controls
     * @param {\Elementor\Controls_Stack} Controls stack for element (widget, column, section)
     */
    public function __construct($controls_stack)
    {
        $this->controls_stack = $controls_stack;
    }

    /**
     * Shorthand for $this->add_control.
     * @param {String} $id - control ID ex: 'border_width'
     * @param {String} $label - control Label ex: 'Width'
     * @param {String} $type - control type ex: Controls_Manager::SLIDER
     * @param {Array} $settings - Control Settings ex: ['selectors' => self::selectors(['.widget-border' => [width: {{SIZE}}{{UNIT}}])]
     * @param {Array} $options (optional) options for $this->add_control($id, $settings, $options);
     */
    public function control($id, $label, $type, $settings = [], $options = [])
    {
        $control_settings = $this->create_control_settings($label, $type, $settings);
        $this->controls_stack->add_control($id, $control_settings, $options);
    }



    /**
     * Shorthand for $this->add_responsive_control.
     * @param {String} $id - control ID ex: 'border_width'
     * @param {String} $label - control Label ex: 'Width'
     * @param {String} $type - control type ex: Controls_Manager::SLIDER
     * @param {Array} $settings - Control Settings ex: ['selectors' => self::selectors(['.widget-border' => [width: {{SIZE}}{{UNIT}}])]
     * @param {Array} $options (optional) options for $this->add_control($id, $settings, $options);
     */
    public function responsive_control($id, $label, $type, $settings = [], $options = [])
    {
        $control_settings = $this->create_control_settings($label, $type, $settings);
        $this->controls_stack->add_responsive_control($id, $control_settings, $options);
    }


    /**
     * Shorthand for $this->add_group_control.
     * @param {String} $id - ex: widget_text
     * @param {String} $type - ex: Group_Control_Typography::get_type()
     * @param {Array} $settings - Control Settings ex: ['selector' => '{{WRAPPER}} .widget-text', 'condition' => ['some_control' => 'required_value']]
     * @param {Array} $options (optional) options for $this->add_group_control($id, $settings, $options);
     */
    public function group_control($id, $type, $settings = [], $options = [])
    {
        $group_settings = [];
        foreach ($settings as $key => $val) $group_settings[$key] = $val;
        $group_settings['name'] = $id;
        $this->controls_stack->add_group_control($type, $group_settings, $options);
    }


    /**
     * Formats shorthand control settings to be used in the default elementor widget control settings by adding the type and label to the settings
     * @param {String} $label - Control Label
     * @param {String} $type - Control Type
     * @param {Array} $settings - Control Settings
     */
    private function create_control_settings($label, $type, $settings = [])
    {
        $control_settings = [];
        foreach ($settings as $key => $val) $control_settings[$key] = $val;
        if ($type) $control_settings['type'] = $type;
        if ($label) $control_settings['label'] = self::_($label);
        return $control_settings;
    }


    //-------------------------------------//
    //---------- STICKY CONTROLS ----------//
    //-------------------------------------//

    /**
     * Creates a sticky control that targets the provided selectors when the widget or its parents have 'elementor-sticky--effects' class.
     * @param {String} $id - control ID ex: 'color_sticky'
     * @param {String} $label - control Label ex: 'Color Sticky'
     * @param {String} $type - control type ex: Controls_Manager::COLOR
     * @param {Array} $settings - Control Settings ex: ['selectors' => self::selectors(['.widget-text' => [color: {{VALUE}}])]
     * @param {Array} $options (optional) options for $this->add_control($id, $settings, $options);
     */
    public function sticky_control($id, $label, $type, $settings = [], $options = [])
    {
        $update_selectors = isset($settings['update_selectors']) ? $settings['update_selectors'] : true;
        if ($update_selectors === true) $settings = $this->prepare_sticky_control_settings($settings);
        $this->control($id, $label, $type, $settings, $options);
    }

    /**
     * Creates a responsive sticky control that targets the provided selectors when the widget or its parents have 'elementor-sticky--effects' class.
     * @param {String} $id - control ID ex: 'color_sticky'
     * @param {String} $label - control Label ex: 'Color Sticky'
     * @param {String} $type - control type ex: Controls_Manager::COLOR
     * @param {Array} $settings - Control Settings ex: ['selectors' => self::selectors(['.widget-text' => [color: {{VALUE}}])
     * @param {Array} $options (optional) options for $this->add_responsive_control($id, $settings, $options);
     */
    public function sticky_responsive_control($id, $label, $type, $settings = [], $options = [])
    {
        $update_selectors = isset($settings['update_selectors']) ? $settings['update_selectors'] : true;
        if ($update_selectors === true) $settings = $this->prepare_sticky_control_settings($settings);
        $this->responsive_control($id, $label, $type, $settings, $options);
    }

    /**
     * Creates a group control that will target the provided selectors when the widget or it's parents become receive '.elementor-sticky--effects'
     * @param {String} $id - ex: widget_text
     * @param $type - ex: Group_Control_Typography::get_type()
     * @param {Array} $settings - Control Settings ex: ['selector' => '{{WRAPPER}} .widget-text', 'condition' => ['some_control' => 'required_value']]
     * @param {Array} $options (optional) options for $this->add_group_control($id, $settings, $options);
     */
    public function sticky_group_control($id, $type, $settings = [], $options = [])
    {
        $update_selectors = isset($settings['update_selectors']) ? $settings['update_selectors'] : true;
        if ($update_selectors === true && isset($settings['selector'])) {
            $settings['selector'] = self::prepare_sticky_selector($settings['selector']);
        }

        $this->group_control($id, $type, $settings, $options);
        $group_control_ids = array_keys($this->get_group_control_fields($id, $type));
        foreach ($group_control_ids as $control_id) {
            $this->add_control_classes($control_id, ['themeshark-sticky-control']);
            $this->add_control_themeshark_settings($control_id, [CH::REQUIRE_STICKY => true]);
        }
    }


    /**
     * Adds HTML classes to controls that have already been added to the Control Stack
     */
    public function add_control_classes($control_id, $classes)
    {
        $control_classes = $this->get_control_setting($control_id, 'classes', '');
        foreach ($classes as $class) $control_classes .= " $class";
        $this->controls_stack->update_control($control_id, ['classes' => $control_classes]);
    }


    /**
     * Adds settings to 'themeshark_settings' array in control. creates themeshark_settings array if it doesn't already exist
     */
    public function add_control_themeshark_settings($control_id, $themeshark_settings)
    {
        $ts_settings = $this->get_control_setting($control_id, 'themeshark_settings', []);
        foreach ($themeshark_settings as $key => $val) $ts_settings[$key] = $val;
        $this->controls_stack->update_control($control_id, ['themeshark_settings' => $ts_settings]);
    }


    /**
     * returns the setting of the control or $value_if_not_set as a fallback
     */
    public function get_control_setting($control_id, $setting_key, $value_if_not_set)
    {
        $control = $this->controls_stack->get_controls($control_id);
        return self::get_nested_value($control, $setting_key, $value_if_not_set);
    }


    /**
     * Creates a sticky control using the settings from another non-sticky control, and changes the selectors to target them when sticky. ex $this->control('color' ...$settings); $this->sticky_duplicate_control('color_sticky', 'color'). __NOTE__: Does not work on group controls
     * @param {String} $id - control ID for the sticky control ex: 'color_sticky'
     * @param {String} $duplicate_id - control ID of the control that you want to copy the settings from ex: 'color'
     * @param {Array} $updated_settings - settings that you want to be different from the copied ones. ex: 'label' => self::_('Color Sticky')
     * @param {Array} $options (optional) options for $this->add_control($id, $settings, $options);
     */
    public function sticky_duplicate_control($id, $duplicate_id, $updated_settings = [], $options = [])
    {
        $settings = $this->create_control_settings_from_duplicate($duplicate_id, $updated_settings);
        $label = self::get_nested_value($settings, 'label', null);
        $type = self::get_nested_value($settings, 'type', null);

        if (isset($settings['responsive'])) {
            $this->sticky_responsive_control($id, $label, $type, $settings, $options);
        } else {
            $this->sticky_control($id, $label, $type, $settings, $options);
        }
    }




    /**
     * Creates a RAW_HTML type control that notifies to turn on "Sticky" to use the sticky controls and provides a clickable link to the Motion Effects section. Appears only when sticky is turned off.
     * @param {String} $control_id - ID of control. ex: use_sticky_notice
     */
    public function preset_control_sticky_notice($control_id)
    {
        $tab_click_path = self::create_click_path_string([
            '.elementor-panel-navigation-tab.elementor-tab-control-advanced', // click advanced tab
            '.elementor-control-type-section.elementor-control-section_effects' // click section effects 
        ]);

        $this->control($control_id, null, \Elementor\Controls_Manager::RAW_HTML, [
            'classes' => 'themeshark-sticky-notice',
            'raw' => self::_('Turn on ')
                . '<strong>' . self::_('"Sticky"') . '</strong> '
                . self::_('under ')
                . '<a style="cursor:pointer;" onclick="' . $tab_click_path . '">' . self::_('Motion Effects') . '</a>'
                . self::_(' for the widget or a parent section to use sticky effects.')
        ]);
    }


    /**
     * Creates a RAW_HTML type control that notifies to turn on "Sticky" to use the sticky controls and provides a clickable link to the Motion Effects section. Appears only when sticky is turned off.
     * @param {String} $control_id - ID of control. ex: use_sticky_notice
     */
    public function preset_control_overflow_notice($control_id, $additional_settings = [])
    {
        $tab_click_path = self::create_click_path_string([
            '#elementor-panel-footer-settings', // click page settings
            '.elementor-panel-navigation-tab.elementor-tab-control-style',
        ]);

        $settings = array_merge([
            'raw' => self::_('Tip: Use ')
                . '<strong>' . self::_('"Hide Overflow X"') . '</strong>'
                . self::_(' in ')
                . '<strong><a style="cursor:pointer;" onclick="' . $tab_click_path . '">' . self::_('Page Settings') . '</a></strong>'
                . self::_(' if elements are wider than the page.')
        ], $additional_settings);

        $this->control($control_id, null, \Elementor\Controls_Manager::RAW_HTML, $settings);
    }

    /**
     * Simulates clicks in the elementor editor panel on the provided selectors in the order provided
     * @param {Array} $tab_selector_path each selector for the element that will be clicked
     */
    public static function create_click_path_string($tab_selectors = [])
    {
        $function_string = '(function(){ ';
        foreach ($tab_selectors as $selector) {
            $function_string .= 'elementor.getPanelView().$el.find(`' . $selector . '`).click(); ';
        }
        $function_string .= ' })()';
        return $function_string;
    }


    /**
     * Returns group control fields for a group type. if no group is provided, returns fields for all group types
     * @param {String} $group_type ex: 'typography'
     * @return {Array|\Elementor\Group_Control_Base}
     */
    private static function get_default_group_controls($group_type = null)
    {
        return \Elementor\Plugin::instance()->controls_manager->get_control_groups($group_type);
    }


    /**
     * Returns fields inside the current control stack that belong to a specified group. Must provide both the group ID and the type.
     * @param {String} $group_control_id id of the control group. ex: my_typography_group
     * @param {String} $group_type ex: 'typography'
     */
    public function get_group_control_fields($group_control_id, $group_type)
    {
        $group = self::get_default_group_controls($group_type);
        $field_names = array_keys($group->get_fields());
        $group_options = $group->get_options();
        $prefix = $group_control_id . '_';

        $controls = [];

        $popover_name = self::get_nested_value($group_options, ['popover', 'starter_name'], $group_type);
        $popover_id = $prefix . str_replace('-', '_', $popover_name);
        $popover = $this->controls_stack->get_controls($popover_id);
        if ($popover) $controls[$popover['name']] = $popover;

        //get group field controls
        foreach ($field_names as $field_name) {
            $control_id = $prefix . $field_name;
            $control = $this->controls_stack->get_controls($control_id);
            if ($control) $controls[$control['name']] = $control;
        }

        return $controls;
    }

    /**
     * Runs multiple selector => vals through the prepare_sticky_selector() function
     * @param {Array} $selectors - control 'selector' 
     */
    public static function prepare_sticky_selectors($selectors)
    {
        $formatted_selectors = [];
        foreach ($selectors as $key => $val) {
            $new_selector = self::prepare_sticky_selector($key);
            $formatted_selectors[$new_selector] = $val;
        }
        return $formatted_selectors;
    }

    /**
     * Modifies selectors to target the widget when it or any parent element has the class '.elementor-sticky--effects'
     * @param $selector - Control 'selector' key
     */
    public static function prepare_sticky_selector($selector)
    {
        $page_class = 'elementor-' . get_the_ID();

        $sticky_class = '.elementor-sticky--effects';
        $page_class = '.elementor-' . get_the_ID();
        $widget_class = ".elementor-element.elementor-element-{{ID}}";

        $new_key_self_sticky = str_replace('{{WRAPPER}}', "{{WRAPPER}}$sticky_class", $selector);
        $new_key_parent_sticky = str_replace('{{WRAPPER}}', "$page_class $sticky_class $widget_class", $selector);

        $new_selector = "$new_key_self_sticky, $new_key_parent_sticky";

        return $new_selector;
    }


    /**
     * Creates control settings based off of the settings from another control
     */
    private function create_control_settings_from_duplicate($duplicate_id, $settings = [])
    {
        $duplicate_settings = $this->controls_stack->get_controls($duplicate_id);

        if (!$duplicate_settings) { // if no settings found
            if (!$this->controls_stack->get_controls($duplicate_id)) { //check if there is a control
                TS_Error::die("not able to find control settings for: $duplicate_id. <br>Note that you cannot duplicate group controls");
            } else return $settings;
        }

        // filter out keys that will not be used from duplicated control
        $new_settings = [];
        $banned_keys = ['tab', 'inner_tab', 'tabs_wrapper', 'section', 'name'];
        foreach ($duplicate_settings as $key => $val) {
            if (in_array($key, $banned_keys)) continue;
            $new_settings[$key] = $val;
        }
        // add updated settings
        foreach ($settings as $key => $val) {
            $new_settings[$key] = $val;
        }
        return $new_settings;
    }

    /**
     * returns a value nested inside a multi-dimensional. returns $value_if_not_set as fallback. 
     * 
     * __Example__: get_nested_value([ 'first_level' => [ 'second_level' => [ 'my_key' => 10 ] ] ], ['first_level', 'second_level', 'my_key']) = 10
     * 
     * @param {Array} $array the multi-dimensional array that will be searched
     * @param {Array} $keys the path of keys that will be searched. ex: ['first_level', 'second_level']
     * @param $value_if_not_set fallback value if the path isn't set.
     */
    public static function get_nested_value($array, $keys = [], $value_if_not_set = null)
    {
        if (!is_array($keys)) $keys = [$keys];

        $cur_array = $array;
        foreach ($keys as $key) {
            if (isset($cur_array[$key])) {
                $cur_array = $cur_array[$key];
                continue;
            } else return $value_if_not_set;
        }
        return $cur_array;
    }

    /**
     * Searches multi-dimensional array for a value. Creates the path and sets the final key as $value if it is not set. If overwrite_value === true, the value will be overwritten with the $value argument
     * @param {Array} $array the multi-dimensional array that will be searched
     * @param {Array} $keys the path of keys that will be searched and that will be added if it doesn't already exist.
     * @param {Mixed} $value value that will be set for that path if it doesn't exist or that will overwrite the value if $overwrite_value === true
     * @param {Boolean} $overwrite_value whether $value arg should overwrite the value that may already exist
     * @return {Array} The array provided with the ensured path
     */
    public static function ensure_nested_value($array, $keys, $value, $overwrite_value = false)
    {
        $result = self::_ensure_nested_value($array, $keys, $value, $overwrite_value);
        return $result;
    }

    /**
     * called by ensure_nested_value because &$array & &$value can't accept variables when called directly.
     */
    private static function _ensure_nested_value(&$array, $pathParts, &$value, $overwrite_value = false)
    {
        $temp = &$array;
        foreach ($pathParts as $key) {
            $temp = &$temp[$key];
        }
        if ($overwrite_value === true) {
            $temp = $value;
        }
        return $array;
    }


    /**
     * Takes control settings and modifies the selector values to target sticky
     * @param {Array} $control_settings - Widget Control Settings
     */
    private function prepare_sticky_control_settings($control_settings)
    {
        $control_settings = self::ensure_nested_value($control_settings, ['themeshark_settings', CH::REQUIRE_STICKY], true, true);
        $classes = self::get_nested_value($control_settings, 'classes', '');

        $classes .= ' themeshark-sticky-control';
        $control_settings['classes'] = $classes;

        if (isset($control_settings['selectors'])) {
            $control_settings['selectors'] = self::prepare_sticky_selectors($control_settings['selectors']);
        }
        if (isset($control_settings['selector'])) {
            $control_settings['selector'] = self::prepare_sticky_selector($control_settings['selector']);
        }

        return $control_settings;
    }


    /**
     * Adds a size control for an image media control. this size will be retrieved when using $SC->get_image_html
     * @param {String} $image_control_id control id of the media control for the image
     * @param {Array} $args controls stack control args for image size group
     * @param {Array} $options controls stack control options for image size group
     */
    public function add_image_size_control($image_control_id, $args = [], $options = [], $is_sticky_control = false)
    {
        $args_default = [
            'fields_options' => [
                'size' => ['default' => 'large']
            ]
        ];
        if (sizeof($args) === 0) $args = $args_default;
        if ($is_sticky_control) $this->sticky_group_control($image_control_id, Group_Control_Image_Size::get_type(), $args, $options);
        else $this->group_control($image_control_id, Group_Control_Image_Size::get_type(), $args, $options);
    }

    /**
     * Gets image HTML from media control
     * @param {String} $image_control_id control id of the media control for the image
     * @param {Array|Boolean} $settings control stack settings to pull id from
     * @param {String|Boolean} $custom_image_size size of the image. ex: thumbnail. if false, uses $image_control_id . '_size' control
     */
    public function get_image_html($image_control_id, $settings = false, $custom_image_size = false)
    {
        $settings = $settings ? $settings : $this->controls_stack->get_settings();
        if ($custom_image_size) $settings[$image_control_id . '_size'] = $custom_image_size;
        $image_html = Group_Control_Image_Size::get_attachment_image_html($settings, $image_control_id);
        return $image_html;
    }

    /**
     * Renders Icon HTML from icon control
     * @param {String} $icon_control control for the icon to be rendered
     * @param {Array|Boolean} $settings control stack settings to pull id from
     * @param {String|Boolean} $attributes atts to be added to <i> element
     */
    public function render_icon($icon_control, $attributes = ['aria-hidden' => 'true', 'class' => 'fa-fw'])
    {
        Icons_Manager::render_icon($icon_control, $attributes);
    }


    public function render_social_icon($icon_control, $link_control, $attributes = [])
    {
        $link_key = '__social_icon__';
        $social = explode(' ', $icon_control['value'], 2);

        if (empty($social[1])) $social = '';
        else {
            $social = str_replace('fa-', '', $social[1]);
            if (strlen($social) > 0) $this->controls_stack->add_render_attribute($link_key, 'class', 'elementor-social-icon-' . $social);
        }

        $this->controls_stack->add_render_attribute($link_key, 'class', ['elementor-icon', 'elementor-social-icon']);
        $this->controls_stack->add_link_attributes($link_key, $link_control);
        if (sizeof($attributes) > 0) $this->controls_stack->add_render_attribute($link_key, $attributes);
?>
        <a <?php $this->controls_stack->print_render_attribute_string($link_key); ?>>
            <span class="elementor-screen-only"><?php esc_html_e($social); ?></span>

            <?php $this->render_icon($icon_control); ?>
        </a>
<?php
        $this->controls_stack->remove_render_attribute($link_key);
    }


    /**
     * returns valid hover effect groups or option set
     * @param {Array|String} $groups returns groups array if given array, otherwise returns options array if given string
     */
    public static function get_hover_effect_groups($groups = ['fade-in', 'move', 'enter', 'zoom'], $include_none = true)
    {
        $effect_groups = [
            'fade-in' => [
                'label' => self::_('Fade In'),
                'options' => self::options_select(
                    ['fade-in-', 'Fade In'],
                    ['fade-in-left', 'Fade In Left'],
                    ['fade-in-right', 'Fade In Right'],
                    ['fade-in-top', 'Fade In Top'],
                    ['fade-in-bottom', 'Fade In Bottom']
                ),
            ],
            'move' => [
                'label' => self::_('Move'),
                'options' => self::options_select(
                    ['move-right', 'Move Right'],
                    ['move-left', 'Move Left'],
                    ['move-up', 'Move Up'],
                    ['move-down', 'Move Down']
                ),
            ],
            'enter' => [
                'label' => self::_('Enter'),
                'options' => self::options_select(
                    ['enter-left', 'Enter Left'],
                    ['enter-right', 'Enter Right'],
                    ['enter-top', 'Enter Top'],
                    ['enter-bottom', 'Enter Bottom'],
                    ['enter-zoom', 'Enter Zoom']
                ),
            ],
            'zoom' => [
                'label' => self::_('Zoom'),
                'options' => self::options_select(
                    ['zoom-in', 'Zoom In'],
                    ['zoom-out', 'Zoom Out']
                )
            ]
        ];

        $requested_groups = [];

        if ($include_none) {
            $requested_groups['none'] = [
                'label'   => self::_('None'),
                'options' => self::options_select(['', 'None']),
            ];
        }

        if (is_array($groups)) {
            foreach ($groups as $group) {
                if (!isset($effect_groups[$group])) TS_Error::die("$group is not a valid hover effect group");
                $requested_groups[$group] = $effect_groups[$group];
            }
        } else {
            if (!isset($effect_groups[$groups])) TS_Error::die("$groups is not a valid hover effect group");
            $requested_groups = $effect_groups[$groups]['options'];
        }
        return $requested_groups;
    }

    public function ensure_hover_effect_attribute($render_attribute, $hover_animation = '')
    {
        if (!empty($hover_animation)) {
            $this->controls_stack->add_render_attribute($render_attribute, 'class', 'ts-effect--' . $hover_animation);
        }
    }

    public static function get_recommended_social_brands()
    {
        return [
            'android',
            'apple',
            'behance',
            'bitbucket',
            'codepen',
            'delicious',
            'deviantart',
            'digg',
            'dribbble',
            'elementor',
            'facebook',
            'flickr',
            'foursquare',
            'free-code-camp',
            'github',
            'gitlab',
            'globe',
            'houzz',
            'instagram',
            'jsfiddle',
            'linkedin',
            'medium',
            'meetup',
            'mix',
            'mixcloud',
            'odnoklassniki',
            'pinterest',
            'product-hunt',
            'reddit',
            'shopping-cart',
            'skype',
            'slideshare',
            'snapchat',
            'soundcloud',
            'spotify',
            'stack-overflow',
            'steam',
            'telegram',
            'thumb-tack',
            'tripadvisor',
            'tumblr',
            'twitch',
            'twitter',
            'viber',
            'vimeo',
            'vk',
            'weibo',
            'weixin',
            'whatsapp',
            'wordpress',
            'xing',
            'yelp',
            'youtube',
            '500px',
        ];
    }



    //-------------------------------------//
    //---------- STATIC HELPERS -----------//
    //-------------------------------------//

    /**
     * Used for 'selectors' key in control settings.
     * @param {Array} $selectors css selectors string. use key '_vars' => ['MY_VAR' => '.themeshark-widget'] to use in other selectors. ex: '%MY_VAR% .inner-wrap' == '.themeshark-widget .inner-wrap'
     * @param {String} $wrapper_addon_selectors add css selectors to {{WRAPPER}}, otherwise a space is automatically inserted. ex: {{WRAPPER}}.my-selector.
     * @param {Boolean} $add_wrapper_prefix whether "{{WRAPPER}}" should be added before the selectors string and after each ", " for each selector
     * @return {Array} selectors array for the elementor control 'selectors' key
     */
    public static function selectors($selectors = [], $wrapper_addon_selectors = '', $add_wrapper_prefix = true)
    {
        $vars = [];
        //check if vars are present
        if (isset($selectors['_vars'])) {
            foreach ($selectors['_vars'] as $key => $val) $vars[$key] = $val;
            unset($selectors['_vars']);
        };

        if (!$wrapper_addon_selectors) $wrapper_addon_selectors = '';

        //ensure that $add_wrapper_prefix === true if using $wrapper_addon_selectors.
        if (!empty($wrapper_addon_selectors) && $add_wrapper_prefix !== true) {
            TS_Error::die("cannot add wrapper selector $wrapper_addon_selectors. $add_wrapper_prefix must be set to true to use wrapper addon selectors");
        }

        $wrapper_prefix = "{{WRAPPER}}$wrapper_addon_selectors ";
        $formatted_selectors = [];
        foreach ($selectors as $selectors_str => $props_array) {
            if (!is_array($props_array)) {
                TS_Error::die('Properties must be an array when using Shorthand_Selectors::selectors()');
            }
            $selectors_str = $add_wrapper_prefix === true ?
                $wrapper_prefix . str_replace(',', ", $wrapper_prefix ", $selectors_str) : $selectors_str;

            //replace vars
            foreach ($vars as $key => $val) $selectors_str = str_replace("%$key%", $val, $selectors_str);
            $joint = substr($selectors_str, -1) === ';' ? ' ' : '; ';
            $props_str = implode($joint, $props_array);
            $formatted_selectors[$selectors_str] = $props_str;
        }

        return $formatted_selectors;
    }

    /**
     * Translates string using plugin text domain. same as doing __('text', THEMESHARK_TXTDOMAIN)
     * @param {String} $string - ex: 'text'
     * @return {String} Translated String
     */
    public static function _($string)
    {
        return __($string, 'elementor-themeshaark');
    }

    /**
     * Sets 'options' key for a SELECT type control. format: [val, title, icon]
     * @param {Arrays} ...$option_arrays - ex: ['left', 'Left'], ['right', 'Right']
     * @return {Array} ex: ['left' => __('Left', THEMESHARK_TXTDOMAIN), 'right' => __('Right', THEMESHARK_TXTDOMAIN)]
     */
    public static function options_select(...$option_arrays)
    {
        $option_setting = [];
        foreach ($option_arrays as $option) {
            $value = $option[0];
            $label = $option[1];
            $option_setting[$value] = self::_($label);
        }
        return $option_setting;
    }




    /**
     * Sets 'options' key for a CHOOSE type control. format: [val, title, icon]
     * @param {Arrays} ...$option_arrays - ex: ['left', 'Left', 'eicon-text-align-left'], ['right', 'Right', 'eicon-text-align-right']
     * @return {Array} ex: ['left' => ['title' => __('Left', THEMESHARK_TXTDOMAIN), 'icon' => 'eicon-text-align-left'], 'right' => ['title' => __('Right', THEMESHARK_TXTDOMAIN), 'icon' => 'eicon-text-align-right']]
     */
    public static function options_choose(...$option_arrays)
    {
        $option_setting = [];
        foreach ($option_arrays as $option) {
            $value = $option[0];
            $title = $option[1];
            $icon = $option[2];

            $option_setting[$value] = [
                'title' => self::_($title),
                'icon' => $icon
            ];
        }
        return $option_setting;
    }



    /**
     * Returns horizontal align options array for CM::CHOOSE controls
     * @return {Array}
     */
    public static function choice_set_h_align($choices = ['left', 'center', 'right'])
    {
        $prefix = 'eicon-h-align';
        $choice_dictionary = [
            'left' => self::options_choose(['left', 'Left', "$prefix-left"]),
            'center' => self::options_choose(['center', 'Center', "$prefix-center"]),
            'right' => self::options_choose(['right', 'Right', "$prefix-right"]),
        ];

        return self::create_choice_set($choices, $choice_dictionary);
    }

    /**
     * Returns vertical align options array for CM::CHOOSE controls
     * @return {Array}
     */
    public static function choice_set_v_align($choices = ['top', 'center', 'bottom'])
    {
        $prefix = 'eicon-v-align';
        $choice_dictionary = [
            'top' => self::options_choose(['top', 'Top', "$prefix-top"]),
            'center' => self::options_choose(['center', 'Center', "$prefix-middle"]),
            'bottom' => self::options_choose(['bottom', 'Bottom', "$prefix-bottom"]),
        ];

        return self::create_choice_set($choices, $choice_dictionary);
    }

    /**
     * Returns horizontal align options array for CM::CHOOSE controls
     * @return {Array}
     */
    public static function choice_set_text_align($choices = ['left', 'center', 'right', 'justify'])
    {
        $prefix = 'eicon-text-align';
        $choice_dictionary = [
            'left' => self::options_choose(['left', 'Left', "$prefix-left"]),
            'center' => self::options_choose(['center', 'Center', "$prefix-center"]),
            'right' => self::options_choose(['right', 'Right', "$prefix-right"]),
            'justify' => self::options_choose(['justify', 'Justified', "$prefix-justify"]),
        ];
        return self::create_choice_set($choices, $choice_dictionary);
    }


    /**
     * Creates choice options for CM::CHOOSE controls. if $choices array is associative, keys will be used as labels. otherwise uses defaults.
     * @param {String} $eicon_prefix the prefix to the element icon class that will be used
     * @param {Array} $choices choice options. 
     */
    private static function create_choice_set($choices, $choice_dictionary)
    {
        $options = [];
        if (!is_array($choices)) TS_Error::die("choices must be an array. received: $choices");
        foreach ($choices as $choice) {
            if (!isset($choice_dictionary[$choice])) TS_Error::die("$choice is not a recognized choice option.");
            $options = array_merge($options, $choice_dictionary[$choice]);
        }
        return $options;
    }


    /**
     * sets the allowed range and steps for each unit. format: [unit, min, max, step]. providing null skips the key. unit required.
     * @param {Arrays} ...$range_arrays - ex: ['px', 0, 400], ['%', 0, 100, 2], ['vh', null, 100]
     * @return {Array} ex: ['px' => ['min' => 0, 'max' => 400], '%' => ['min' => 0, 'max' => 100, 'step' => 2]]
     */
    public static function range(...$range_arrays)
    {
        $range_setting = [];
        foreach ($range_arrays as $range) {
            $unit = $range[0];
            $min = isset($range[1]) ? $range[1] : null;
            $max = isset($range[2]) ? $range[2] : null;
            $step = isset($range[3]) ? $range[3] : null;

            $range_setting[$unit] = [];
            if ($min) $range_setting[$unit]['min'] = $min;
            if ($max) $range_setting[$unit]['max'] = $max;
            if ($step) $range_setting[$unit]['step'] = $step;
        }
        return $range_setting;
    }

    /**
     * used for 'default' key in control settings when specifying a range default
     * @param {String} $unit ex: 'px'
     * @param {Number} $size ex: 100
     * @return {Array} ex: ['size' => 'px', 'unit' => 100]
     */
    public static function range_default($unit, $size = null)
    {
        $default_range = ['unit' => $unit];
        if ($size !== null) $default_range['size'] = $size;
        return $default_range;
    }

    /**
     * used for 'conditions' terms in control settings: 'conditions' => ['terms' => [ these values here ]]
     * @param {String} $name name of control
     * @param {String} $operator relation to the value. ex: '==', '!='
     * @param {String} $value required value of the named control
     * @return {Array} conditions terms array ['conditions']
     */
    public static function cond_term($name, $operator, $value)
    {
        $term = [
            'name' => $name,
            'operator' => $operator,
            'value' => $value
        ];
        return $term;
    }



    public static function set_position($position, $control_or_section_id, $type = 'control', $return_as_option_array = true)
    {
        $allowed_types = ['section', 'control'];

        if (!in_array($type, $allowed_types)) TS_Error::die("$type is not an allowed type");
        $allowed_positions = [
            'control' => ['before', 'after'],
            'section' => ['start', 'end']
        ];
        if (!in_array($position, $allowed_positions[$type])) TS_Error::die("$position is not an allowed position");

        $position = [
            'type' => $type,
            'at' => $position,
            'of' => $control_or_section_id
        ];

        return $return_as_option_array ? ['position' => $position] : $position;
    }


    //-------------------------------------//
    //--------- CONTENT TEMPLATE ----------//
    //-------------------------------------//

    // /**
    //  * ONLY USED FOR DEV PURPOSES Hacky way of using VSCode syntax highlighting for content template. recognizes everything between template_start() & template_end() functions. __NOTE__: only use this is development. change back to <# #> before release
    //  * - Replaces '\<SCRIPT>' and '\</SCRIPT>' with '<# ' and ' #>'
    //  * - Replaces '<_#_' and '</_#_' and '<#' and '#>'
    //  */
    // public static function template_start()
    // {
    //     ob_start(); // all html below here can be returned
    // }

    // /**
    //  * ONLY USED FOR DEV PURPOSES
    //  */
    // public static function template_end()
    // {
    //     $template = ob_get_clean();
    //     $template = str_replace('<SCRIPT>', '<# ', $template);
    //     $template = str_replace('</SCRIPT>', ' #>', $template);
    //     $template = str_replace('</_#_', '<#', $template);
    //     $template = str_replace('<_#_', '<#', $template);
    //     echo $template;
    // }
}
