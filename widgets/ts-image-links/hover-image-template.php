<?php

namespace Themeshark_Elementor\Widgets;

if (!defined('ABSPATH')) exit;

use \Elementor\Group_Control_Border;
use \Elementor\Controls_Manager as CM;
use \Themeshark_Elementor\Inc\TS_Error;
use \Elementor\Group_Control_Box_Shadow;
use \Elementor\Group_Control_Image_Size;
use \Elementor\Group_Control_Typography;
use \Elementor\Group_Control_Background;
use \Themeshark_Elementor\Inc\Globals_Fixer;
use \Themeshark_Elementor\Inc\Shorthand_Controls;
use \Themeshark_Elementor\Controls\Controls_Handler;
use \Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use \Themeshark_Elementor\Controls\Controls_Handler as CH;
use \Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use \Elementor\Utils;

trait Hover_Image_Template
{
    public $attribute_item_wrap    = 'hover_image';
    public $attribute_item_content = 'hover_image_content';
    public $group_image_size_name  = 'thumbnail';
    public $control_key_skin       = 'effect';

    private $SC = null;

    /**
     * @return \Themeshark_Elementor\Inc\Shorthand_Controls
     */
    private function get_SC()
    {
        if (is_null($this->SC)) {
            $this->SC = new Shorthand_Controls($this);
        }
        return $this->SC;
    }

    public function is_post()
    {
        return $this->get_name() === 'ts-image-link-posts' ? true : false;
    }

    public static function register_template_styles()
    {
        self::widget_style('ts-hover-image', self::get_dir_url(__DIR__, 'ts-hover-image.css'));
    }

    //---------------------------------//
    //--------- SECTION QUERY ---------//
    //---------------------------------//

    public function add_control_title_size($args = [])
    {
        $SC = $this->get_SC();
        $SC->control('title_size', 'Title Tag', CM::SELECT, array_merge([
            'default' => 'h3',
            'options' => [
                'h1' => 'H1',
                'h2' => 'H2',
                'h3' => 'H3',
                'h4' => 'H4',
                'h5' => 'H5',
                'h6' => 'H6',
                'div' => 'div',
                'span' => 'span',
                'p' => 'p',
            ],
            'render_type' => 'ui',
            'themeshark_settings' => [
                Controls_Handler::LINK_REPLACE_TAG => [
                    'selector' => '{{WRAPPER}} .themeshark-hover-image-title'
                ]
            ]
        ], $args));
    }
    public function add_control_image_size($args = [])
    {
        $SC = $this->get_SC();
        $SC->group_control($this->group_image_size_name, Group_Control_Image_size::get_type(), array_merge([
            'exclude' => ['custom'],
            'default' => 'medium',
        ], $args));
    }

    public function add_control_height($args = [])
    {
        $SC = $this->get_SC();
        $SC->responsive_control('image_size_height', 'Height', CM::SLIDER, array_merge([
            'default' => $SC::range_default('px', 350),
            'size_units' => ['px', 'vw', 'vh'],
            'range' => $SC::range(['px', 100, 1000], ['vw', 5, 100], ['vh', 5, 100]),
            'separator' => 'before',
            'selectors' => $SC::selectors([
                '{{WRAPPER}}:not(.themeshark-hover-image--skin-card) .themeshark-hover-image,
                 {{WRAPPER}}.themeshark-hover-image--skin-card .themeshark-hover-image-wrap,
                 {{WRAPPER}}.themeshark-hover-image--skin-card .themeshark-hover-image-wrap img' => [
                    'height: {{SIZE}}{{UNIT}}'
                ],
            ], null, false)
        ], $args));
    }

    public function add_control_max_height($args = [])
    {
        $SC = $this->get_SC();
        $SC->responsive_control('image_size_max_height', 'Max Height', CM::SLIDER, array_merge([
            'size_units' => ['px', 'vw', 'vh'],
            'range' => $SC::range(['px', 100, 1000], ['vw', 5, 100], ['vh', 5, 100]),
            'selectors' => $SC::selectors([
                '{{WRAPPER}}:not(.themeshark-hover-image--skin-card) .themeshark-hover-image,
                 {{WRAPPER}}.themeshark-hover-image--skin-card .themeshark-hover-image-wrap,
                 {{WRAPPER}}.themeshark-hover-image--skin-card .themeshark-hover-image-wrap img' => [
                    'max-height: {{SIZE}}{{UNIT}}'
                ]
            ], null, false)
        ], $args));
    }


    public function add_control_effect()
    {
        $SC = $this->get_SC();
        $SC->control($this->control_key_skin, 'Skin', CM::SELECT, [
            'default' => 'corners',
            'prefix_class' => 'themeshark-hover-image--skin-',
            'render_type' => 'template',
            'options' => $SC::options_select(
                ['corners', 'Corners'],
                ['standard', 'Raise Content'],
                ['border-offset', 'Border Offset'],
                ['card', 'Card']
            )
        ]);
    }



    public function section_image_style()
    {
        $SC = new Shorthand_Controls($this);

        $this->start_controls_section('section_image_style', [
            'label' => $SC::_('Image'),
            'tab'   => CM::TAB_STYLE
        ]);

        $SC->responsive_control('image_size_width', 'Width', CM::SLIDER, [
            'default'    => $SC::range_default('px', 350),
            'size_units' => ['px', '%', 'vw', 'vh'],
            'range'      => $SC::range(['px', 100, 1000], ['%', 5, 100], ['vw', 5, 100], ['vh', 5, 100]),
            'selectors'  => $SC::selectors([
                '.themeshark-hover-image' => [
                    'width: {{SIZE}}{{UNIT}}'
                ]
            ])
        ]);


        $SC->responsive_control('image_size_max_width', 'Max Width', CM::SLIDER, [
            'size_units' => ['px', '%', 'vw', 'vh'],
            'range'      => $SC::range(['px', 100, 1000], ['%', 5, 100], ['vw', 5, 100], ['vh', 5, 100]),
            'selectors'  => $SC::selectors([
                '.themeshark-hover-image' => [
                    'max-width: {{SIZE}}{{UNIT}}'
                ]
            ])
        ]);


        $skin_card =
            $SC->responsive_control('image_size_height', 'Height', CM::SLIDER, [
                'default'    => $SC::range_default('px', 350),
                'size_units' => ['px', 'vw', 'vh'],
                'range'      => $SC::range(['px', 100, 1000], ['vw', 5, 100], ['vh', 5, 100]),
                'separator'  => 'before',
                'selectors'  => $SC::selectors([
                    '{{WRAPPER}}:not(.themeshark-hover-image--skin-card):not(.themeshark-hover-image--skin-classic) .themeshark-hover-image,
                    {{WRAPPER}}.themeshark-hover-image--skin-card .themeshark-hover-image-wrap,
                    {{WRAPPER}}.themeshark-hover-image--skin-classic .themeshark-hover-image-wrap,
                    {{WRAPPER}}.themeshark-hover-image--skin-card .themeshark-hover-image-wrap img,
                    {{WRAPPER}}.themeshark-hover-image--skin-classic .themeshark-hover-image-wrap img' => [
                        'height: {{SIZE}}{{UNIT}}'
                    ],
                ], null, false)
            ]);

        $SC->responsive_control('image_size_max_height', 'Max Height', CM::SLIDER, [
            'size_units' => ['px', 'vw', 'vh'],
            'range'      => $SC::range(['px', 100, 1000], ['vw', 5, 100], ['vh', 5, 100]),
            'selectors'  => $SC::selectors([
                '{{WRAPPER}}:not(.themeshark-hover-image--skin-card):not(.themeshark-hover-image--skin-classic) .themeshark-hover-image,
                {{WRAPPER}}.themeshark-hover-image--skin-card .themeshark-hover-image-wrap,
                {{WRAPPER}}.themeshark-hover-image--skin-classic .themeshark-hover-image-wrap,
                {{WRAPPER}}.themeshark-hover-image--skin-card .themeshark-hover-image-wrap img,
                {{WRAPPER}}.themeshark-hover-image--skin-classic .themeshark-hover-image-wrap img' => [
                    'max-height: {{SIZE}}{{UNIT}}'
                ]
            ], null, false)
        ]);


        $this->start_controls_tabs('overlay_tabs', [
            'separator' => 'before'
        ]);


        //TAB NORMAL
        $this->start_controls_tab('overlay_tab_normal', [
            'label' => $SC::_('Normal'),
        ]);

        $SC->control('content_background_color', 'Overlay', CM::COLOR, [
            'default'     => '#000',
            'render_type' => 'ui',
            'selectors'   => $SC::selectors([
                '.themeshark-hover-image' => [
                    '--overlay-color: {{VALUE}}'
                ]
            ])
        ]);

        $SC->control('content_background_opacity', 'Opacity', CM::SLIDER, [
            'range'     => $SC::range(['px', 0, 1, .01]),
            'default'   => $SC::range_default('px', .5),
            'selectors' => $SC::selectors([
                '.themeshark-hover-image' => [
                    '--overlay-opacity: {{SIZE}}'
                ]
            ])
        ]);

        $this->end_controls_tab();

        //TAB HOVER
        $this->start_controls_tab('overlay_tab_hover', [
            'label' => $SC::_('Hover'),
        ]);

        $SC->control('content_background_color_hover', 'Overlay Hover', CM::COLOR, [
            'render_type' => 'ui',
            'selectors'   => $SC::selectors([
                '.themeshark-hover-image:hover' => [
                    '--overlay-color: {{VALUE}}'
                ]
            ])
        ]);

        $SC->control('content_background_opacity_hover', 'Opacity', CM::SLIDER, [
            'range'     => $SC::range(['px', 0, 1, .01]),
            'selectors' => $SC::selectors([
                '.themeshark-hover-image:hover' => [
                    '--overlay-opacity: {{SIZE}}'
                ]
            ])
        ]);

        $this->end_controls_tab();
        $this->end_controls_tabs();

        $this->end_controls_section();
    }



    //---------------------------------//
    //--------- SECTION EFFECT --------//
    //---------------------------------//
    public function section_effect()
    {
        $SC = $this->get_SC();
        $skin = $this->control_key_skin;
        $this->start_controls_section('section_effect', [
            'label' => $SC::_('Effect'),
        ]);

        $SC->control('bg_zoom', 'Background Zoom', CM::SWITCHER, [
            'prefix_class' => '',
            'return_value' => 'themeshark-hover-image-bg-zoom',
            'default' => 'themeshark-hover-image-bg-zoom'
        ]);

        $SC->control('effect_color', 'Color', CM::COLOR, [
            'condition' => [
                "$skin!" => ['standard', 'card']
            ],
            'render_type' => 'ui',
            'selectors' => $SC::selectors([
                '.themeshark-hover-image' => [
                    '--effect-color: {{VALUE}}'
                ]
            ])
        ]);

        $SC->control('effect_color_default', null, CM::HIDDEN, [
            'condition' => ["$skin" => 'border-offset', 'effect_color' => ''],
            'default' => 'var(--e-global-color-accent)',
            'selectors' => $SC::selectors([
                '.themeshark-hover-image' => [
                    '--effect-color:{{VALUE}}'
                ]
            ])
        ]);

        $SC->control('effect_offset', 'Offset', CM::SLIDER, [
            'condition' => [
                "$skin!" => ['standard', 'card']
            ],
            'render_type' => 'ui',
            'range' => $SC::range(['px', 0, 20]),
            'default' => $SC::range_default('px', 10),
            'selectors' => $SC::selectors([
                '.themeshark-hover-image' => [
                    '--effect-offset: {{SIZE}}px'
                ]
            ])
        ]);

        $SC->control('effect_width', 'Width', CM::SLIDER, [
            'condition' => ["$skin" => 'corners'],
            'render_type' => 'ui',
            'range' => $SC::range(['px', 0, 10]),
            'default' => $SC::range_default('px', 3),
            'selectors' => $SC::selectors([
                '.themeshark-hover-image' => [
                    '--effect-width: {{SIZE}}px'
                ]
            ])
        ]);


        $this->end_controls_section();
    }






    //---------------------------------//
    //-------- SECTION CONTENT --------//
    //---------------------------------//
    protected function section_content_style()
    {
        $SC = $this->get_SC();

        $skin = $this->control_key_skin;

        $this->start_controls_section('section_content_style', [
            'label' => $SC::_('Content'),
            'tab' => CM::TAB_STYLE,
        ]);

        $SC->group_control('content_background_color', Group_Control_Background::get_type(), [
            'selector' => '{{WRAPPER}} .themeshark-hover-image-content',
            'exclude' => ['image']
        ]);

        $SC->control('text_align', 'Text Align', CM::CHOOSE, [
            'render_type' => 'ui',
            'default' => 'center',
            'options' => $SC::choice_set_text_align(['left', 'center', 'right']),
            'selectors' => $SC::selectors([
                '.themeshark-hover-image-content,
                 .themeshark-post-meta-data' => [
                    'text-align: {{VALUE}}'
                ]
            ])
        ]);

        $SC->control('heading_title', 'Title', CM::HEADING, [
            'separator' => 'before'
        ]);


        $SC->group_control('title_typography', Group_Control_Typography::get_type(), [
            'selector' => '{{WRAPPER}} .themeshark-hover-image-title',
        ]);


        $this->start_controls_tabs('title_style_tabs');
        $this->start_controls_tab('title_tab_normal', [
            'label' => $SC::_('Normal'),
        ]);

        $SC->control('title_color', 'Color', CM::COLOR, [
            'render_type' => 'ui',
            'themeshark_settings' => [CH::NO_TRANSITION => true],
            'selectors' => $SC::selectors([
                '.themeshark-hover-image-title' => [
                    'color: {{VALUE}}'
                ]
            ])
        ]);

        $SC->control('title_spacing', 'Spacing', CM::SLIDER, [
            'condition' => [
                $skin => ['standard', 'card', 'classic']
            ],
            'themeshark_settings' => [CH::NO_TRANSITION => true],
            'default' => $SC::range_default('px', 10),
            'range' => $SC::range(['px', 0, 20]),
            'selectors' => $SC::selectors([
                '.themeshark-hover-image' => [
                    '--title-spacing: {{SIZE}}{{UNIT}}'
                ]
            ])
        ]);

        $this->end_controls_tab();
        $this->start_controls_tab('title_tab_hover', [
            'label' => $SC::_('Hover'),
            'condition' => ["$skin!" => ['card', 'classic']],
        ]);


        $SC->control('title_color_hover', 'Color', CM::COLOR, [
            'condition' => ["$skin!" => 'card'],
            'render_type' => 'ui',
            'themeshark_settings' => [CH::NO_TRANSITION => true],
            'selectors' => $SC::selectors([
                '.themeshark-hover-image:hover .themeshark-hover-image-title' => [
                    'color: {{VALUE}}'
                ]
            ])
        ]);


        $SC->control('title_spacing_hover', 'Spacing', CM::SLIDER, [
            'condition' => ["$skin!" => ['card', 'classic']],
            'default' => $SC::range_default('px', 5),
            'range' => $SC::range(['px', 0, 20]),
            'themeshark_settings' => [CH::NO_TRANSITION => true],
            'selectors' => $SC::selectors([
                '.themeshark-hover-image:hover' => [
                    '--title-spacing: {{SIZE}}{{UNIT}}'
                ]
            ])
        ]);

        $this->end_controls_tab();
        $this->end_controls_tabs();

        $SC->control('heading_description', 'Description', CM::HEADING, [
            'separator' => 'before'
        ]);

        $SC->group_control('description_typography', Group_Control_Typography::get_type(), [
            'selector' => '{{WRAPPER}} .themeshark-hover-image-description',
            'fields_options' => [],
            'global' => ['default' => Global_Typography::TYPOGRAPHY_TEXT]
        ]);



        $SC->control('description_color', 'Color', CM::COLOR, [
            // 'default' => '#fff',
            'themeshark_settings' => [CH::NO_TRANSITION => true],
            'selectors' => $SC::selectors([
                '.themeshark-hover-image-description' => [
                    'color: {{VALUE}}'
                ]
            ])
        ]);

        $SC->control('description_spacing', 'Spacing', CM::SLIDER, [
            'condition' => ["$skin" => 'classic'],
            'default' => $SC::range_default('px', 5),
            'range' => $SC::range(['px', 0, 20]),
            'themeshark_settings' => [CH::NO_TRANSITION => true],
            'selectors' => $SC::selectors([
                '.themeshark-hover-image-description' => [
                    'margin-bottom: {{SIZE}}{{UNIT}}'
                ]
            ])
        ]);



        $SC->responsive_control('content_padding', 'Padding', CM::DIMENSIONS, [
            'size_units' => ['px', '%'],
            'separator' => 'before',
            'themeshark_settings' => [CH::NO_TRANSITION => true],
            'selectors' => $SC::selectors([
                '.themeshark-hover-image' => [
                    '--content-padding-top: {{TOP}}{{UNIT}}',
                    '--content-padding-right: {{RIGHT}}{{UNIT}}',
                    '--content-padding-bottom: {{BOTTOM}}{{UNIT}}',
                    '--content-padding-left: {{LEFT}}{{UNIT}}',
                ]
            ])
        ]);

        $this->end_controls_section();
    }






    //---------------------------------//
    //-------- SECTION BORDER ---------//
    //---------------------------------//
    protected function section_border_style()
    {
        $SC = $this->get_SC();

        $this->start_controls_section('section_border_style', [
            'label' => $SC::_('Border'),
            'tab' => CM::TAB_STYLE,
        ]);

        $this->start_controls_tabs('border_tabs');
        $this->start_controls_tab('border_tab_normal', [
            'label' => $SC::_('Normal'),
        ]);

        $SC->group_control('border', Group_Control_Border::get_type(), [
            'selector' => '{{WRAPPER}} .themeshark-hover-image',
            'fields_options' => [
                'border' => [
                    'default' => 'solid'
                ]
            ]
        ]);

        $SC->responsive_control('border_radius', 'Border Radius', CM::DIMENSIONS, [
            'size_units' => ['px'],
            'selectors' => $SC::selectors([
                '.themeshark-hover-image' => [
                    '--border-radius-top: {{TOP}}{{UNIT}}',
                    '--border-radius-right: {{RIGHT}}{{UNIT}}',
                    '--border-radius-bottom: {{BOTTOM}}{{UNIT}}',
                    '--border-radius-left: {{LEFT}}{{UNIT}}',
                ]
            ])
        ]);

        $SC->group_control('box_shadow', Group_Control_Box_Shadow::get_type(), [
            'selector' => '{{WRAPPER}} .themeshark-hover-image'
        ]);

        $this->end_controls_tab();

        $this->start_controls_tab('border_tab_hover', [
            'label' => $SC::_('Hover'),
        ]);


        $SC->responsive_control('border_width_hover', 'Width', CM::DIMENSIONS, [
            'condition' => ['border_border!' => ''],
            'selectors' => $SC::selectors([
                '.themeshark-hover-image' => [
                    'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}'
                ]
            ])
        ]);

        $SC->control('border_color_hover', 'Color', CM::COLOR, [
            'condition' => ['border_border!' => ''],
            'selectors' => $SC::selectors([
                '.themeshark-hover-image' => [
                    'border-color: {{VALUE}}'
                ]
            ])
        ]);

        $SC->responsive_control('border_radius_hover', 'Border Radius', CM::DIMENSIONS, [
            'size_units' => ['px'],
            'selectors' => $SC::selectors([
                '.themeshark-hover-image:hover' => [
                    '--border-radius-top: {{TOP}}{{UNIT}}',
                    '--border-radius-right: {{RIGHT}}{{UNIT}}',
                    '--border-radius-bottom: {{BOTTOM}}{{UNIT}}',
                    '--border-radius-left: {{LEFT}}{{UNIT}}',
                ]
            ])
        ]);

        $SC->group_control('box_shadow_hover', Group_Control_Box_Shadow::get_type(), [
            'selector' => '{{WRAPPER}} .themeshark-hover-image:hover',
            'exclude' => ['box_shadow_position']
        ]);

        $this->end_controls_tab();
        $this->end_controls_tabs();

        $this->end_controls_section();
    }





    //---------------------------------//
    //---------- OVERLY TABS ----------//
    //---------------------------------//

    protected function add_overlay_tabs()
    {
        $SC = $this->get_SC();

        $this->start_controls_tabs('overlay_tabs', [
            'separator' => 'before'
        ]);
        $this->start_controls_tab('overlay_tab_normal', [
            'label' => $SC::_('Normal'),
        ]);

        $SC->control('content_background_color', 'Overlay', CM::COLOR, [
            'default' => '#000',
            'render_type' => 'ui',
            'selectors' => $SC::selectors([
                '.themeshark-hover-image' => [
                    '--overlay-color: {{VALUE}}'
                ]
            ])
        ]);

        $SC->control('content_background_opacity', 'Opacity', CM::SLIDER, [
            'range' => $SC::range(['px', 0, 1, .01]),
            'default' => $SC::range_default('px', .5),
            'selectors' => $SC::selectors([
                '.themeshark-hover-image' => [
                    '--overlay-opacity: {{SIZE}}'
                ]
            ])
        ]);

        $this->end_controls_tab();

        $this->start_controls_tab('overlay_tab_hover', [
            'label' => $SC::_('Hover'),
        ]);

        $SC->control('content_background_color_hover', 'Overlay Hover', CM::COLOR, [
            'render_type' => 'ui',
            'selectors' => $SC::selectors([
                '.themeshark-hover-image:hover' => [
                    '--overlay-color: {{VALUE}}'
                ]
            ])
        ]);

        $SC->control('content_background_opacity_hover', 'Opacity', CM::SLIDER, [
            'range' => $SC::range(['px', 0, 1, .01]),
            'selectors' => $SC::selectors([
                '.themeshark-hover-image:hover' => [
                    '--overlay-opacity: {{SIZE}}'
                ]
            ])
        ]);

        $this->end_controls_tab();
        $this->end_controls_tabs();
    }




    //---------------------------------//
    //-------- SECTION READMORE -------//
    //---------------------------------//
    // public function section_readmore()
    // {
    //     $SC = $this->get_SC();
    //     $this->start_controls_section('section_readmore', [
    //         'condition' => [$this->control_key_skin => 'card'],
    //         'label' => $SC::_('Read More Bar'),
    //         'tab' => CM::TAB_CONTENT,
    //     ]);


    //     $SC->control('show_readmore', 'Show Read More', CM::SWITCHER, [
    //         'condition' => [$this->control_key_skin => 'card'],
    //         'return_value' => 'yes',
    //         'default' => 'yes'
    //     ]);

    //     $SC->control('readmore_text', 'Read More Text', CM::TEXT, [
    //         'condition' => ['show_readmore' => 'yes', $this->control_key_skin => 'card'],
    //         'default' => $SC::_('Read More'),
    //         'render_type' => 'ui',
    //         'themeshark_settings' => [
    //             Controls_Handler::LINK_TEXT => [
    //                 'selector' => '{{WRAPPER}} .themeshark-readmore-text'
    //             ]
    //         ]
    //     ]);
    //     $SC->control('readmore_bar_icon', 'Icon', CM::ICONS, [
    //         'condition' => ['show_readmore' => 'yes', $this->control_key_skin => 'card'],
    //         'fa4compatibility' => 'icon',
    //         'default' => [
    //             'value' => 'fas fa-arrow-right',
    //             'library' => 'fa-solid',
    //         ],
    //     ]);
    //     $this->end_controls_section();
    // }



    //---------------------------------//
    //----- SECTION READMORE STYLE ----//
    //---------------------------------//
    public function section_readmore_bar_style()
    {
        $SC = $this->get_SC();

        $button = '.themeshark-readmore-button';
        $button_col = '.themeshark-readmore-col.ts-col-btn';
        $text = '.themeshark-readmore-text';
        $bar = '.themeshark-readmore-bar';
        $effect_prefix = '.ts-readmore-effect';

        $this->start_controls_section('section_readmore_bar_style', [
            'condition' => ['show_readmore' => 'yes', $this->control_key_skin => 'card'],
            'tab' => CM::TAB_STYLE,
            'label' => $SC::_('Read More')
        ]);

        $SC->control('full_bar_link', 'Full Bar Link', CM::SWITCHER, [
            'condition' => ['show_readmore' => 'yes', $this->control_key_skin => 'card'],
            'return_value' => 'yes',
        ]);

        $SC->control('full_bar_effect', 'Effect', CM::SELECT, [
            'condition' => ['full_bar_link' => 'yes'],
            'default' => 'slide',
            'options' => $SC::options_select(
                ['none', 'None'],
                ['slide', 'Slide']
            ),
            'prefix_class' => 'ts-readmore-effect-'
        ]);


        $SC->control('full_bar_effect_color', 'Color', CM::COLOR, [
            'condition' => ['full_bar_effect' => 'slide', 'full_bar_link' => 'yes'],
            'global' => ['default' => Global_Colors::COLOR_ACCENT],
            'selectors' => $SC::selectors([
                '.themeshark-readmore-bar::before' => [
                    'background-color: {{VALUE}}'
                ]
            ]),
        ]);

        $SC->control('heading_icon', 'Icon', CM::HEADING, [
            'separator' => 'before'
        ]);

        $SC->responsive_control('icon_size', 'Icon Size', CM::SLIDER, [
            'range' => $SC::range(['px', 15, 40]),
            'default' => $SC::range_default('px', 25),
            'selectors' => $SC::selectors([
                $button => [
                    'font-size: {{SIZE}}{{UNIT}}'
                ]
            ])
        ]);

        $SC->responsive_control('icon_button_size', 'Button Size', CM::SLIDER, [
            'range' => $SC::range(['px', 30, 80]),
            'default' => $SC::range_default('px', 50),
            'selectors' => $SC::selectors([
                $button_col => [
                    'width: {{SIZE}}{{UNIT}}',
                    'height: {{SIZE}}{{UNIT}}'
                ]
            ])
        ]);


        $this->start_controls_tabs('tabs_icon_color_styles');
        $this->start_controls_tab('tab_icon_style_normal', [
            'label' => $SC::_('Normal')
        ]);

        $SC->control('icon_primary_color_normal', 'Icon Color', CM::COLOR, [
            'default' => '#fff',
            'selectors' => $SC::selectors([
                $button => [
                    'color: {{VALUE}}'
                ]
            ]),
        ]);

        $SC->control('icon_background_color_normal', 'Button Color', CM::COLOR, [
            'global' => ['default' => Global_Colors::COLOR_ACCENT],
            Globals_Fixer::FIX => true,
            'selectors' => $SC::selectors([
                $button_col => [
                    'background-color: {{VALUE}}'
                ]
            ]),
        ]);




        $this->end_controls_tab();
        $this->start_controls_tab('tab_icon_style_hover', [
            'label' => $SC::_('Hover'),
        ]);

        $SC->control('icon_primary_color_hover', 'Icon Color', CM::COLOR, [
            'default' => '#fff',
            'selectors' => $SC::selectors([
                "a$button_col:hover $button,
                 a$bar:hover $button" => [
                    'color: {{VALUE}}'
                ]
            ]),
        ]);

        $SC->control('icon_background_color_hover', 'Button Color', CM::COLOR, [
            'default' => '',
            'global' => ['default' => Global_Colors::COLOR_PRIMARY],
            Globals_Fixer::FIX => true,
            'selectors' => $SC::selectors([
                "a$button_col:hover,
                 a$bar:hover $button_col" => [
                    'background-color: {{VALUE}};'
                ]

            ]),
        ]);


        $this->end_controls_tab();
        $this->end_controls_tabs();
        $SC = new Shorthand_Controls($this);
        $SC->control('heading_readmore', 'Read More', CM::HEADING, [
            'separator' => 'before'
        ]);

        $SC->control('text_align_readmore', 'Text Align', CM::CHOOSE, [
            'condition' => ['use_readmore_bar' => 'yes'],
            'options' => $SC::choice_set_text_align(['left', 'right']),
            'selectors_dictionary' => [
                'left' => 'auto auto auto var(--readmore-spacing);',
                'right' => 'auto var(--readmore-spacing) auto auto;'
            ],
            'default' => 'right',
            'selectors' => $SC::selectors([
                $text => [
                    'margin: {{VALUE}}'
                ]
            ])
        ]);

        $SC->responsive_control('text_spacing_readmore', 'Spacing', CM::SLIDER, [
            'condition' => ['use_readmore_bar' => 'yes'],
            'range' => $SC::range(['px', 0, 50]),
            'default' => $SC::range_default('px', 15),
            'selectors' => $SC::selectors([
                $text => [
                    '--readmore-spacing: {{SIZE}}{{UNIT}}'
                ]
            ])
        ]);

        $SC->group_control('typography_readmore', Group_Control_Typography::get_type(), [
            'global' => ['default' => Global_Typography::TYPOGRAPHY_PRIMARY],
            'selector' => "{{WRAPPER}} $text"
        ]);

        $this->start_controls_tabs('tabs_readmore_bar_styles');
        $this->start_controls_tab('tab_readmore_bar_style_normal', [
            'label' => $SC::_('Normal'),
        ]);

        $SC->control('readmore_bar_color_normal', 'Color', CM::COLOR, [
            'global' => ['default' => Global_Colors::COLOR_TEXT],
            'selectors' => $SC::selectors([
                $text => [
                    'color: {{VALUE}};'
                ]
            ])
        ]);
        $SC->control('readmore_bar_color_background_normal', 'Background Color', CM::COLOR, [
            'default' => '#F1F1F1',
            'selectors' => $SC::selectors([
                $bar => [
                    'background-color: {{VALUE}};'
                ]
            ])
        ]);


        $this->end_controls_tab();
        $this->start_controls_tab('tab_readmore_bar_style_hover', [
            'condition' => ['full_bar_link' => 'yes'],
            'label' => $SC::_('Hover'),
        ]);

        $SC->control('readmore_bar_color_hover', 'Color', CM::COLOR, [
            'condition' => ['full_bar_link' => 'yes'],
            'default' => '',
            'selectors' => $SC::selectors([
                "{{WRAPPER}} $bar:hover $text" => [
                    'color: {{VALUE}}'
                ]
            ], null, false)
        ]);

        $SC->control('readmore_bar_color_background_hover', 'Background Color', CM::COLOR, [
            'condition' => ['full_bar_link' => 'yes'],
            'default' => '',
            'selectors' => $SC::selectors([
                "{{WRAPPER}}:not($effect_prefix-slide) $bar:hover,
                 {{WRAPPER}}$effect_prefix-slide $bar::before" => [
                    'background-color: {{VALUE}}'
                ],
            ], null, false)
        ]);



        $this->end_controls_tab();
        $this->end_controls_tabs();
        $this->end_controls_section();
    }


    protected function register_controls()
    {

        $SC = $this->get_SC();

        $this->start_controls_section('section_style_image', [
            'label' => $SC::_('Image'),
            'tab' => CM::TAB_STYLE
        ]);

        $SC->responsive_control('image_size_width', 'Width', CM::SLIDER, [
            'default' => $SC::range_default('px', 350),
            'size_units' => ['px', '%', 'vw', 'vh'],
            'range' => $SC::range(['px', 100, 1000], ['%', 5, 100], ['vw', 5, 100], ['vh', 5, 100]),
            'selectors' => $SC::selectors([
                '.themeshark-hover-image' => [
                    'width: {{SIZE}}{{UNIT}}'
                ]
            ])
        ]);

        $SC->responsive_control('image_size_max_width', 'Max Width', CM::SLIDER, [
            'size_units' => ['px', '%', 'vw', 'vh'],
            'range' => $SC::range(['px', 100, 1000], ['%', 5, 100], ['vw', 5, 100], ['vh', 5, 100]),
            'selectors' => $SC::selectors([
                '.themeshark-hover-image' => [
                    'max-width: {{SIZE}}{{UNIT}}'
                ]
            ])
        ]);



        $SC->responsive_control('image_size_height', 'Height', CM::SLIDER, [
            'default' => $SC::range_default('px', 350),
            'size_units' => ['px', 'vw', 'vh'],
            'range' => $SC::range(['px', 100, 1000], ['vw', 5, 100], ['vh', 5, 100]),
            'selectors' => $SC::selectors([
                '.themeshark-hover-image' => [
                    'height: {{SIZE}}{{UNIT}}'
                ]
            ])
        ]);


        $SC->responsive_control('image_size_max_height', 'Max Height', CM::SLIDER, [
            'size_units' => ['px', 'vw', 'vh'],
            'range' => $SC::range(['px', 100, 1000], ['vw', 5, 100], ['vh', 5, 100]),
            'selectors' => $SC::selectors([
                '.themeshark-hover-image' => [
                    'max-height: {{SIZE}}{{UNIT}}'
                ]
            ])
        ]);

        $this->end_controls_section();
    }



    public function get_tag($tag_key)
    {
        $settings = $this->get_settings();
        $is_card_layout = $settings[$this->control_key_skin] === 'card';
        $is_post = $this->is_post();
        $has_link = $is_post || !empty($settings['link']['url']) ? true : false;
        $is_full_bar_link = $settings['full_bar_link'] === 'yes' ? true : false;

        switch ($tag_key) {
            case 'item_wrap':
                if ($is_card_layout) $tag = 'div';
                else $tag = $has_link ? 'a' : 'div';
                break;
            case 'image_wrap':
                if ($is_card_layout && $has_link) $tag = 'a';
                else $tag = 'div';
                break;
            case 'readmore_bar':
                if ($is_full_bar_link && $has_link) $tag = 'a';
                else $tag = 'div';
                break;
            case 'readmore_button':
                if (!$is_full_bar_link && $has_link) $tag = 'a';
                else $tag = 'div';
                break;
            default:
                TS_Error::die("$tag_key is not a valid tag key.");
        }

        return $tag;
    }


    /**
     * adds default render attributes
     * @param {String} $skin_key control ID that holds the skin class
     */
    public function add_default_render_attributes()
    {
        $skin_key = $this->control_key_skin;
        $settings = $this->get_settings();

        $center_content_fade_skins = [
            'corners',
            'border-offset'
        ];

        $this->add_render_attribute($this->attribute_item_wrap, 'class', 'themeshark-hover-image');
        if (isset($settings[$skin_key]) && in_array($settings[$skin_key], $center_content_fade_skins)) {
            $this->add_render_attribute($this->attribute_item_wrap, 'class', 'ts-center-content-fade');
        }
        $this->add_render_attribute($this->attribute_item_content, 'class', 'themeshark-hover-image-content');
    }


    public function render_readmore_bar()
    {
        $settings = $this->get_settings();

        $tag_bar = $this->get_tag('readmore_bar');
        $tag_btn = $this->get_tag('readmore_button');

        $this->add_render_attribute('readmore_bar', 'class', 'themeshark-readmore-bar');
        $this->add_render_attribute('readmore_button', 'class', ['themeshark-readmore-col', 'ts-col-btn']);

        if ($settings['show_readmore'] === 'yes') : ?>

            <<?php echo Utils::validate_html_tag($tag_bar) . ' ' . $this->get_render_attribute_string('readmore_bar'); ?>>

                <div class='themeshark-readmore-col ts-col-text'>

                    <span class='themeshark-readmore-text'><?php esc_html_e($settings['readmore_text']); ?></span>
                </div>

                <<?php echo Utils::validate_html_tag($tag_btn) . ' ' . $this->get_render_attribute_string('readmore_button'); ?>>

                    <span class="themeshark-readmore-button">

                        <?php \Elementor\Icons_Manager::render_icon($settings['readmore_bar_icon'], ['aria-hidden' => 'true']); ?>
                    </span>
                </<?php echo Utils::validate_html_tag($tag_btn); ?>>
            </<?php echo Utils::validate_html_tag($tag_bar); ?>>
        <?php endif;
    }

    /**
     * Render
     */
    public function render_image_wrap($image_html)
    {
        $tag_wrap = $this->get_tag('image_wrap');
        // echo $tag_wrap;

        if (!empty($image_html)) : ?>

            <<?php echo Utils::validate_html_tag($tag_wrap); ?> class='themeshark-hover-image-wrap'>

                <?php echo $image_html; ?>

                <div class='themeshark-hover-image-overlay'></div>
            </<?php echo Utils::validate_html_tag($tag_wrap); ?>>
        <?php endif;
    }

    /**
     * Render
     */
    public function render_title_wrap($title_text)
    {
        $title_tag = $this->get_settings('title_size');
        if (!empty($title_text)) : ?>
            <div class='themeshark-hover-image-title-wrap'>
                <<?php echo Utils::validate_html_tag($title_tag); ?> class='themeshark-hover-image-title'>
                    <?php esc_html_e($title_text); ?>
                </<?php echo Utils::validate_html_tag($title_tag); ?>>
            </div>
        <?php endif;
    }

    /**
     * Render
     */
    public function render_description_wrap($description_text)
    {
        if (!empty($description_text)) : ?>
            <div class='themeshark-hover-image-description'>
                <p><?php esc_html_e($description_text); ?></p>
            </div>
        <?php endif;
    }

    /**
     * Creates a hover image item using the standard html template. uses $attribute_item_wrap and $attribute_item_content
     * @param {String} $wrap_tag HTML tag for wrap. ex: 'a' or 'div'
     * @param {String} $image_html HTML String for image ex '\<img src="..." />'
     * @param {String} $title_text ex: 'My Post Title'
     * @param {String} $description_text ex: 'My post description'
     */
    public function render_standard_layout($tag_wrap, $image_html, $title_text, $description_text, $additional_att_string = '')
    {
        $settings = $this->get_settings();
        $is_card_layout = $settings[$this->control_key_skin] === 'card';
        $tag_wrap = $this->get_tag('item_wrap');

        // $use_readmore_text = $settings['show_readmore'] === 'yes' && $settings['use_readmore_bar'] !== 'yes';
        $use_readmore_bar = $is_card_layout && $settings['show_readmore'] === 'yes';

        if ($is_card_layout) $tag_wrap = 'div'; ?>

        <<?php echo Utils::validate_html_tag($tag_wrap) . ' ' . $this->get_render_attribute_string($this->attribute_item_wrap)  . ' ' . $additional_att_string; ?>>
            <?php $this->render_image_wrap($image_html); ?>

            <div <?php $this->print_render_attribute_string($this->attribute_item_content); ?>>
                <?php
                $this->render_title_wrap($title_text);
                $this->render_description_wrap($description_text);
                ?>

            </div>

            <?php
            if ($use_readmore_bar) {
                $this->render_readmore_bar();
            } ?>
        </<?php echo Utils::validate_html_tag($tag_wrap); ?>>
<?php
    }
}
