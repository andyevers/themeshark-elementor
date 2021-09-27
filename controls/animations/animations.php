<?php

namespace Themeshark_Elementor\Controls;

use \Elementor\Controls_Manager as CM;
use \Themeshark_Elementor\Inc\Helpers;
use \Themeshark_Elementor\Inc\TS_Error;
use \Themeshark_Elementor\Inc\Shorthand_Controls;
use \Themeshark_Elementor\Controls\Controls_Handler as CH;

if (!defined('ABSPATH')) exit;

/**
 * ThemeShark Animation Controls
 * 
 * Adds animation controls which take priority over the default widget animations. 
 * 
 * @since 1.0.0
 */
final class Animations
{

    private static $_instance = null;

    public static function register()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct()
    {
        //add animation attributes 
        add_action('elementor/element/after_add_attributes', [$this, 'add_animation_attributes']);

        //addon animation 
        add_action('elementor/element/section/section_effects/before_section_end', [$this, 'add_addon_animation_controls']);
        add_action('elementor/element/column/section_effects/before_section_end', [$this, 'add_addon_animation_controls']);
        add_action('elementor/element/common/section_effects/before_section_end', [$this, 'add_addon_animation_controls']);

        //add animation scripts
        add_action('elementor/frontend/after_enqueue_scripts', [$this, 'enqueue_animation_scripts']);
        add_filter('elementor/controls/animations/additional_animations', [$this, 'add_additional_animations']);

        $this->ensure_no_duplicate_animations();
    }

    public function enqueue_animation_scripts()
    {
        $dir_url = Helpers::get_dir_url(__DIR__);
        wp_enqueue_script('ts-animations', "$dir_url/animations.js", ['elementor-frontend', 'ts-functions'], false, true);
        wp_enqueue_style('ts-animations', "$dir_url/animations.css");
    }

    public static $custom_transform_identifier = '__ts_transform__';

    private static $themeshark_additional_animations = [
        'ThemeShark Fade In Short'      => [
            'tsFadeInShortDown'     => 'Fade In Short Down',
            'tsFadeInShortLeft'     => 'Fade In Short Left',
            'tsFadeInShortRight'    => 'Fade In Short Right',
            'tsFadeInShortUp'       => 'Fade In Short Up',
        ],
        'ThemeShark Custom Transform'   => [
            '__ts_transform__transform'         => 'Custom Transform',
            '__ts_transform__transformFadeIn'   => 'Custom Transform Fade In',
        ]
    ];

    private static function get_additional_animation_ids()
    {
        $animation_ids = [];
        foreach (self::$themeshark_additional_animations as $group => $animations) {
            foreach ($animations as $id => $label) {
                $animation_ids[] = $id;
            }
        }
        return $animation_ids;
    }

    /**
     * This is used to add animations from any module. animations must go through here or they will be overwritten. To require the custom transform control, start animation name with __ts_transform__ and use settings with css var '--ts-starting-transform_'
     */
    public static function add_themeshark_animations($animation_array)
    {
        $animations = array_merge(self::$themeshark_additional_animations, $animation_array);
        self::$themeshark_additional_animations = $animations;
    }

    public static function get_custom_transform_animations()
    {
        $animation_ids = self::get_additional_animation_ids();
        $custom_transform_animations = [];
        if (!is_array($animation_ids)) $animation_ids = [$animation_ids];
        foreach ($animation_ids as $animation_id) {
            if (strpos($animation_id, self::$custom_transform_identifier) === 0) {
                $custom_transform_animations[] = $animation_id;
            }
        }
        return $custom_transform_animations;
    }


    private function ensure_no_duplicate_animations()
    {
        $dupe_array = [];
        $additional_animation_ids = self::get_additional_animation_ids();
        foreach ($additional_animation_ids as $animation_id) {
            if (in_array($animation_id, $dupe_array)) TS_Error::die("found duplicate animation id: $animation_id");
            $dupe_array[] = $animation_id;
        }
    }

    /**
     * adds the custom animations to the dropdown
     */
    public function add_additional_animations()
    {
        return self::$themeshark_additional_animations;
    }

    /**
     * Adds controls & updates animation control for default widget animations
     */
    public function add_addon_animation_controls(\Elementor\Element_Base $element)
    {
        $SC = new Shorthand_Controls($element);
        $el_name = $element->get_name();


        //widgets have '_' prefix to allow for custom animations as well
        $animation_key           = $el_name === 'common' ? '_animation' : 'animation';
        $animation_repeat_key    = $el_name === 'common' ? '_animation_repeat' : 'animation_repeat';
        $animation_threshold_key = $el_name === 'common' ? '_animation_threshold' : 'animation_threshold';


        // adds class that makes the default waypoint get destroyed to be watched using themeshark scroll observer
        $element->update_control($animation_key, [
            'prefix_class'      => ' themeshark-observed-animation '
        ]);

        //Inject custom animation controls after animation selection
        $element->start_injection($SC::set_position('after', $animation_key, 'control', false));

        // custom transform
        $SC->group_control('_custom_animation_transform_before', Group_Control_Transform::get_type(), [
            'condition'      => [$animation_key => self::get_custom_transform_animations()],
            'fields_options' =>  Group_Control_Transform::fields_vars_map([
                'translate_x'   => '--ts-translate-x_',
                'translate_y'   => '--ts-translate-y_',
                'scale_x'       => '--ts-scale-x_',
                'scale_y'       => '--ts-scale-y_',
                'skew_x'        => '--ts-skew-x_',
                'skew_y'        => '--ts-skew-y_',
                'rotate'        => '--ts-rotate_',
                'origin_x'      => '--ts-origin-x_',
                'origin_y'      => '--ts-origin-y_'
            ], ['__all'         => ['themeshark_settings' => [CH::RESET_WRAPPER_CLASS => 'animated']]]),
            'selector'       => '{{WRAPPER}}'
        ]);

        // repeat
        $SC->control($animation_repeat_key, 'Animation Reset', CM::SWITCHER, [
            'condition'           => ["$animation_key!" => ['', 'none']],
            'label_on'            => $SC::_('Yes'),
            'label_off'           => $SC::_('No'),
            'classes'             => 'themeshark-control-icon',
            'return_value'        => 'yes',
            'render_type'         => 'ui',
            'themeshark_settings' => [
                CH::RESET_WRAPPER_CLASS => 'animated'
            ],
        ]);

        // threshold
        //TODO: sometimes not enough of the element is visible and causes this to not fire.
        $SC->control($animation_threshold_key, 'Threshold', CM::HIDDEN, [
            'condition'           => ["$animation_key!" => ['', 'none']],
            'default'             => '0',
            'options'             => $SC::options_select(
                ['0',   'Default'],
                ['.25', '25% Visible'],
                ['.5',  '50% Visible'],
                ['.75', '75% Visible'],
                ['1',   'All Visible']
            ),
            'classes'             => 'themeshark-control-icon',
            'themeshark_settings' => [
                CH::RESET_WRAPPER_CLASS => 'animated'
            ],
        ]);

        //return if not widget
        if ($el_name !== 'common') {
            $element->end_injection();
            return;
        }

        //animation overwrite notice (for widgets only)
        $SC->control('_animation_overwritten_notice', null, CM::RAW_HTML, [
            'raw'               => '<strong>' . $SC::_('Warning: ') . '</strong>'
                . $SC::_('You already have another custom animation set for this widget, which causes this animation to be ignored.'),

            'content_classes'   => 'elementor-panel-alert elementor-panel-alert-danger',
            'render_type'       => 'ui',
            'condition'         => [
                'animation!'  => '',
                '_animation!' => ['', 'none']
            ]
        ]);

        $element->end_injection();
    }

    private static function get_if_has_val($element, $control_key)
    {
        if (!is_string($control_key)) return null;
        $has_val = $element->get_settings($control_key) !== null && !empty($element->get_settings($control_key));
        return $has_val ? $element->get_settings($control_key) : null;
    }

    /**
     * Adds additional settings to data-settings for animation controls. other animation settings are added by elementor
     */
    public function add_animation_attributes($element)
    {
        $el_name = $element->get_name();

        // return if no '_wrapper' render attribute
        $wrapper_attr = $element->get_render_attributes('_wrapper');
        if (!$wrapper_attr) return;

        //return if no data-settings
        $data_settings = isset($wrapper_attr['data-settings']) ? $wrapper_attr['data-settings'][0] : null;
        if (!$data_settings) return;

        //return if no animation
        $decoded_data_settings = json_decode($data_settings, true);
        if (!(isset($decoded_data_settings['animation']) || isset($decoded_data_settings['_animation']))) return;

        //If has animation, add the following:
        $animation_repeat_key           = 'animation_repeat';
        $animation_threshold_key        = 'animation_threshold';
        $animation_observed_element_key = 'animation_observed_element';

        // for widgets, check for custom animations and use those keys, otherwise use default animation keys
        $is_widget = $el_name !== 'section' && $el_name !== 'column';

        if ($is_widget) {
            $has_animation                  = self::get_if_has_val($element, 'animation');
            $animation_repeat_key           = $has_animation ? $animation_repeat_key : '_animation_repeat';
            $animation_threshold_key        = $has_animation ? $animation_threshold_key : '_animation_threshold';
            $animation_observed_element_key = $has_animation ? $animation_observed_element_key : '_animation_observed_element';
        }

        //get each animation setting and put it inside data-settings for the element with the default animations
        $animation_repeat           = $element->get_settings($animation_repeat_key);
        $animation_threshold        = $element->get_settings($animation_threshold_key);
        $animation_observed_element = $element->get_settings($animation_observed_element_key);

        $has_alternate_observed_el  = $animation_observed_element && $element->get_settings('animation_advanced_popover') === 'yes';

        //add new settings
        if ($animation_repeat)          $decoded_data_settings[$animation_repeat_key] = $animation_repeat;
        if ($animation_threshold)       $decoded_data_settings[$animation_threshold_key] = $animation_threshold;
        if ($has_alternate_observed_el) $decoded_data_settings[$animation_observed_element_key] = $animation_observed_element;

        //update data-settings
        $element->add_render_attribute('_wrapper', 'data-settings', wp_json_encode($decoded_data_settings), true);
    }

    public static function get_allowed_keys($settings, $possible_keys)
    {
        $required_keys = ['animation'];

        //validate not both include/exclude_keys
        if (isset($settings['include']) && isset($settings['exclude'])) {
            TS_Error::die('You cannot set both "include" and "exclude" keys in your animation settings.');
        }

        if (isset($settings['include'])) {
            foreach ($settings['include'] as $key) { //verify valid keys
                if (!in_array($key, $possible_keys)) TS_Error::die("$key is not an allowed animation control key");
            }
            foreach ($required_keys as $key) { //verify includes required keys
                if (!in_array($key, $settings['include'])) TS_Error::die("$key is a required animation control");
            }
            return $settings['include'];
        }

        if (isset($settings['exclude'])) {
            $filtered_keys = [];
            foreach ($possible_keys as $key) {
                if (!in_array($key, $settings['exclude'])) $filtered_keys[] = $key;
            }
            foreach ($required_keys as $key) {
                if (in_array($key, $settings['exclude'])) TS_Error::die("$key is a required animation control");
            }
            return $filtered_keys;
        }
        return $possible_keys;
    }


    /**
     * Creates an animation control group (main ID 'animation') for widgets. Cancels out animations set in the advanced settings. Only one allowed per widget.
     * @param $element controls stack instance for element
     * @param {Array} $settings accepted keys: ['animations', 'defaults', 'alternative_selectors'] 
     */
    public static function add_controls($element, $settings)
    {

        $el_name = $element->get_name();
        $allowed_keys = ['animations', 'defaults', 'alternative_selectors', 'exclude', 'include_only'];

        //validate accepted keys: 
        foreach ($settings as $key => $val) {
            if (!in_array($key, $allowed_keys)) {
                TS_Error::die("$key is not an allowed key for animation settings. accepted keys include: " . implode(', ', $allowed_keys));
            }
        }

        //validate is widget
        if ($el_name === 'section' || $el_name === 'column') {
            TS_Error::die('You cannot use controls_custom_animations() in sections or columns.');
        }

        //validate no other animation control
        if ($element->get_controls('animation')) {
            TS_Error::die('You can only have one animation control in your widget control stack.');
        }


        //get key or use empty array/null if not present
        $animation_options              = isset($settings['animations']) ? $settings['animations'] : [];
        $default_control_settings       = isset($settings['defaults']) ? $settings['defaults'] : [];
        $alternative_selectors_options  = isset($settings['alternative_selectors']) ? $settings['alternative_selectors'] : null;


        $SC               = new Shorthand_Controls($element);
        $animated_wrapper = '{{WRAPPER}}.animated.themeshark-custom-animation';
        $ts_settings      = [CH::RESET_WRAPPER_CLASS => 'animated'];

        //add default options
        $default_animation_options  = $SC::options_select(['', 'Default']);
        $options                    = array_merge($default_animation_options, $animation_options);

        $defaults = [
            'animation'                         => '',
            '_animation_duration'               => '',
            'animation_duration_custom'         => 2,
            'animation_delay'                   => '',
            'animation_repeat'                  => '',
            'animation_advanced_popover'        => '',
            'animation_timing_function'         => 'ease',
            'animation_iteration_count'         => '1',
            'animation_iteration_count_custom'  => 1,
            'animation_direction'               => 'normal',
            'animation_observed_element'        => '',
            'animation_advanced_popover'        => null, //no default
            'animation_iteration_count_custom'  => null, //no default
            'animation_threshold'               => null,
        ];

        $allowed_keys = self::get_allowed_keys($settings, array_keys($defaults));

        //update defaults if defaults provided
        foreach ($default_control_settings as $default_setting => $val) {
            $defaults[$default_setting] = $val;
        }

        $animation_condition = ['animation!' => ''];

        if (in_array('animation', $allowed_keys)) {
            $SC->control('animation', 'Animation', CM::SELECT, [
                'options'            => $options,
                'frontend_available' => true,
                'default'            => $defaults['animation'],
                'prefix_class'       => ' themeshark-observed-animation themeshark-custom-animation custom-animation-',
            ]);
        }


        if (in_array('_animation_duration', $allowed_keys)) {
            $options = [
                'slow'  => $SC::_('Slow'),
                ''      => $SC::_('Normal'),
                'fast'  => $SC::_('Fast'),
            ];
            $selectors_dictionary = [
                'slow'  => '--animation-duration: 2s',
                ''      => '--animation-duration: 1.25s',
                'fast'  => '--animation-duration: .75s',
            ];
            if (in_array('animation_duration_custom', $allowed_keys)) {
                $options['custom-duration']              = $SC::_('Custom');
                $selectors_dictionary['custom-duration'] = '';
            }
            $SC->control('_animation_duration', 'Animation Duration', CM::SELECT, [
                'condition'             => $animation_condition,
                'render_type'           => 'ui',
                'default'               => $defaults['_animation_duration'],
                'prefix_class'          => 'animated-',
                'options'               => $options,
                'selectors_dictionary'  => $selectors_dictionary,
                'themeshark_settings'   => $ts_settings,
                'selectors' => [
                    $animated_wrapper => '{{VALUE}}'
                ]
            ]);
        }

        if (in_array('animation_duration_custom', $allowed_keys)) {

            $condition = ['animation!' => ''];

            if (in_array('_animation_duration', $allowed_keys)) {
                $condition['_animation_duration'] = 'custom-duration';
            }

            $SC->control('animation_duration_custom', 'Duration (s)', CM::NUMBER, [
                'condition' => $condition,
                'frontend_available' => true,
                'required' => true,
                'min' => 0,
                'max' => 10,
                'step' => .05,
                'default' => $defaults['animation_duration_custom'],
                'themeshark_settings' => $ts_settings,
                'selectors' => [
                    "$animated_wrapper.animated-custom-duration" => '--animation-duration: {{VALUE}}s'
                ]
            ]);
        }

        if (in_array('animation_delay', $allowed_keys)) {
            $SC->control('animation_delay', 'Animation Delay (ms)', CM::NUMBER, [
                'condition' => $animation_condition,
                'render_type' => 'none',
                'frontend_available' => true,
                'default' => $defaults['animation_delay'],
                'min' => 0,
                'step' => 100,
            ]);
        }

        if (in_array('animation_repeat', $allowed_keys)) {
            $SC->control('animation_repeat', 'Animation Reset', CM::SWITCHER, [
                'condition' => $animation_condition,
                // 'description' => $SC::_('Resets animation when element scrolls out of view'),
                'label_on' => $SC::_('Yes'),
                'label_off' => $SC::_('No'),
                'default' => $defaults['animation_repeat'],
                'classes' => 'themeshark-control-icon',
                'return_value' => 'yes',
                'render_type' => 'ui',
                'themeshark_settings' => $ts_settings
            ]);
        }

        if (!in_array('animation_advanced_popover', $allowed_keys)) return;

        $SC->control('animation_advanced_popover', 'Advanced Settings', CM::POPOVER_TOGGLE, [
            'condition' => ['animation!' => ''],
            'label_off' => $SC::_('Default'),
            'label_on' => $SC::_('Custom'),
            'return_value' => 'yes'
        ]);

        $element->start_popover();

        $popover_condition = ['animation!' => '', 'animation_advanced_popover' => 'yes'];

        if (in_array('animation_timing_function', $allowed_keys)) {
            $SC->control('animation_timing_function', 'Timing Function', CM::SELECT, [
                'condition' => $popover_condition,
                'default' => $defaults['animation_timing_function'],
                'options' => $SC::options_select(
                    ['ease', 'Ease'],
                    ['ease-in', 'Ease In'],
                    ['ease-out', 'Ease Out'],
                    ['ease-in-out', 'Ease In Out'],
                    ['linear', 'Linear']
                ),
                'selectors' => [
                    $animated_wrapper => '--animation-timing-function: {{VALUE}}'
                ],
            ]);
        }

        if (in_array('animation_iteration_count', $allowed_keys)) {
            $options = ['1' => $SC::_('Default'), 'infinite' => $SC::_('Infinite')];
            if (in_array('animation_iteration_count_custom', $allowed_keys))  $options[''] = $SC::_('custom');

            $SC->control('animation_iteration_count', 'Iteration Count', CM::SELECT, [
                'condition' => $popover_condition,
                'default' => $defaults['animation_iteration_count'],
                'options' => $options,
                'themeshark_settings' => $ts_settings,
                'selectors' => [
                    $animated_wrapper => '--animation-iteration-count: {{VALUE}}'
                ]
            ]);
        }

        if (in_array('animation_iteration_count_custom', $allowed_keys)) {
            $SC->control('animation_iteration_count_custom', 'Iterations', CM::NUMBER, [
                'condition' => ['animation_iteration_count' => '', 'animation!' => '', 'animation_advanced_popover' => 'yes'],
                'frontend_available' => true,
                'min' => 0,
                'max' => 10,
                'step' => 1,
                'default' => $defaults['animation_iteration_count_custom'],
                'render_type' => 'ui',
                'themeshark_settings' => $ts_settings,
                'selectors' => [
                    $animated_wrapper => '--animation-iteration-count: {{animation_iteration_count.VALUE || VALUE}}'
                ]
            ]);
        }

        if (in_array('animation_direction', $allowed_keys)) {
            $SC->control('animation_direction', 'Direction', CM::SELECT, [
                'condition' => $popover_condition,
                'default' => $defaults['animation_direction'],
                'options' => $SC::options_select(
                    ['normal', 'Normal'],
                    ['reverse', 'Reverse'],
                    ['alternate', 'Alternate'],
                    ['alternate-reverse', 'Alternate Reverse']
                ),
                'themeshark_settings' => $ts_settings,
                'selectors' => [
                    $animated_wrapper => '--animation-direction: {{VALUE}}'
                ]
            ]);
        }

        // if (in_array('animation_threshold', $allowed_keys)) {

        //     $SC->control('animation_threshold', 'Threshold', CM::HIDDEN, [
        //         'condition' => $popover_condition,
        //         'default' => '0',
        //         'options' => $SC::options_select(
        //             ['0', 'Default'],
        //             ['.25', '25% Visible'],
        //             ['.5', '50% Visible'],
        //             ['.75', '75% Visible'],
        //             ['1', 'All Visible']
        //         ),
        //         // 'description' => $SC::_('Threshold = % of the element that needs to be visible before the animation starts'),
        //         'themeshark_settings' => $ts_settings,
        //     ]);
        // }

        if (in_array('animation_observed_element', $allowed_keys)) {
            if ($alternative_selectors_options) {
                $default_alternative_selectors_options = $SC::options_select(['', 'Default']);
                $selectors_options = array_merge($default_alternative_selectors_options, $alternative_selectors_options);

                $SC->control('animation_observed_element', 'Observed Element', CM::SELECT, [
                    'condition' => $popover_condition,
                    'themeshark_settings' => $ts_settings,
                    'render_type' => 'ui',
                    'default' => $defaults['animation_observed_element'],
                    'options' => $selectors_options,
                ]);
            }
        }
        $element->end_popover();
    }
}
