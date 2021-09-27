<?php

namespace Themeshark_Elementor\Widgets;

if (!defined('ABSPATH')) exit;

use \Elementor\Group_Control_Border;
use \Elementor\Controls_Manager as CM;
use \Elementor\Group_Control_Box_Shadow;
use \Elementor\Group_Control_Typography;
use \Elementor\Core\Responsive\Responsive;
use \Themeshark_Elementor\Inc\Globals_Fixer;
use \Themeshark_Elementor\Inc\Shorthand_Controls;
use \Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use \Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use \Themeshark_Elementor\Inc\TS_Widget;

class TS_Nav_Menu extends TS_Widget
{

    const NAME = 'ts-nav-menu';
    const TITLE = 'Sticky Styles Nav';

    public static function register_styles()
    {
        $deps = \Themeshark_Elementor\Plugin::has_elementor_pro() ? [] : ['ts-nav-menu-nopro'];
        self::widget_style('ts-nav-menu-nopro', self::get_dir_url(__DIR__, 'ts-nav-menu-nopro.css'));
        self::widget_style('ts-nav-menu', self::get_dir_url(__DIR__, 'ts-nav-menu.css'), $deps);
    }

    public static function register_scripts()
    {
        self::widget_script('ts-nav-menu', self::get_dir_url(__DIR__, 'ts-nav-menu.js'));
    }

    public function on_before_construct()
    {
        if (!\Themeshark_Elementor\Plugin::has_elementor_pro()) {
            wp_enqueue_style('ts-nav-menu-nopro'); // always preload this if no elementor pro
        }
    }

    protected $nav_menu_index = 1;

    public function get_icon()
    {
        return 'tsicon-sticky-styles-nav';
    }

    public function get_categories()
    {
        return ['themeshark'];
    }

    public function get_keywords()
    {
        return self::keywords(['menu', 'nav', 'sticky']);
    }

    public function get_script_depends()
    {
        return ['smartmenus', 'ts-nav-menu'];
    }

    public function get_style_depends()
    {
        return ['ts-nav-menu', 'ts-nav-menu-nopro'];
    }

    public function on_export($element)
    {
        unset($element['settings']['menu']);
        return $element;
    }

    protected function get_nav_menu_index()
    {
        return $this->nav_menu_index++;
    }

    private function get_available_menus()
    {
        $menus = wp_get_nav_menus();

        $options = [];

        foreach ($menus as $menu) {
            $options[$menu->slug] = $menu->name;
        }

        return $options;
    }

    protected function register_controls()
    {
        $SC = new Shorthand_Controls($this);

        $this->start_controls_section(
            'section_layout',
            [
                'label' => __('Layout', THEMESHARK_TXTDOMAIN),
            ]
        );

        $menus = $this->get_available_menus();

        if (!empty($menus)) {
            $this->add_control(
                'menu',
                [
                    'label' => __('Menu', THEMESHARK_TXTDOMAIN),
                    'type' => CM::SELECT,
                    'options' => $menus,
                    'default' => array_keys($menus)[0],
                    'save_default' => true,
                    'separator' => 'after',
                    'description' => sprintf(__('Go to the <a href="%s" target="_blank">Menus screen</a> to manage your menus.', THEMESHARK_TXTDOMAIN), admin_url('nav-menus.php')),
                ]
            );
        } else {
            $this->add_control(
                'menu',
                [
                    'type' => CM::RAW_HTML,
                    'raw' => '<strong>' . __('There are no menus in your site.', THEMESHARK_TXTDOMAIN) . '</strong><br>' . sprintf(__('Go to the <a href="%s" target="_blank">Menus screen</a> to create one.', THEMESHARK_TXTDOMAIN), admin_url('nav-menus.php?action=edit&menu=0')),
                    'separator' => 'after',
                    'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
                ]
            );
        }

        $this->add_control(
            'layout',
            [
                'label' => __('Layout', THEMESHARK_TXTDOMAIN),
                'type' => CM::SELECT,
                'default' => 'horizontal',
                'options' => [
                    'horizontal' => __('Horizontal', THEMESHARK_TXTDOMAIN),
                    'vertical' => __('Vertical', THEMESHARK_TXTDOMAIN),
                    'dropdown' => __('Dropdown', THEMESHARK_TXTDOMAIN),
                ],
                'frontend_available' => true,
            ]
        );

        $this->add_control(
            'align_items',
            [
                'label' => __('Align', THEMESHARK_TXTDOMAIN),
                'type' => CM::CHOOSE,
                'options' => $SC::choice_set_text_align(['left', 'center', 'right', 'justify']),
                'prefix_class' => 'elementor-nav-menu__align-',
                'condition' => [
                    'layout!' => 'dropdown',
                ],
            ]
        );

        $SC->control('pointer', 'Pointer', CM::SELECT, [
            'options' => $SC::options_select(
                ['none', 'None'],
                ['underline', 'Underline'],
                ['overline', 'Overline'],
                ['double-line', 'Double Line'],
                ['framed', 'Framed'],
                ['background', 'Background'],
                ['text', 'Text'],
                ['ts-corners', 'Corners']
            ),
            'default' => 'underline',
            'style_transfer' => true,
            'condition' => ['layout!' => 'dropdown'],
        ]);

        $SC->control('pointer_weight', 'Pointer Weight', CM::NUMBER, [
            'condition' => ['pointer' => 'ts-corners'],
            'min' => 1,
            'max' => 5,
            'default' => 2,
            'selectors' => $SC::selectors([
                '.themeshark-nav-menu' => [
                    '--pointer-weight: {{VALUE}}px'
                ]
            ])
        ]);

        $SC->control('animation_corners', 'Animation', CM::SELECT, [
            'options' => $SC::options_select(
                ['none', 'None'],
                ['expand', 'Expand'],
                ['fade', 'Fade']
            ),
            'default' => 'expand',
            'condition' => [
                'layout!' => 'dropdown',
                'pointer' => 'ts-corners',
            ],
        ]);

        $this->add_control(
            'animation_line',
            [
                'label' => __('Animation', THEMESHARK_TXTDOMAIN),
                'type' => CM::SELECT,
                'default' => 'fade',
                'options' => [
                    'fade' => 'Fade',
                    'slide' => 'Slide',
                    'grow' => 'Grow',
                    'drop-in' => 'Drop In',
                    'drop-out' => 'Drop Out',
                    'ts-slide-left' => 'Slide Left',
                    'none' => 'None',
                ],
                'condition' => [
                    'layout!' => 'dropdown',
                    'pointer' => ['underline', 'overline', 'double-line'],
                ],
            ]
        );

        $this->add_control(
            'animation_framed',
            [
                'label' => __('Animation', THEMESHARK_TXTDOMAIN),
                'type' => CM::SELECT,
                'default' => 'fade',
                'options' => [
                    'fade' => 'Fade',
                    'grow' => 'Grow',
                    'shrink' => 'Shrink',
                    'draw' => 'Draw',
                    'corners' => 'Corners',
                    'none' => 'None',
                ],
                'condition' => [
                    'layout!' => 'dropdown',
                    'pointer' => 'framed',
                ],
            ]
        );

        $this->add_control(
            'animation_background',
            [
                'label' => __('Animation', THEMESHARK_TXTDOMAIN),
                'type' => CM::SELECT,
                'default' => 'fade',
                'options' => [
                    'fade' => 'Fade',
                    'grow' => 'Grow',
                    'shrink' => 'Shrink',
                    'sweep-left' => 'Sweep Left',
                    'sweep-right' => 'Sweep Right',
                    'sweep-up' => 'Sweep Up',
                    'sweep-down' => 'Sweep Down',
                    'shutter-in-vertical' => 'Shutter In Vertical',
                    'shutter-out-vertical' => 'Shutter Out Vertical',
                    'shutter-in-horizontal' => 'Shutter In Horizontal',
                    'shutter-out-horizontal' => 'Shutter Out Horizontal',
                    'none' => 'None',
                ],
                'condition' => [
                    'layout!' => 'dropdown',
                    'pointer' => 'background',
                ],
            ]
        );

        $this->add_control(
            'animation_text',
            [
                'label' => __('Animation', THEMESHARK_TXTDOMAIN),
                'type' => CM::SELECT,
                'default' => 'grow',
                'options' => [
                    'grow' => 'Grow',
                    'shrink' => 'Shrink',
                    'sink' => 'Sink',
                    'float' => 'Float',
                    'skew' => 'Skew',
                    'rotate' => 'Rotate',
                    'none' => 'None',
                ],
                'condition' => [
                    'layout!' => 'dropdown',
                    'pointer' => 'text',
                ],
            ]
        );

        $icon_prefix = \Elementor\Icons_Manager::is_migration_allowed() ? 'fas ' : 'fa ';

        $SC->control('submenu_icon', 'Submenu Indicator', CM::ICONS, [
            'separator' => 'before',
            'default' => [
                'value' => $icon_prefix . 'fa-caret-down',
                'library' => 'fa-solid',
            ],
            'recommended' => [
                'fa-solid' => [
                    'chevron-down',
                    'angle-down',
                    'caret-down',
                    'plus',
                ],
            ],
            'label_block' => false,
            'skin' => 'inline',
            'exclude_inline_options' => ['svg'],
            'frontend_available' => true,
        ]);


        $this->add_control(
            'heading_mobile_dropdown',
            [
                'label' => __('Mobile Dropdown', THEMESHARK_TXTDOMAIN),
                'type' => CM::HEADING,
                'separator' => 'before',
                'condition' => [
                    'layout!' => 'dropdown',
                ],
            ]
        );

        $breakpoints = Responsive::get_breakpoints();

        $this->add_control(
            'dropdown',
            [
                'label' => __('Breakpoint', THEMESHARK_TXTDOMAIN),
                'type' => CM::SELECT,
                'default' => 'tablet',
                'options' => [
                    /* translators: %d: Breakpoint number. */
                    'mobile' => sprintf(__('Mobile (< %dpx)', THEMESHARK_TXTDOMAIN), $breakpoints['md']),
                    /* translators: %d: Breakpoint number. */
                    'tablet' => sprintf(__('Tablet (< %dpx)', THEMESHARK_TXTDOMAIN), $breakpoints['lg']),
                    'none' => __('None', THEMESHARK_TXTDOMAIN),
                ],
                'prefix_class' => 'elementor-nav-menu--dropdown-',
                'condition' => [
                    'layout!' => 'dropdown',
                ],
            ]
        );

        $this->add_control(
            'full_width',
            [
                'label' => __('Full Width', THEMESHARK_TXTDOMAIN),
                'type' => CM::SWITCHER,
                'description' => __('Stretch the dropdown of the menu to full width.', THEMESHARK_TXTDOMAIN),
                'prefix_class' => 'elementor-nav-menu--',
                'return_value' => 'stretch',
                'frontend_available' => true,
                'condition' => [
                    'dropdown!' => 'none',
                ],
            ]
        );

        $this->add_control(
            'text_align',
            [
                'label' => __('Align', THEMESHARK_TXTDOMAIN),
                'type' => CM::SELECT,
                'default' => 'aside',
                'options' => [
                    'aside' => __('Aside', THEMESHARK_TXTDOMAIN),
                    'center' => __('Center', THEMESHARK_TXTDOMAIN),
                ],
                'prefix_class' => 'elementor-nav-menu__text-align-',
                'condition' => [
                    'dropdown!' => 'none',
                ],
            ]
        );

        $this->add_control(
            'toggle',
            [
                'label' => __('Toggle Button', THEMESHARK_TXTDOMAIN),
                'type' => CM::SELECT,
                'default' => 'burger',
                'options' => [
                    '' => __('None', THEMESHARK_TXTDOMAIN),
                    'burger' => __('Hamburger', THEMESHARK_TXTDOMAIN),
                ],
                'prefix_class' => 'elementor-nav-menu--toggle elementor-nav-menu--',
                'render_type' => 'template',
                'frontend_available' => true,
                'condition' => [
                    'dropdown!' => 'none',
                ],
            ]
        );

        $this->add_control(
            'toggle_align',
            [
                'label' => __('Toggle Align', THEMESHARK_TXTDOMAIN),
                'type' => CM::CHOOSE,
                'default' => 'center',
                'options' => [
                    'left' => [
                        'title' => __('Left', THEMESHARK_TXTDOMAIN),
                        'icon' => 'eicon-h-align-left',
                    ],
                    'center' => [
                        'title' => __('Center', THEMESHARK_TXTDOMAIN),
                        'icon' => 'eicon-h-align-center',
                    ],
                    'right' => [
                        'title' => __('Right', THEMESHARK_TXTDOMAIN),
                        'icon' => 'eicon-h-align-right',
                    ],
                ],
                'selectors_dictionary' => [
                    'left' => 'margin-right: auto',
                    'center' => 'margin: 0 auto',
                    'right' => 'margin-left: auto',
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-menu-toggle' => '{{VALUE}}',
                ],
                'condition' => [
                    'toggle!' => '',
                    'dropdown!' => 'none',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_style_main-menu',
            [
                'label' => __('Main Menu', THEMESHARK_TXTDOMAIN),
                'tab' => CM::TAB_STYLE,
                'condition' => [
                    'layout!' => 'dropdown',
                ],

            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'menu_typography',
                'global' => [
                    'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
                ],
                'selector' => '{{WRAPPER}} .elementor-nav-menu .elementor-item',
            ]
        );



        //-------------------------//
        //------ NORMAL MENU ------//
        //-------------------------//

        $this->start_controls_tabs('tabs_menu_item_style');
        $this->start_controls_tab('tab_menu_item_normal', ['label' => $SC::_('Normal')]);

        // SELECTORS
        //-----------------------------------------------
        $menu_item             = '.elementor-nav-menu--main .elementor-item';
        $pointer_prefix        = '.elementor-nav-menu--main:not(.e--pointer-framed):not(.e--pointer-ts-corners) .elementor-item';
        $pointer_framed_prefix = '.e--pointer-framed .elementor-item';

        $SC->control('color_menu_item', 'Text', CM::COLOR, [
            'global' => ['default' => Global_Colors::COLOR_TEXT],
            'default' => '',
            'selectors' => $SC::selectors([
                "$menu_item" => ['color: {{VALUE}}']
            ]),
            Globals_Fixer::FIX => true
        ]);

        $SC->control('color_menu_item_hover', 'Text Hover', CM::COLOR, [
            'condition' => ['pointer!' => 'background'],
            'global' => ['default' => Global_Colors::COLOR_ACCENT],
            'selectors' => $SC::selectors([
                "$menu_item:hover,
                 $menu_item.elementor-item-active,
                 $menu_item.highlighted,
                 $menu_item:focus" => ['color: {{VALUE}}']
            ]),
            Globals_Fixer::FIX => true
        ]);


        $SC->control('color_menu_item_active', 'Text Active', CM::COLOR, [
            'selectors' => $SC::selectors([
                "$menu_item.elementor-item-active" => ['color:{{VALUE}}']
            ])
        ]);

        $SC->control('pointer_color_menu_item_hover', 'Pointer Hover', CM::COLOR, [
            'global' => ['default' => Global_Colors::COLOR_ACCENT],
            'default' => '',
            'separator' => 'before',
            'selectors' => $SC::selectors([
                "$pointer_prefix::before, 
                 $pointer_prefix::after" => ['background-color: {{VALUE}}'],
                "$pointer_framed_prefix::before, 
                 $pointer_framed_prefix::after" => ['border-color: {{VALUE}}'],
                '.themeshark-nav-menu' => ['--pointer-color: {{VALUE}}'],
            ]),
            Globals_Fixer::FIX => true
        ]);


        $SC->control('pointer_color_menu_item_active', 'Pointer Active', CM::COLOR, [
            'condition' => ['pointer!' => ['none', 'text']],
            'default' => '',
            'selectors' => $SC::selectors([
                "$pointer_prefix.elementor-item-active::before, 
                 $pointer_prefix.elementor-item-active::after" => ['background-color: {{VALUE}}'],
                "$pointer_framed_prefix.elementor-item-active::before, 
                 $pointer_framed_prefix.elementor-item-active::after" => ['border-color: {{VALUE}}'],
                '.themeshark-nav-menu .elementor-item.elementor-item-active' => ['--pointer-color: {{VALUE}}'],
            ]),
            Globals_Fixer::FIX => true
        ]);

        $SC->control('color_menu_item_hover_pointer_bg', 'Text Hover', CM::COLOR, [
            'condition' => ['pointer' => 'background'],
            'default' => '#fff',
            'selectors' => $SC::selectors([
                "$menu_item:hover,
                 $menu_item.elementor-item-active,
                 $menu_item.highlighted,
                 $menu_item:focus" => ['color: {{VALUE}}'],
            ]),
        ]);


        $this->end_controls_tab();
        $this->start_controls_tab('tab_menu_item_sticky', ['label' => $SC::_('Sticky')]);

        //-------------------------//
        //------ STICKY MENU ------//
        //-------------------------//

        $sticky_settings = ['global' => ['default' => '']];
        $SC->sticky_duplicate_control('color_menu_item_sticky', 'color_menu_item', $sticky_settings);
        $SC->sticky_duplicate_control('color_menu_item_hover_sticky', 'color_menu_item_hover', $sticky_settings);
        $SC->sticky_duplicate_control('color_menu_item_active_sticky', 'color_menu_item_active', $sticky_settings);
        $SC->sticky_duplicate_control('pointer_color_menu_item_hover_sticky', 'pointer_color_menu_item_hover', $sticky_settings);
        $SC->sticky_duplicate_control('pointer_color_menu_item_active_sticky', 'pointer_color_menu_item_active', $sticky_settings);
        $SC->sticky_duplicate_control('color_menu_item_hover_pointer_bg_sticky', 'color_menu_item_hover_pointer_bg', $sticky_settings);

        $this->end_controls_tab();
        $this->end_controls_tabs();

        $SC->control('hr', null, CM::DIVIDER);


        //-------------------------//
        //---- NORMAL SPACING -----//
        //-------------------------//

        $this->start_controls_tabs('spacing', ['label' => $SC::_('Spacing')]);
        $this->start_controls_tab('spacing_normal', ['label' => $SC::_('Normal')]);

        $SC->responsive_control('pointer_width', 'Pointer Width', CM::SLIDER, [
            'condition' => ['pointer' => ['underline', 'overline', 'double-line', 'framed']],
            'range' => $SC::range(['px', null, 30]),
            'selectors' => $SC::selectors([
                '_vars' => ['P' => '.e--pointer', 'A' => '.e--animation', 'ITEM' => '.elementor-item'],
                '%P%-framed %ITEM%::before' => ['border-width: {{SIZE}}{{UNIT}}'],
                '%P%-framed%A%-draw %ITEM%::before' => ['border-width: 0 0 {{SIZE}}{{UNIT}} {{SIZE}}{{UNIT}}'],
                '%P%-framed%A%-draw %ITEM%::after' => ['border-width: {{SIZE}}{{UNIT}} {{SIZE}}{{UNIT}} 0 0'],
                '%P%-framed%A%-corners %ITEM%::before' => ['border-width: {{SIZE}}{{UNIT}} 0 0 {{SIZE}}{{UNIT}}'],
                '%P%-framed%A%-corners %ITEM%::after' => ['border-width: 0 {{SIZE}}{{UNIT}} {{SIZE}}{{UNIT}} 0'],
                '%P%-underline %ITEM%::after,
                 %P%-overline %ITEM%::before,
                 %P%-double-line %ITEM%::before,
                 %P%-double-line %ITEM%::after' => ['height: {{SIZE}}{{UNIT}}']
            ]),
        ]);

        $SC->responsive_control('padding_horizontal_menu_item', 'Horizontal Padding', CM::SLIDER, [
            'range' => $SC::range(['px', null, 50]),
            'selectors' => $SC::selectors([
                $menu_item => [
                    'padding-left: {{SIZE}}{{UNIT}}',
                    'padding-right: {{SIZE}}{{UNIT}}'
                ]
            ])
        ]);

        $SC->responsive_control('padding_vertical_menu_item', 'Vertical Padding', CM::SLIDER, [
            'range' => $SC::range(['px', null, 50]),
            'selectors' => $SC::selectors([
                $menu_item => [
                    'padding-top: {{SIZE}}{{UNIT}}',
                    'padding-bottom: {{SIZE}}{{UNIT}}'
                ]
            ])
        ]);

        $SC->responsive_control('menu_space_between', 'Space Between', CM::SLIDER, [
            'range' => $SC::range(['px', null, 100]),
            'selectors' => $SC::selectors([
                '_vars' => [
                    'ITEM' => '.elementor-nav-menu > li:not(:last-child)',
                    'HOR_LAYOUT' => '.elementor-nav-menu--layout-horizontal'
                ],
                'body:not(.rtl) {{WRAPPER}} %HOR_LAYOUT% %ITEM%' => ['margin-right: {{SIZE}}{{UNIT}}'],
                'body.rtl {{WRAPPER}} %HOR_LAYOUT% %ITEM%' => ['margin-left: {{SIZE}}{{UNIT}}'],
                '{{WRAPPER}} .elementor-nav-menu--main:not(%HOR_LAYOUT%) %ITEM%' => ['margin-bottom: {{SIZE}}{{UNIT}}'],
            ], null, false)
        ]);

        $SC->responsive_control('border_radius_menu_item', 'Border Radius', CM::SLIDER, [
            'condition' => ['pointer' => 'background'],
            'size_units' => ['px', 'em', '%'],
            'selectors' => $SC::selectors([
                '_vars' => [
                    'SHUTTER_HOR_ITEM' => '.e--animation-shutter-in-horizontal .elementor-item',
                    'SHUTTER_VERT_ITEM' => '.e--animation-shutter-in-vertical .elementor-item'
                ],
                '.elementor-item::before' => ['border-radius: {{SIZE}}{{UNIT}}'],
                '%SHUTTER_HOR_ITEM%::before' => ['border-radius: {{SIZE}}{{UNIT}} {{SIZE}}{{UNIT}} 0 0'],
                '%SHUTTER_HOR_ITEM%::after' => ['border-radius: 0 0 {{SIZE}}{{UNIT}} {{SIZE}}{{UNIT}}'],
                '%SHUTTER_VERT_ITEM%::before' => ['border-radius: 0 {{SIZE}}{{UNIT}} {{SIZE}}{{UNIT}} 0'],
                '%SHUTTER_VERT_ITEM%::after' => ['border-radius: {{SIZE}}{{UNIT}} 0 0 {{SIZE}}{{UNIT}}'],
            ]),
        ]);

        $this->end_controls_tab();

        //-------------------------//
        //---- STICKY SPACING -----//
        //-------------------------//

        $this->start_controls_tab('spacing_sticky', ['label' => $SC::_('Sticky')]);

        $SC->sticky_duplicate_control('pointer_width_sticky', 'pointer_width');
        $SC->sticky_duplicate_control('padding_horizontal_menu_item_sticky', 'padding_horizontal_menu_item');
        $SC->sticky_duplicate_control('padding_vertical_menu_item_sticky', 'padding_vertical_menu_item');
        $SC->sticky_duplicate_control('menu_space_between_sticky', 'menu_space_between');
        $SC->sticky_duplicate_control('border_radius_menu_item_sticky', 'border_radius_menu_item');

        $this->end_controls_tab();
        $this->end_controls_tabs();

        $this->end_controls_section();

        $this->start_controls_section(
            'section_style_dropdown',
            [
                'label' => __('Dropdown', THEMESHARK_TXTDOMAIN),
                'tab' => CM::TAB_STYLE,
            ]
        );

        $this->add_control(
            'dropdown_description',
            [
                'raw' => __('On desktop, this will affect the submenu. On mobile, this will affect the entire menu.', THEMESHARK_TXTDOMAIN),
                'type' => CM::RAW_HTML,
                'content_classes' => 'elementor-descriptor',
            ]
        );

        $SC->responsive_control('dropdown_text_align', 'Text Align', CM::CHOOSE, [
            'options' => $SC::choice_set_text_align(['left', 'center', 'right']),
            'selectors_dictionary' => [
                'left'   => 'flex-direction: row; justify-content: flex-start;',
                'center' => 'flex-direction: row; justify-content: center;',
                'right'  => 'flex-direction: row-reverse; justify-content: flex-end;',
            ],
            'selectors' => $SC::selectors([
                '.elementor-nav-menu--dropdown .elementor-item,
                 .elementor-nav-menu--dropdown .elementor-sub-item' => [
                    '{{VALUE}}'
                ]
            ]),
        ]);

        $this->start_controls_tabs('tabs_dropdown_item_style');

        $this->start_controls_tab(
            'tab_dropdown_item_normal',
            [
                'label' => __('Normal', THEMESHARK_TXTDOMAIN),
            ]
        );

        $this->add_control(
            'color_dropdown_item',
            [
                'label' => __('Text Color', THEMESHARK_TXTDOMAIN),
                'type' => CM::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .elementor-nav-menu--dropdown a, {{WRAPPER}} .elementor-menu-toggle' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'background_color_dropdown_item',
            [
                'label' => __('Background Color', THEMESHARK_TXTDOMAIN),
                'type' => CM::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .elementor-nav-menu--dropdown' => 'background-color: {{VALUE}}',
                ],
                'separator' => 'none',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_dropdown_item_hover',
            [
                'label' => __('Hover', THEMESHARK_TXTDOMAIN),
            ]
        );

        $this->add_control(
            'color_dropdown_item_hover',
            [
                'label' => __('Text Color', THEMESHARK_TXTDOMAIN),
                'type' => CM::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .elementor-nav-menu--dropdown a:hover,
					{{WRAPPER}} .elementor-nav-menu--dropdown a.elementor-item-active,
					{{WRAPPER}} .elementor-nav-menu--dropdown a.highlighted,
					{{WRAPPER}} .elementor-menu-toggle:hover' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'background_color_dropdown_item_hover',
            [
                'label' => __('Background Color', THEMESHARK_TXTDOMAIN),
                'type' => CM::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .elementor-nav-menu--dropdown a:hover,
					{{WRAPPER}} .elementor-nav-menu--dropdown a.elementor-item-active,
					{{WRAPPER}} .elementor-nav-menu--dropdown a.highlighted' => 'background-color: {{VALUE}}',
                ],
                'separator' => 'none',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_dropdown_item_active',
            [
                'label' => __('Active', THEMESHARK_TXTDOMAIN),
            ]
        );

        $this->add_control(
            'color_dropdown_item_active',
            [
                'label' => __('Text Color', THEMESHARK_TXTDOMAIN),
                'type' => CM::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .elementor-nav-menu--dropdown a.elementor-item-active' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'background_color_dropdown_item_active',
            [
                'label' => __('Background Color', THEMESHARK_TXTDOMAIN),
                'type' => CM::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .elementor-nav-menu--dropdown a.elementor-item-active' => 'background-color: {{VALUE}}',
                ],
                'separator' => 'none',
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'dropdown_typography',
                'global' => [
                    'default' => Global_Typography::TYPOGRAPHY_ACCENT,
                ],
                'exclude' => ['line_height'],
                'selector' => '{{WRAPPER}} .elementor-nav-menu--dropdown .elementor-item, {{WRAPPER}} .elementor-nav-menu--dropdown  .elementor-sub-item',
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'dropdown_border',
                'selector' => '{{WRAPPER}} .elementor-nav-menu--dropdown',
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'dropdown_border_radius',
            [
                'label' => __('Border Radius', THEMESHARK_TXTDOMAIN),
                'type' => CM::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .elementor-nav-menu--dropdown' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .elementor-nav-menu--dropdown li:first-child a' => 'border-top-left-radius: {{TOP}}{{UNIT}}; border-top-right-radius: {{RIGHT}}{{UNIT}};',
                    '{{WRAPPER}} .elementor-nav-menu--dropdown li:last-child a' => 'border-bottom-right-radius: {{BOTTOM}}{{UNIT}}; border-bottom-left-radius: {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'dropdown_box_shadow',
                'exclude' => [
                    'box_shadow_position',
                ],
                'selector' => '{{WRAPPER}} .elementor-nav-menu--main .elementor-nav-menu--dropdown, {{WRAPPER}} .elementor-nav-menu__container.elementor-nav-menu--dropdown',
            ]
        );

        $this->add_responsive_control(
            'padding_horizontal_dropdown_item',
            [
                'label' => __('Horizontal Padding', THEMESHARK_TXTDOMAIN),
                'type' => CM::SLIDER,
                'selectors' => $SC::selectors([
                    '.elementor-nav-menu--dropdown a' => [
                        'padding-left: {{SIZE}}{{UNIT}}; padding-right: {{SIZE}}{{UNIT}}'
                    ],
                ]),
                'separator' => 'before',

            ]
        );

        $this->add_responsive_control(
            'padding_vertical_dropdown_item',
            [
                'label' => __('Vertical Padding', THEMESHARK_TXTDOMAIN),
                'type' => CM::SLIDER,
                'range' => [
                    'px' => [
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-nav-menu--dropdown a' => 'padding-top: {{SIZE}}{{UNIT}}; padding-bottom: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->add_control(
            'heading_dropdown_divider',
            [
                'label' => __('Divider', THEMESHARK_TXTDOMAIN),
                'type' => CM::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'dropdown_divider',
                'selector' => '{{WRAPPER}} .elementor-nav-menu--dropdown li:not(:last-child)',
                'exclude' => ['width'],
            ]
        );

        $this->add_control(
            'dropdown_divider_width',
            [
                'label' => __('Border Width', THEMESHARK_TXTDOMAIN),
                'type' => CM::SLIDER,
                'range' => [
                    'px' => [
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-nav-menu--dropdown li:not(:last-child)' => 'border-bottom-width: {{SIZE}}{{UNIT}}',
                ],
                'condition' => [
                    'dropdown_divider_border!' => '',
                ],
            ]
        );

        $this->add_responsive_control(
            'dropdown_top_distance',
            [
                'label' => __('Distance', THEMESHARK_TXTDOMAIN),
                'type' => CM::SLIDER,
                'range' => [
                    'px' => [
                        'min' => -100,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-nav-menu--main > .elementor-nav-menu > li > .elementor-nav-menu--dropdown, {{WRAPPER}} .elementor-nav-menu__container.elementor-nav-menu--dropdown' => 'margin-top: {{SIZE}}{{UNIT}} !important',
                ],
                'separator' => 'before',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section('style_toggle', [
            'label' => $SC::_('Toggle Button'),
            'tab' => CM::TAB_STYLE,
            'condition' => [
                'toggle!' => '',
                'dropdown!' => 'none',
            ],
        ]);

        $this->start_controls_tabs('tabs_toggle_style');

        $this->start_controls_tab(
            'tab_toggle_style_normal',
            [
                'label' => __('Normal', THEMESHARK_TXTDOMAIN),
            ]
        );

        // $SC->control('toggle_color', 'Color', CM::COLOR, [

        // ])
        $this->add_control(
            'toggle_color',
            [
                'label' => __('Color', THEMESHARK_TXTDOMAIN),
                'type' => CM::COLOR,
                'selectors' => [
                    '{{WRAPPER}} div.elementor-menu-toggle' => 'color: {{VALUE}}', // Harder selector to override text color control
                ],
            ]
        );

        $this->add_control(
            'toggle_background_color',
            [
                'label' => __('Background Color', THEMESHARK_TXTDOMAIN),
                'type' => CM::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-menu-toggle' => 'background-color: {{VALUE}}',
                ],
            ]
        );
        $this->add_control(
            'toggle_color_hover',
            [
                'label' => __('Color Hover', THEMESHARK_TXTDOMAIN),
                'type' => CM::COLOR,
                'selectors' => [
                    '{{WRAPPER}} div.elementor-menu-toggle:hover' => 'color: {{VALUE}}', // Harder selector to override text color control
                ],
            ]
        );

        $this->add_control(
            'toggle_background_color_hover',
            [
                'label' => __('Background Color Hover', THEMESHARK_TXTDOMAIN),
                'type' => CM::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-menu-toggle:hover' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_toggle_style_hover',
            [
                'label' => __('Sticky', THEMESHARK_TXTDOMAIN),
            ]
        );

        $SC->sticky_duplicate_control('toggle_color_sticky', 'toggle_color');
        $SC->sticky_duplicate_control('toggle_background_color_sticky', 'toggle_background_color');


        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_responsive_control(
            'toggle_size',
            [
                'label' => __('Size', THEMESHARK_TXTDOMAIN),
                'type' => CM::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 15,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-menu-toggle' => 'font-size: {{SIZE}}{{UNIT}}',
                ],
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'toggle_border_width',
            [
                'label' => __('Border Width', THEMESHARK_TXTDOMAIN),
                'type' => CM::SLIDER,
                'range' => [
                    'px' => [
                        'max' => 10,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-menu-toggle' => 'border-width: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->add_responsive_control(
            'toggle_border_radius',
            [
                'label' => __('Border Radius', THEMESHARK_TXTDOMAIN),
                'type' => CM::SLIDER,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .elementor-menu-toggle' => 'border-radius: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $available_menus = $this->get_available_menus();



        if (!$available_menus) {
            return;
        }

        $settings = $this->get_active_settings();

        $args = [
            'echo' => false,
            'menu' => $settings['menu'],
            'menu_class' => 'elementor-nav-menu',
            'menu_id' => 'menu-' . $this->get_nav_menu_index() . '-' . $this->get_id(),
            'fallback_cb' => '__return_empty_string',
            'container' => '',
        ];

        if ('vertical' === $settings['layout']) {
            $args['menu_class'] .= ' sm-vertical';
        }

        // Add custom filter to handle Nav Menu HTML output.
        add_filter('nav_menu_link_attributes', [$this, 'handle_link_classes'], 10, 4);
        add_filter('nav_menu_submenu_css_class', [$this, 'handle_sub_menu_classes']);
        add_filter('nav_menu_item_id', '__return_empty_string');

        // General Menu.
        $menu_html = wp_nav_menu($args);

        // Dropdown Menu.
        $args['menu_id'] = 'menu-' . $this->get_nav_menu_index() . '-' . $this->get_id();
        $dropdown_menu_html = wp_nav_menu($args);

        // Remove all our custom filters.
        remove_filter('nav_menu_link_attributes', [$this, 'handle_link_classes']);
        remove_filter('nav_menu_submenu_css_class', [$this, 'handle_sub_menu_classes']);
        remove_filter('nav_menu_item_id', '__return_empty_string');

        if (empty($menu_html)) {
            return;
        }

        $this->add_render_attribute('menu-toggle', [
            'class' => ['elementor-menu-toggle', 'themeshark-menu-toggle'],
            'role' => 'button',
            'tabindex' => '0',
            'aria-label' => __('Menu Toggle', THEMESHARK_TXTDOMAIN),
            'aria-expanded' => 'false',
        ]);


        $is_edit_mode = isset($_GET['action']) && $_GET['action'] === 'elementor';
        if ($is_edit_mode) {
            $this->add_render_attribute('menu-toggle', [
                'class' => 'elementor-clickable',
            ]);
        }

        $this->add_render_attribute('main-menu', 'role', 'navigation');

        if ('dropdown' !== $settings['layout']) :
            $this->add_render_attribute('main-menu', 'class', [
                'themeshark-nav-menu',
                'elementor-nav-menu--main',
                'elementor-nav-menu__container',
                'elementor-nav-menu--layout-' . $settings['layout'],
            ]);

            if ($settings['pointer']) :
                $this->add_render_attribute('main-menu', 'class', 'e--pointer-' . $settings['pointer']);

                foreach ($settings as $key => $value) :
                    if (0 === strpos($key, 'animation') && $value) :
                        $this->add_render_attribute('main-menu', 'class', 'e--animation-' . $value);

                        break;
                    endif;
                endforeach;
            endif; ?>
            <nav <?php $this->print_render_attribute_string('main-menu'); ?>>
                <?php echo $menu_html; ?>
            </nav>
        <?php
        endif;
        ?>
        <div <?php $this->print_render_attribute_string('menu-toggle'); ?>>
            <i class="eicon-menu-bar" aria-hidden="true"></i>
            <span class="elementor-screen-only"><?php _e('Menu', THEMESHARK_TXTDOMAIN); ?></span>
        </div>
        <nav class="elementor-nav-menu--dropdown elementor-nav-menu__container themeshark-nav-menu--dropdown" role="navigation" aria-hidden="true"><?php echo $dropdown_menu_html; ?></nav>
<?php
    }

    public function handle_link_classes($atts, $item, $args, $depth)
    {
        $classes = $depth ? 'elementor-sub-item' : 'elementor-item';
        $is_anchor = false !== strpos($atts['href'], '#');

        if (!$is_anchor && in_array('current-menu-item', $item->classes)) {
            $classes .= ' elementor-item-active';
        }

        if ($is_anchor) {
            $classes .= ' elementor-item-anchor';
        }

        if (empty($atts['class'])) {
            $atts['class'] = $classes;
        } else {
            $atts['class'] .= ' ' . $classes;
        }

        return $atts;
    }

    public function handle_sub_menu_classes($classes)
    {
        $classes[] = 'elementor-nav-menu--dropdown';

        return $classes;
    }

    public function render_plain_content()
    {
    }
}
