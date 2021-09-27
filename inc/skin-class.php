<?php

namespace Themeshark_Elementor\Inc;

use \Elementor\Skin_Base as Elementor_Skin_Base;
use \Themeshark_Elementor\Inc\TS_Error;

if (!defined('ABSPATH')) exit;

abstract class TS_Skin extends Elementor_Skin_Base
{
    /**
     * Adds action: "elementor/element/$name/$section_id/$position" for controls to be injected
     * @param {String} $section_id id of section where controls will be injected
     * @param {String} $position choices: before_section_end, after_section_end, before_section_start, after_section_start
     * @param {Function} $controls_callback callback for injecting controls
     * @param {Int} $accepted_args number of arguments the callback will receive
     * $controls_callback vars ($widget, $section_args)
     */
    public function add_controls_injection($section_id, $position, $controls_callback, $accepted_args = 1)
    {
        $accepted_positions = ['before_section_end', 'after_section_end', 'before_section_start', 'after_section_start'];
        if (!in_array($position, $accepted_positions)) TS_Error::die("$position is not an accepted injection position");

        $name = $this->parent->get_name();

        add_action("elementor/element/$name/$section_id/$position", $controls_callback, 10, $accepted_args);
    }


    public function hide_parent_control($control_id)
    {
        $control_args = $this->parent->get_controls($control_id);

        if (!isset($control_args['condition'])) $control_args['condition'] = [];
        if (!isset($control_args['condition']['_skin!'])) $control_args['condition']['_skin!'] = [];
        if (!is_array($control_args['condition']['_skin!'])) $control_args['condition']['_skin!'] = [$control_args['condition']['_skin!']];
        $control_args['condition']['_skin!'][] = $this->get_id();

        $this->parent->update_control($control_id, $control_args);
    }

    /**
     * @return \Themeshark_Elementor\Widgets\TS_Widget
     */
    public function get_parent()
    {
        return $this->parent;
    }

    public function get_skin_control_setting($setting_key)
    {
        $setting_key = $this->get_control_id($setting_key);
        return $this->parent->get_settings($setting_key);
    }

    private $_SC = null;

    /**
     * @return \Themeshark_Elementor\Inc\Shorthand_Controls
     */
    public function shorthand_controls()
    {
        if (is_null($this->_SC)) $this->_SC = new Shorthand_Controls($this);
        return $this->_SC;
    }
}
