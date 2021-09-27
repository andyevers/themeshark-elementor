<?php

namespace Themeshark_Elementor\Controls;

if (!defined('ABSPATH')) exit;

use \Elementor\Controls_Stack;
use \Elementor\Group_Control_Base;
use \Elementor\Controls_Manager as CM;
use \Themeshark_Elementor\Inc\Shorthand_Controls as SC;


/**
 * ThemeShark Group Control Transform
 *
 * For adding transform properties to widgets
 *
 * @since 1.0.0
 */
class Group_Control_Transform extends Group_Control_Base
{
    protected static $fields;

    public static function get_type()
    {
        return 'transform-controls';
    }

    protected function init_fields()
    {
        $controls = [];

        $controls['translate_x'] = [
            'label'       => SC::_('Translate X'),
            'type'        => CM::SLIDER,
            'render_type' => 'ui',
            'size_units'  => ['px', '%', 'vw'],
            'range'       => SC::range(['px', -500, 500], ['%', -200, 200], ['vw', -150, 150]),
            'responsive'  => true,
            'selectors'   => [
                '{{SELECTOR}}' => '--gc-transform-translate_x_: {{SIZE || 0}}{{UNIT || px}}',
            ],
            'device_args' => [
                Controls_Stack::RESPONSIVE_TABLET => [
                    'selectors' => [
                        '{{SELECTOR}}' => '--gc-transform-translate_x_: {{SIZE}}{{UNIT}}'
                    ],
                ],
                Controls_Stack::RESPONSIVE_MOBILE => [
                    'selectors' => [
                        '{{SELECTOR}}' => '--gc-transform-translate_x_: {{SIZE}}{{UNIT}}'
                    ],
                ],
            ],
        ];

        $controls['translate_y'] = [
            'label'       => SC::_('Translate Y'),
            'type'        => CM::SLIDER,
            'render_type' => 'ui',
            'size_units'  => ['px', '%', 'vh'],
            'range'       => SC::range(['px', -500, 500], ['%', -200, 200], ['vh', -150, 150]),
            'responsive'  => true,
            'selectors'   => [
                '{{SELECTOR}}' => '--gc-transform-translate_y_: {{SIZE || 0}}{{UNIT || px}}',
            ],
            'device_args' => [
                Controls_Stack::RESPONSIVE_TABLET => [
                    'selectors' => [
                        '{{SELECTOR}}' => '--gc-transform-translate_y_: {{SIZE}}{{UNIT}}'
                    ],
                ],
                Controls_Stack::RESPONSIVE_MOBILE => [
                    'selectors' => [
                        '{{SELECTOR}}' => '--gc-transform-translate_y_: {{SIZE}}{{UNIT}}'
                    ],
                ],
            ],
        ];

        $controls['scale_x'] = [
            'label'       => SC::_('Scale X'),
            'type'        => CM::SLIDER,
            'render_type' => 'ui',
            'range'       => SC::range(['px', 0, 3, 0.01]),
            'responsive'  => true,
            'separator'   => 'before',
            'selectors'   => [
                '{{SELECTOR}}' => '--gc-transform-scale_x_: {{SIZE || 1}}',
            ],
            'device_args' => [
                Controls_Stack::RESPONSIVE_TABLET => [
                    'selectors' => [
                        '{{SELECTOR}}' => '--gc-transform-scale_x_: {{SIZE}}'
                    ],
                ],
                Controls_Stack::RESPONSIVE_MOBILE => [
                    'selectors' => [
                        '{{SELECTOR}}' => '--gc-transform-scale_x_: {{SIZE}}'
                    ],
                ],
            ],
        ];

        $controls['scale_y'] = [
            'label'       => SC::_('Scale Y'),
            'type'        => CM::SLIDER,
            'render_type' => 'ui',
            'range'       => SC::range(['px', 0, 3, 0.01]),
            'responsive'  => true,
            'selectors'   => [
                '{{SELECTOR}}' => '--gc-transform-scale_y_: {{SIZE || 1}}',
            ],
            'device_args' => [
                Controls_Stack::RESPONSIVE_TABLET => [
                    'selectors' => [
                        '{{SELECTOR}}' => '--gc-transform-scale_y_: {{SIZE}}'
                    ],
                ],
                Controls_Stack::RESPONSIVE_MOBILE => [
                    'selectors' => [
                        '{{SELECTOR}}' => '--gc-transform-scale_y_: {{SIZE}}'
                    ],
                ],
            ],
        ];

        $controls['skew_x'] = [
            'label'       => SC::_('Skew X'),
            'type'        => CM::SLIDER,
            'render_type' => 'ui',
            'range'       => SC::range(['px', -90, 90]),
            'responsive'  => true,
            'separator'   => 'before',
            'selectors'   => [
                '{{SELECTOR}}' => '--gc-transform-skew_x_: {{SIZE || 0}}deg',
            ],
            'device_args' => [
                Controls_Stack::RESPONSIVE_TABLET => [
                    'selectors' => [
                        '{{SELECTOR}}' => '--gc-transform-skew_x_: {{SIZE}}deg'
                    ],
                ],
                Controls_Stack::RESPONSIVE_MOBILE => [
                    'selectors' => [
                        '{{SELECTOR}}' => '--gc-transform-skew_x_: {{SIZE}}deg'
                    ],
                ],
            ],
        ];

        $controls['skew_y'] = [
            'label'       => SC::_('Skew Y'),
            'type'        => CM::SLIDER,
            'render_type' => 'ui',
            'range'       => SC::range(['px', -90, 90]),
            'responsive'  => true,
            'selectors'   => [
                '{{SELECTOR}}' => '--gc-transform-skew_y_: {{SIZE || 0}}deg',
            ],
            'device_args' => [
                Controls_Stack::RESPONSIVE_TABLET => [
                    'selectors' => [
                        '{{SELECTOR}}' => '--gc-transform-skew_y_: {{SIZE}}deg'
                    ],
                ],
                Controls_Stack::RESPONSIVE_MOBILE => [
                    'selectors' => [
                        '{{SELECTOR}}' => '--gc-transform-skew_y_: {{SIZE}}deg'
                    ],
                ],
            ],
        ];




        $controls['rotate'] = [
            'label'       => SC::_('Rotate'),
            'type'        => CM::SLIDER,
            'render_type' => 'ui',
            'range'       => SC::range(['px', -360, 360]),
            'responsive'  => true,
            'separator'   => 'before',
            'selectors'   => [
                '{{SELECTOR}}' => '--gc-transform-rotate_: {{SIZE || 0}}deg',
            ],
            'device_args' => [
                Controls_Stack::RESPONSIVE_TABLET => [
                    'selectors' => [
                        '{{SELECTOR}}' => '--gc-transform-rotate_: {{SIZE}}deg'
                    ],
                ],
                Controls_Stack::RESPONSIVE_MOBILE => [
                    'selectors' => [
                        '{{SELECTOR}}' => '--gc-transform-rotate_: {{SIZE}}deg'
                    ],
                ],
            ],
        ];


        $controls['origin_x'] = [
            'label'       => SC::_('Transform Origin X'),
            'type'        => CM::SLIDER,
            'render_type' => 'ui',
            'size_units'  => ['px', '%'],
            'range'       => SC::range(['px', -200, 200], ['%', 0, 100]),
            'default'     => SC::range_default('%'),
            'responsive'  => true,
            'separator'   => 'before',
            'selectors'   => [
                '{{SELECTOR}}' => '--gc-transform-origin_x_: {{SIZE || 50}}{{UNIT || %}}'
            ],
            'device_args' => [
                Controls_Stack::RESPONSIVE_TABLET => [
                    'selectors' => [
                        '{{SELECTOR}}' => '--gc-transform-origin_x_: {{SIZE}}{{UNIT}}',
                    ],
                ],
                Controls_Stack::RESPONSIVE_MOBILE => [
                    'selectors' => [
                        '{{SELECTOR}}' =>  '--gc-transform-origin_x_: {{SIZE}}{{UNIT}}',
                    ],
                ],
            ],
        ];

        $controls['origin_y'] = [
            'label'       => SC::_('Transform Origin Y'),
            'type'        => CM::SLIDER,
            'render_type' => 'ui',
            'size_units'  => ['px', '%'],
            'range'       => SC::range(['px', -200, 200], ['%', 0, 100]),
            'default'     => SC::range_default('%'),
            'responsive'  => true,
            'selectors'   => [
                '{{SELECTOR}}' => '--gc-transform-origin_y_: {{SIZE || 50}}{{UNIT || %}}'
            ],
            'device_args' => [
                Controls_Stack::RESPONSIVE_TABLET => [
                    'selectors' => [
                        '{{SELECTOR}}' => '--gc-transform-origin_y_: {{SIZE}}{{UNIT}}',
                    ],
                ],
                Controls_Stack::RESPONSIVE_MOBILE => [
                    'selectors' => [
                        '{{SELECTOR}}' =>  '--gc-transform-origin_y_: {{SIZE}}{{UNIT}}',
                    ],
                ],
            ],

        ];

        $translate_x = 'translateX(var(--gc-transform-translate_x_))';
        $translate_y = 'translateY(var(--gc-transform-translate_y_))';
        $scale_x     = 'scaleX(var(--gc-transform-scale_x_))';
        $scale_y     = 'scaleY(var(--gc-transform-scale_y_))';
        $skew_x      = 'skewX(var(--gc-transform-skew_x_))';
        $skew_y      = 'skewY(var(--gc-transform-skew_y_))';
        $rotate      = 'rotate(var(--gc-transform-rotate_))';
        $origin_x    = 'var(--gc-transform-origin_x_)';
        $origin_y    = 'var(--gc-transform-origin_y_)';

        $controls['_transform_origin_'] = [
            'label'       => SC::_('Rotate'),
            'type'        => CM::HIDDEN,
            'separator'   => 'before',
            'default'     => "transform-origin: $origin_x $origin_y",
            'selectors'   => [
                '{{SELECTOR}}' => "{{VALUE}}",
            ],
        ];

        $controls['_transform_'] = [
            'label'       => SC::_('Rotate'),
            'type'        => CM::HIDDEN,
            'separator'   => 'before',
            'default'     => "transform: $translate_x $translate_y $scale_x $scale_y $skew_x $skew_y $rotate",
            'selectors'   => [
                '{{SELECTOR}}' => "{{VALUE}}",
            ],
        ];

        return $controls;
    }


    /**
     * used on 'fields_options' key. map each control to a css var, injects into fields options provided in second arg. overwrites standard transform vars
     * @param {Array} $vars_map control_id => --css-var
     * @param {Array} $fields_options fields options to be injected to 
     */
    public static function fields_vars_map($vars_map, $fields_options)
    {
        $units_map = [
            'translate_x' => '{{SIZE}}{{UNIT}}',
            'translate_y' => '{{SIZE}}{{UNIT}}',
            'scale_x'     => '{{SIZE}}',
            'scale_y'     => '{{SIZE}}',
            'skew_x'      => '{{SIZE}}deg',
            'skew_y'      => '{{SIZE}}deg',
            'rotate'      => '{{SIZE}}deg',
            'origin_x'    => '{{SIZE}}{{UNIT}}',
            'origin_y'    => '{{SIZE}}{{UNIT}}'
        ];

        foreach ($units_map as $control_id => $units) {
            $fields_options[$control_id]['device_args'] = [];
        }

        foreach ($vars_map as $control_id => $css_var) {
            if (!in_array($control_id, array_keys($units_map))) wp_die("$control_id is not a valid transform control");
            if (!isset($fields_options[$control_id])) $fields_options[$control_id] = [];

            $value_suffix = $units_map[$control_id];
            $fields_options[$control_id]['selectors'] = ['{{SELECTOR}}' => "$css_var: $value_suffix"];
        }

        //removes default transform
        $fields_options['_transform_']['default'] = '';
        $fields_options['_transform_origin_']['default'] = '';
        $fields_options['_transform_']['selectors'] = [];
        $fields_options['_transform_origin_']['selectors'] = [];

        return $fields_options;
    }

    protected function get_default_options()
    {
        return [
            'popover' => [
                'starter_name'  => 'transform_controls',
                'starter_title' => SC::_('Transform'),
                'settings'      => ['render_type' => 'ui'],
            ],
        ];
    }
}
