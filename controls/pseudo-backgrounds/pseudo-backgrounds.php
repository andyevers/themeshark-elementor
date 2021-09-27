<?php

namespace Themeshark_Elementor\Controls;

use \Elementor\Controls_Stack;
use \Elementor\Group_Control_Border;
use \Elementor\Controls_Manager as CM;
use \Elementor\Group_Control_Background;
use \Elementor\Group_Control_Box_Shadow;
use \Themeshark_Elementor\Inc\Globals_Fixer;
use \Themeshark_Elementor\Controls\Animations;
use \Themeshark_Elementor\Inc\Shorthand_Controls;
use \Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use \Themeshark_Elementor\Controls\Controls_Handler as CH;


if (!defined('ABSPATH')) exit;

final class Pseudo_Backgrounds
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
        //Pseudo Sections
        add_action("elementor/element/common/section_effects/after_section_end", [$this, 'inject_pseudo_sections']);
        add_action("elementor/element/section/section_effects/after_section_end", [$this, 'inject_pseudo_sections']);
        add_action("elementor/element/column/section_effects/after_section_end", [$this, 'inject_pseudo_sections']);

        //Animation Settings Control
        add_action('elementor/element/section/section_effects/before_section_end', [$this, 'inject_pseudo_animation_settings']);
        add_action('elementor/element/column/section_effects/before_section_end', [$this, 'inject_pseudo_animation_settings']);
        add_action('elementor/element/common/section_effects/before_section_end', [$this, 'inject_pseudo_animation_settings']);

        //Pseudo BG Alert
        add_action("elementor/element/section/section_background/after_section_start", [$this, 'inject_pseudo_bg_alert']);
        add_action("elementor/element/column/section_style/after_section_start", [$this, 'inject_pseudo_bg_alert']);

        //add styles
        add_action('elementor/frontend/after_enqueue_styles', [$this, 'enqueue_pseudo_bg_styles']);

        Animations::add_themeshark_animations($this->custom_animations);
    }


    public $custom_animations = [
        'ThemeShark Pseudo Background' => [
            'tsPseudoGrowExpand' => 'Pseudo BG Grow Expand Scale',
            'tsPseudoGrowExpandDimensions' => 'Pseudo BG Grow Expand Dimensions'
        ],
        'ThemeShark Pseudo Custom Transform' => [
            '__ts_transform__pseudoTransform' => 'Pseudo BG Transform',
            '__ts_transform__pseudoTransformFadeIn' => 'Pseudo BG Transform Fade In'
        ]
    ];


    private function get_pseudo_animations()
    {
        $animation_ids = [];
        foreach ($this->custom_animations as $group => $animations) {
            foreach ($animations as $id => $label) {
                $animation_ids[] = $id;
            }
        }
        return $animation_ids;
    }


    public function enqueue_pseudo_bg_styles()
    {
        $dir_url = THEMESHARK_URL . 'controls/pseudo-backgrounds';
        wp_enqueue_style('ts-pseudo-backgrounds', "$dir_url/pseudo-backgrounds.css");
    }

    public static function get_pseudo_tab_click_path($element)
    {
        $SC = new Shorthand_Controls($element);
        $tab_click_path = $SC::create_click_path_string([
            '.elementor-panel-navigation-tab.elementor-tab-control-advanced', // click advanced tab
            '.elementor-control-type-section.elementor-control-_section_pseudo_bg_before' // click section effects 
        ]);

        return $tab_click_path;
    }

    public function inject_pseudo_bg_alert($element)
    {
        $SC = new Shorthand_Controls($element);

        $tab_click_path = self::get_pseudo_tab_click_path($element);

        $SC->control('has_pseudo_bg_alert', null, CM::RAW_HTML, [
            'condition' => ['_use_pseudo_bg_before' => 'on'],
            'raw'       => '<strong>' . $SC::_('Note:') . '</strong> '
                . $SC::_('This section has a')
                . "<a style='cursor:pointer' onclick='$tab_click_path'> " . $SC::_('pseudo background') . '</a>'
        ]);
    }

    public function inject_pseudo_animation_settings($element)
    {
        $SC = new Shorthand_Controls($element);
        $name = $element->get_name();

        $animation_key = $name === 'common' ? '_animation' : 'animation';
        $position_after_animation = $SC::set_position('after', $animation_key);

        $SC->responsive_control('_pseudo_animation_starting_scale', 'Animation Starting Scale', CM::SLIDER, [
            'condition'           => [$animation_key => 'tsPseudoGrowExpand'],
            'range'               => $SC::range(['px', 0, .3, 0.005]),
            'range_default'       => $SC::range_default('px', 0.015),
            'themeshark_settings' => [CH::RESET_WRAPPER_CLASS => 'animated'],
            'selectors'           => $SC::selectors([
                '{{WRAPPER}}.ts-pseudo-bg-before-on::before,
                 {{WRAPPER}}.ts-pseudo-bg-after-on::after' => [
                    '--animation-starting-scale: {{SIZE}}'
                ]
            ], null, false)
        ], $position_after_animation);

        $tab_click_path = self::get_pseudo_tab_click_path($element);

        $SC->control('_pseudo_animation_prefer_scale', null, CM::RAW_HTML, [
            'raw'       => '<strong> ' . $SC::_('Note: ') . '</strong>'
                . $SC::_('Prefer using \'Pseudo BG Grow Expand Scale\' if background warping is not an issue.'),
            'condition' => [
                $animation_key => 'tsPseudoGrowExpandDimensions',
                '_use_pseudo_bg_before!' => ''
            ],
        ], $position_after_animation);


        $SC->control('_pseudo_animation_bg_required_notice', null, CM::RAW_HTML, [
            'raw'             => '<strong>' . $SC::_('Notice: ') . '</strong>'
                . $SC::_('This animation requires you to have a ')
                . "<a style='cursor:pointer; color: #b3044b;' onclick='$tab_click_path'> "
                . $SC::_('pseudo background')
                . '</a>',
            'content_classes' => 'elementor-panel-alert elementor-panel-alert-danger',
            'render_type'     => 'ui',
            'condition'       => [
                $animation_key => $this->get_pseudo_animations(),
                '_use_pseudo_bg_before' => ''
            ],
        ], $position_after_animation);
    }


    /**
     * Injects sticky controls
     * @param $element instance of the Control element,
     * @param $section_id id of the section found in $this->start_control_section('my_section_id', ...)
     */
    public function inject_pseudo_sections($element)
    {
        if ($element->get_controls('_use_pseudo_bg_before') !== NULL) return;
        $this->inject_pseudo_section($element, 'before');
        $this->inject_pseudo_section($element, 'after');
    }


    public function inject_pseudo_section($element, $before_after)
    {
        $SC           = new Shorthand_Controls($element);
        $selector     = "{{WRAPPER}}::$before_after";
        $id_suffix    = "_$before_after";
        $switch_label = $before_after === 'before' ? 'Pseudo Background Before' : 'Pseudo Background After';


        //PSEUDO CONTROLS
        $section_args = [
            'label'   => $SC::_($switch_label),
            'tab'     => CM::TAB_ADVANCED,
            'classes' => 'themeshark-section-icon'
        ];

        $switcher_args = [
            'render_type'  => 'ui',
            'default'      => '',
            'return_value' => 'on',
            'prefix_class' => "ts-pseudo-bg-$before_after-",
        ];

        $conditions = ['condition' => ["_use_pseudo_bg$id_suffix" => 'on']];

        if ($before_after === 'after') {
            $switcher_args['condition'] = ['_use_pseudo_bg_before' => 'on'];
            $section_args['condition']  = ['_use_pseudo_bg_before' => 'on'];
        }

        $element->start_controls_section("_section_pseudo_bg$id_suffix", $section_args);

        $SC->control("_use_pseudo_bg$id_suffix", $switch_label, CM::SWITCHER, $switcher_args);

        if ($before_after === 'before') {
            $SC->preset_control_overflow_notice("_pseudo_bg_overflow_notice$id_suffix", [
                'condition' => ['_use_pseudo_bg_before' => 'on']
            ]);
        }

        $SC->control("_psuedo_bg_display_popover$id_suffix", 'Responsive Display', CM::POPOVER_TOGGLE, array_merge($conditions, [
            'render_type'  => 'ui',
            'label_off'    => $SC::_('Default'),
            'label_on'     => $SC::_('Custom'),
            'return_value' => 'yes'
        ]));

        $element->start_popover();

        $responsive_settings = array_merge($conditions, [
            'return_value'         => 'yes',
            'selectors_dictionary' => [
                ''    => 'display:block',
                'yes' => 'display:none'
            ],
        ]);

        $SC->control("_pseudo_bg_hide_desktop$id_suffix", 'Hide On Desktop', CM::SWITCHER, array_merge($responsive_settings, [
            'selectors' => ["(desktop) $selector" => '{{VALUE}}']
        ]));
        $SC->control("_pseudo_bg_hide_tablet$id_suffix", 'Hide On Tablet', CM::SWITCHER, array_merge($responsive_settings, [
            'selectors' => ["(tablet) $selector" => '{{VALUE}}']
        ]));
        $SC->control("_pseudo_bg_hide_mobile$id_suffix", 'Hide On Mobile', CM::SWITCHER, array_merge($responsive_settings, [
            'selectors' => ["(mobile) $selector" => '{{VALUE}}']
        ]));

        $element->end_popover();

        $SC->control("_pseudo_bg_z_index$id_suffix", 'Z-Index', CM::NUMBER, array_merge($conditions, [
            'selectors' => [$selector => 'z-index: {{VALUE}};']
        ]));

        //--------- Align Controls ----------//
        $ALIGN_SETTINGS = array_merge($conditions, [
            'toggle'      => false,
            'render_type' => 'ui',
            'selectors'   => [$selector => '{{VALUE}}: 0px']
        ]);

        $SC->control("_pseudo_bg_align$id_suffix", 'Horizontal Align', CM::CHOOSE, array_merge([
            'default'   => 'left',
            'separator' => 'before',
            'options'   => $SC::options_choose(
                ['left',  'Left',  'eicon-h-align-left'],
                ['right', 'Right', 'eicon-h-align-right']
            ),
        ], $ALIGN_SETTINGS));

        $SC->control("_pseudo_bg_valign$id_suffix", 'Vertical Align', CM::CHOOSE, array_merge([
            'default' => 'top',
            'options' => $SC::options_choose(
                ['top',    'Top',    'eicon-v-align-top'],
                ['bottom', 'Bottom', 'eicon-v-align-bottom']
            ),
        ], $ALIGN_SETTINGS));


        //------------------------//
        //------NORMAL TAB--------//
        //------------------------//

        $element->start_controls_tabs("sticky_tabs$id_suffix", array_merge($conditions, [
            'separator' => 'before'
        ]));

        $element->start_controls_tab("_pseudo_bg_normal_tab$id_suffix", ['label' => $SC::_('Normal')]);

        $SC->group_control("_pseudo_bg$id_suffix", Group_Control_Background::get_type(), array_merge($conditions, [
            'selector'       => $selector,
            'fields_options' => [
                '__all'      => ['render_type' => 'ui'],
                'image'      => ['responsive' => false],
                'xpos'       => ['responsive' => false],
                'ypos'       => ['responsive' => false],
                'repeat'     => ['responsive' => false],
                'size'       => ['responsive' => false],
                'background' => ['default' => 'classic', 'frontend_available' => true],
                'position'   => ['responsive' => false, 'themeshark_settings' => [CH::NO_TRANSITION => true]],
                'bg_width'   => ['range' => $SC::range(['px', 0, 1000], ['%', 0, 150], ['vw', 0, 150]), 'device_args' => []],
                'color'      => [
                    Globals_fixer::FIX      => true,
                    'themeshark_settings'   => [CH::NO_TRANSITION => true],
                    'global' =>  ['default' => Global_Colors::COLOR_ACCENT],
                ],
            ],
        ]));

        // Transform
        $_pseudo_bg_transform = array_merge($conditions, [
            'selector' => $selector,
            'fields_options' => Group_Control_Transform::fields_vars_map([
                'translate_x' => '--translate-x_',
                'translate_y' => '--translate-y_',
                'scale_x'     => '--scale-x_',
                'scale_y'     => '--scale-y_',
                'skew_x'      => '--skew-x_',
                'skew_y'      => '--skew-y_',
                'rotate'      => '--rotate_',
                'origin_x'    => '--origin-x_',
                'origin_y'    => '--origin-y_'
            ], ['__all' => ['themeshark_settings' => [CH::NO_TRANSITION => true]]])
        ]);

        $SC->control("_pseudo_bg_transform_divider$id_suffix", null, CM::DIVIDER);
        $SC->group_control("_pseudo_bg_transform$id_suffix", Group_Control_Transform::get_type(), $_pseudo_bg_transform);

        //--------- Width Height ----------//
        $WIDTH_HEIGHT_SETTINGS = array_merge($conditions, [
            'render_type'         => 'ui',
            'themeshark_settings' => [CH::NO_TRANSITION => true],
        ]);

        $SC->responsive_control("_pseudo_bg_width$id_suffix", 'Width', CM::SLIDER, array_merge([
            'size_units' => ['px', '%', 'vw'],
            'range'      => $SC::range(['px', 0, 1000], ['%', 0, 150], ['vw', 0, 150]),
            'selectors'  => [$selector => 'width: {{SIZE}}{{UNIT}}'],
            'separator'  => 'before'
        ], $WIDTH_HEIGHT_SETTINGS));

        $SC->responsive_control("_pseudo_bg_min_width$id_suffix", 'Min Width', CM::SLIDER, array_merge([
            'size_units' => ['px', '%', 'vw'],
            'range'      => $SC::range(['px', 0, 1000], ['%', 0, 150], ['vw', 0, 150]),
            'selectors'  => [$selector => 'min-width: {{SIZE}}{{UNIT}}'],
        ], $WIDTH_HEIGHT_SETTINGS));

        $SC->responsive_control("_pseudo_bg_max_width$id_suffix", 'Max Width', CM::SLIDER, array_merge([
            'size_units' => ['px', '%', 'vw'],
            'range'      => $SC::range(['px', 0, 1000], ['%', 0, 150], ['vw', 0, 150]),
            'selectors'  => [$selector => 'max-width: {{SIZE}}{{UNIT}}'],
        ], $WIDTH_HEIGHT_SETTINGS));

        $SC->responsive_control("_pseudo_bg_height$id_suffix", 'Height', CM::SLIDER, array_merge([
            'size_units' => ['px', '%', 'vh'],
            'range'      => $SC::range(['px', 0, 700], ['%', 0, 150], ['vh', 0, 150]),
            'selectors'  => [$selector => 'height: {{SIZE}}{{UNIT}}'],
        ], $WIDTH_HEIGHT_SETTINGS));


        //--------- Opacity ----------//
        $SC->control("_pseudo_bg_opacity$id_suffix", 'Opacity', CM::SLIDER, array_merge($conditions, [
            'render_type'         => 'ui',
            'range'               => $SC::range(['px', 0, 1, 0.01]),
            'selectors'           => [$selector => 'opacity: {{SIZE || 1}}'],
            'themeshark_settings' => [CH::NO_TRANSITION => true],
            'separator'           => 'before'
        ]));

        //--------- Border Settings ----------//
        $SC->control("_pseudo_bg_border_heading$id_suffix", 'Border', CM::HEADING, array_merge($conditions, [
            'separator' => 'before'
        ]));

        // Group Border
        $_pseudo_bg_border = array_merge($conditions, [
            'selector'       => $selector,
            'fields_options' => ['__all' => [
                'themeshark_settings' => [CH::NO_TRANSITION => true]
            ]],
        ]);
        $SC->group_control("_pseudo_bg_border$id_suffix", Group_Control_Border::get_type(), $_pseudo_bg_border);

        $SC->responsive_control("_pseudo_bg_border_radius$id_suffix", 'Border Radius', CM::DIMENSIONS, array_merge($conditions, [
            'size_units' => ['px', '%'],
            'selectors'  => [
                $selector => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]));

        //Box Shadow
        $_pseudo_bg_shadow = array_merge($conditions, [
            'render_type' => 'ui',
            'selector'    => $selector,
        ]);
        $SC->group_control("_pseudo_bg_shadow$id_suffix", Group_Control_Box_Shadow::get_type(),  $_pseudo_bg_shadow);
        $element->end_controls_tab();

        //------------------------//
        //------STICKY TAB--------//
        //------------------------//
        $element->start_controls_tab("_pseudo_bg_sticky_tab$id_suffix", array_merge($conditions, [
            'label' => $SC::_('Sticky')
        ]));

        $SC->preset_control_sticky_notice("_pseudo_bg_require_sticky_notice$id_suffix", $conditions);


        $SC->sticky_group_control("_pseudo_bg_transition_sticky$id_suffix", Group_Control_Transition::get_type(), [
            'selector'         => $selector,
            'update_selectors' => false
        ]);

        $SC->sticky_control("_pseudo_bg_sticky_color$id_suffix", 'Background Color', CM::COLOR, [
            'selectors' => [
                $selector => 'background-color: {{VALUE}}'
            ]
        ]);

        $SC->sticky_group_control("_pseudo_bg_transform_sticky$id_suffix", Group_Control_Transform::get_type(), $_pseudo_bg_transform);

        //add prefix class to sticky popover toggle to add will-change: transform;
        $element->update_control("_pseudo_bg_transform_sticky$id_suffix" . '_transform_controls', [
            'prefix_class' => 'sticky-transform-'
        ]);


        //--------- Width Height ----------//
        $SC->sticky_duplicate_control("_pseudo_bg_width_sticky$id_suffix", "_pseudo_bg_width$id_suffix");
        $SC->sticky_duplicate_control("_pseudo_bg_min_width_sticky$id_suffix", "_pseudo_bg_min_width$id_suffix");
        $SC->sticky_duplicate_control("_pseudo_bg_max_width_sticky$id_suffix", "_pseudo_bg_max_width$id_suffix");
        $SC->sticky_duplicate_control("_pseudo_bg_height_sticky$id_suffix", "_pseudo_bg_height$id_suffix");

        //--------- Opacity ----------//
        $SC->sticky_duplicate_control("_pseudo_bg_opacity_sticky$id_suffix", "_pseudo_bg_opacity$id_suffix");

        //--------- Border Settings ----------//
        $SC->sticky_control("_pseudo_bg_border_heading_sticky$id_suffix", 'Border', CM::HEADING, ['separator' => 'before']);
        $SC->sticky_group_control("_pseudo_bg_border_sticky$id_suffix", Group_Control_Border::get_type(), $_pseudo_bg_border);
        $SC->sticky_duplicate_control("_pseudo_bg_border_radius_sticky$id_suffix", "_pseudo_bg_border_radius$id_suffix");
        $SC->sticky_group_control("_pseudo_bg_shadow_sticky$id_suffix", Group_Control_Box_Shadow::get_type(),  $_pseudo_bg_shadow);

        $element->end_controls_tab();
        $element->end_controls_tabs();
        $element->end_controls_section();
    }
}
