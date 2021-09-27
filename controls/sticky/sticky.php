<?php

namespace Themeshark_Elementor\Controls;

use \Themeshark_Elementor\Plugin;
use \Elementor\Controls_Manager as CM;
use \Themeshark_Elementor\Inc\Shorthand_Controls;

if (!defined('ABSPATH')) exit;

/**
 * ThemeShark Sticky Controls
 *
 * Adds sticky controls to already existing widgets/elements
 *
 * @since 1.0.0
 */
final class Sticky
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
        //Enqueue Editor Scripts
        add_action('elementor/editor/after_enqueue_scripts', [$this, 'editor_sticky_scripts']);

        // Sticky Instructions
        add_action("elementor/element/common/section_effects/before_section_end", [$this, 'inject_sticky_instructions']);
        add_action("elementor/element/section/section_effects/before_section_end", [$this, 'inject_sticky_instructions']);

        if (!Plugin::has_elementor_pro()) {
            require_once THEMESHARK_PATH . 'controls/sticky/sticky-nopro.php';
            new Sticky_Module();
        }
    }

    public function register_nopro()
    {

        add_action('elementor/widgets/widgets_registered', [$this, 'register_nopro']); //admin & frontend scripts register

        $dir_url = THEMESHARK_URL . 'controls/sticky';
        wp_enqueue_script('ts-sticky-nopro', "$dir_url/sticky-nopro.js", ['elementor-sticky'], false, true);
    }

    // activate sticky script
    public function editor_sticky_scripts()
    {
        $dir_url = THEMESHARK_URL . 'controls/sticky';
        wp_enqueue_script('ts-sticky', "$dir_url/sticky.js", ['ts-controls-handler'], false, true);
        wp_enqueue_style('ts-sticky', "$dir_url/sticky.css"); //editor widget icons
    }

    public function inject_sticky_instructions($element)
    {
        $SC = new Shorthand_Controls($element);
        $element->update_control('sticky', [
            'description' => '<strong>' . $SC::_('Note:') . '</strong> ' . $SC::_('Use "Sticky" on widgets or any of their parent sections to activate ThemeShark sticky effects.')
        ]);
    }

    /**
     * Injects sticky instructions control above the sticky position inside the section_effects common section
     * @param $element instance of the Control element,
     * @param $section_id id of the section found in $this->start_control_section('my_section_id', ...)
     */
    public function inject_sticky_column_instructions($element)
    {

        // see args and options https://github.com/elementor/elementor/blob/master/includes/base/controls-stack.php
        // see function get_position_info( array $position ) in controls-stack.php (link above). Can also use $this->start_injection($position_array) for multiple controls
        $SC = new Shorthand_Controls($element);
        $control_instructions_settings = [
            'raw' => '<strong>' . $SC::_('Note:') . '</strong> ' . $SC::_('Use "Sticky" On a parent section to use ThemeShark sticky controls.'),
            'separator' => 'before',
        ];

        //STICKY INSTRUCTIONS CONTROL
        $SC->control('_sticky_controls_instructions', null, CM::RAW_HTML, $control_instructions_settings, [
            'position' => [
                'type' => 'control',
                'at' => 'before',
                'of' => 'sticky',
            ]
        ]);
    }
}
