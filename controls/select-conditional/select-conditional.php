<?php

namespace Themeshark_Elementor\Controls;

use Elementor\Control_Select;
use Themeshark_Elementor\Inc\Helpers;

if (!defined('ABSPATH')) exit;

/**
 * Control select
 * @var \Elementor\Base_Control;
 */
class Control_Select_Conditional extends Control_Select
{
    const ID = 'select-conditional';

    public function get_type()
    {
        return self::ID;
    }

    protected function get_default_settings()
    {
        return [
            'option_sets' => [],
            'defaults' => []
        ];
    }

    public function enqueue()
    {
        wp_enqueue_style('select-conditional',  Helpers::get_dir_url(__DIR__, 'select-conditional.css'));
        wp_enqueue_script('select-conditional', Helpers::get_dir_url(__DIR__, 'select-conditional.js'));
    }
}
