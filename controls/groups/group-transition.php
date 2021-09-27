<?php

namespace Themeshark_Elementor\Controls;

if (!defined('ABSPATH')) exit;

use \Elementor\Controls_Manager;
use \Elementor\Group_Control_Base;

/**
 * ThemeShark Group Control Transition
 *
 * For adding transition properties to widgets
 *
 * @since 1.0.0
 */
class Group_Control_Transition extends Group_Control_Base
{
    protected static $fields;

    public static function get_type()
    {
        return 'transition-controls';
    }

    protected function init_fields()
    {
        $controls = [];

        $controls['duration'] = [
            'label'     => __('Duration', THEMESHARK_TXTDOMAIN),
            'type'      => Controls_Manager::NUMBER,
            'min'       => 0,
            'max'       => 5,
            'default'   => .3,
            'step'      => 0.1,
            'selectors' => [
                '{{SELECTOR}}' => 'transition-duration: {{VALUE}}s'
            ],
        ];


        $controls['delay'] = [
            'label'     => __('Delay', THEMESHARK_TXTDOMAIN),
            'type'      => Controls_Manager::NUMBER,
            'min'       => 0,
            'max'       => 5,
            'default'   => 0,
            'step'      => 0.1,
            'selectors' => [
                '{{SELECTOR}}' => 'transition-delay: {{VALUE}}s'
            ],
        ];


        $controls['timing_function'] = [
            'label'   => __('Timing Function', THEMESHARK_TXTDOMAIN),
            'type'    => Controls_Manager::SELECT,
            'default' => 'ease',
            'options' => [
                'ease'        => __('Ease', THEMESHARK_TXTDOMAIN),
                'ease-in'     => __('Ease In', THEMESHARK_TXTDOMAIN),
                'ease-out'    => __('Ease Out', THEMESHARK_TXTDOMAIN),
                'ease-in-out' => __('Ease In Out', THEMESHARK_TXTDOMAIN),
                'linear'      => __('Linear', THEMESHARK_TXTDOMAIN),
            ],
            'selectors' => [
                '{{SELECTOR}}' => 'transition-timing-function: {{VALUE}}'
            ],
        ];

        return $controls;
    }

    protected function get_default_options()
    {
        return [
            'popover' => [
                'starter_name'  => 'transition_controls',
                'starter_title' => _x('Transition Settings', 'Transition Settings Controls', THEMESHARK_TXTDOMAIN),
                'settings'      => [
                    'render_type' => 'ui',
                    'separator'   => 'none'
                ],
            ],
        ];
    }
}
