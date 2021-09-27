<?php

namespace Themeshark_Elementor\Widgets;

if (!defined('ABSPATH')) exit;

use \Elementor\Utils;
use \Elementor\Repeater;
use \Elementor\Controls_Manager as CM;
use \Themeshark_Elementor\Inc\TS_Widget;
use \Elementor\Group_Control_Typography;
use \Elementor\Group_Control_Background;
use \Themeshark_Elementor\Inc\Shorthand_Controls;
use \Themeshark_Elementor\Controls\Background_Video;
use \Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use \Themeshark_Elementor\Controls\Controls_Handler as CH;
use Themeshark_Elementor\Inc\Helpers;


/**
 * ThemeShark Hover Effects Button Widget
 *
 * Button but with additional options and hover effects
 * 
 * @since 1.0.0
 */
class TS_Expander extends TS_Widget
{

    const NAME = 'ts-expander';
    const TITLE = 'Expanding Section';

    public static function register_styles()
    {
        self::widget_style('ts-expander', self::get_dir_url(__DIR__, 'ts-expander.css'));
    }

    public static function register_scripts()
    {
        self::widget_script('ts-expander', self::get_dir_url(__DIR__, 'ts-expander.js'), ['ts-background-video']);
    }

    public static function editor_scripts()
    {
        self::editor_script('ts-expander-editor', self::get_dir_url(__DIR__, 'ts-expander-editor.js'));
    }

    public function get_style_depends()
    {
        return ['ts-expander'];
    }

    public function get_script_depends()
    {
        return ['ts-expander'];
    }

    public function get_icon()
    {
        return 'tsicon-expanding-section';
    }

    public function get_categories()
    {
        return ['themeshark'];
    }
    public function get_keywords()
    {
        return self::keywords(['expander', 'expanding', 'slides', 'frames']);
    }

    protected function add_repeater_controls(Repeater $repeater)
    {
        $SCR = new Shorthand_Controls($repeater);


        $slide_bg = '{{CURRENT_ITEM}}.ts-expander-slide-bg';
        $slide_bg_overlay = "$slide_bg .ts-expander-slide-bg-overlay";
        $slide_heading = '{{CURRENT_ITEM}} .themeshark-expander-slide-heading';
        $slide_content =  '{{CURRENT_ITEM}} .themeshark-expander-slide-content';

        $repeater->start_controls_tabs('slide_tabs');
        $repeater->start_controls_tab('slide_content_tab', [
            'label' => $SCR::_('Content'),
        ]);

        $SCR->control('title', 'Title', CM::TEXTAREA, [
            'rows' => 2,
            'placeholder' => $SCR::_('Enter your text'),
            'default' => $SCR::_('Add Your Heading Text Here'),
            'label_block' => true,
            'themeshark_settings' => [
                CH::LINK_TEXT => ['selector' => $slide_heading]
            ]
        ]);

        $SCR->control('slide_heading_tag', 'HTML Tag', CM::SELECT, [
            'default' => 'h2',
            'themeshark_settings' => [
                CH::LINK_REPLACE_TAG => ['selector' => $slide_heading]
            ],
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
            ]
        ]);

        $SCR->control('content', 'Content', CM::WYSIWYG, [
            'separator' => 'before',
            'frontend_available' => true,
            'default' => $this->get_repeater_defaults()[0]['content'],
        ]);

        $repeater->end_controls_tab();


        // SLIDE STYLES
        $repeater->start_controls_tab('slide_style_tab', [
            'label' => $SCR::_('Styles')
        ]);

        $SCR->control('background_image', 'Background Image', CM::MEDIA, [
            'title' => $SCR::_('Background Image'),
            'selectors' => $SCR::selectors([
                $slide_bg => [
                    'background-image: url("{{URL}}")'
                ],
            ]),
        ]);

        $SCR->control('background_position', 'Position', CM::SELECT, [
            'condition' => ['background_image[url]!' => ''],
            'options' => $SCR::options_select(
                ['', 'Default'],
                ['center center', 'Center Center'],
                ['center left', 'Center Left'],
                ['center right', 'Center Right'],
                ['top center', 'Top Center'],
                ['top left', 'Top Left'],
                ['top right', 'Top Right'],
                ['bottom center', 'Bottom Center'],
                ['bottom left', 'Bottom Left'],
                ['bottom right', 'Bottom Right']
            ),
            'selectors' => $SCR::selectors([
                $slide_bg => [
                    'background-position: {{VALUE}}'
                ],
            ]),
        ]);

        $SCR->control('background_repeat', 'Repeat', CM::SELECT, [
            'condition' => ['background_image[url]!' => ''],
            'options' => $SCR::options_select(
                ['', 'Default'],
                ['no-repeat', 'No-repeat'],
                ['repeat', 'Repeat'],
                ['repeat-x', 'Repeat-x'],
                ['repeat-y', 'Repeat-y']
            ),
            'selectors' => $SCR::selectors([
                $slide_bg => [
                    'background-repeat: {{VALUE}}'
                ]
            ])
        ]);


        $SCR->control('background_size', 'Size', CM::SELECT, [
            'condition' => ['background_image[url]!' => ''],
            'default' => '',
            'options' => $SCR::options_select(
                ['', 'Default'],
                ['auto', 'Auto'],
                ['cover', 'Cover'],
                ['contain', 'Contain']
            ),
            'selectors' => $SCR::selectors([
                $slide_bg => [
                    'background-size: {{VALUE}}'
                ]
            ])
        ]);

        $SCR->control('slide_background_overlay', 'Overlay Color', CM::COLOR, [
            'render_type' => 'ui',
            'separator' => 'before',
            'selectors' => $SCR::selectors([
                $slide_bg_overlay => [
                    'background-color: {{VALUE}}'
                ]
            ])
        ]);

        $SCR->control('slide_background_overlay_opacity', 'Overlay Opacity', CM::SLIDER, [
            'condition' => ['slide_background_overlay!' => ''],
            'range' => $SCR::range(['px', 0, 1, 0.01]),
            'default' => $SCR::range_default('px', 0.5),
            'render_type' => 'ui',
            'themeshark_settings' => [
                CH::NO_TRANSITION => true
            ],
            'selectors' => $SCR::selectors([
                $slide_bg_overlay => [
                    'opacity: {{SIZE}}'
                ]
            ])
        ]);

        $SCR->control('slide_text_align', 'Text Align', CM::CHOOSE, [
            'render_type' => 'ui',
            'separator' => 'before',
            'options' => $SCR::options_choose(
                ['left', 'Left', 'eicon-text-align-left'],
                ['center', 'Center', 'eicon-text-align-center'],
                ['right', 'Right', 'eicon-text-align-right']
            ),
            'selectors' => $SCR::selectors([
                "$slide_heading, $slide_content" => [
                    'text-align: {{VALUE}}'
                ]
            ])
        ]);

        $SCR->control('slide_heading_color', 'Heading Color', CM::COLOR, [
            'render_type' => 'ui',
            'selectors' => $SCR::selectors([
                $slide_heading => [
                    'color: {{VALUE}}'
                ]
            ])
        ]);

        $SCR->control('slide_content_color', 'Content Color', CM::COLOR, [
            'render_type' => 'ui',
            'selectors' => $SCR::selectors([
                $slide_content => [
                    'color: {{VALUE}}'
                ]
            ])
        ]);

        $repeater->end_controls_tab();
        $repeater->end_controls_tabs();
    }

    protected function get_repeater_defaults()
    {
        $defaults = [
            'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.',
            'image' => ['url' => Utils::get_placeholder_image_src()],
        ];
        return [
            array_merge($defaults, ['title' => 'First Slide']),
            array_merge($defaults, ['title' => 'Second Slide']),
            array_merge($defaults, ['title' => 'Third Slide']),
        ];
    }

    protected function _register_controls()
    {
        $SC = new Shorthand_Controls($this);

        $this->start_controls_section('slides_section', [
            'label' => $SC::_('Slides'),
            'tab' => CM::TAB_CONTENT,
        ]);

        $repeater = new Repeater();

        $this->add_repeater_controls($repeater);

        $SC->control('slides', 'Items', CM::REPEATER, [
            'fields' => $repeater->get_controls(),
            'default' => $this->get_repeater_defaults(),
            'render_type' => 'template',
            'title_field' => '{{{ title }}}',
            'themeshark_settings' => [
                CH::NEW_SLIDES_ADD_HANDLERS => true,
                'expander_handle_repeater_controls' => true, // Used only for expander. defined in widget handler file
            ]
        ]);

        $this->end_controls_section();


        $this->start_controls_section('section_heading', [
            'label' => $SC::_('Heading'),
            'tab' => CM::TAB_CONTENT
        ]);

        $SC->control('heading_text', 'Heading', CM::TEXTAREA, [
            'dynamic' => ['active' => true],
            'rows' => 2,
            'placeholder' => $SC::_('Enter your text'),
            'default' => $SC::_('Add Your Heading Text Here'),
        ]);

        $SC->control('heading_tag', 'HTML Tag', CM::SELECT, [
            'dynamic' => ['active' => true],
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
        ]);

        $SC->control('sub_heading', 'Sub Heading', CM::TEXTAREA, [
            'dynamic' => ['active' => true],
            'default' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor.',
            'separator' => 'before',
            'rows' => 5,
        ]);

        $this->end_controls_section();


        $this->start_controls_section('section_slides_background', [
            'label' => $SC::_('Background'),
            'tab' => CM::TAB_STYLE,
        ]);

        $SC->control('heading_bg', 'Background', CM::HEADING, [
            'separator' => 'before'
        ]);

        $SC->group_control('background', Group_Control_Background::get_type(), [
            'types' => ['classic', 'gradient'],
            'selector' => '{{WRAPPER}} .themeshark-expander-standard-bg',
            'types' => ['classic', 'gradient', 'video'],
            'fields_options' => [
                'color' => ['themeshark_settings' => [CH::NO_TRANSITION => true]],
                'background' => ['default' => 'classic', 'frontend_available' => true],
                'image' => ['default' => ['url' => Utils::get_placeholder_image_src()], 'render_type' => 'ui'],
                'position' => ['default' => 'center center'],
                'size' => ['default' => 'cover'],
            ]
        ]);

        $SC->control('video_pause_on_deintersect', 'Pause When Inactive', CM::SWITCHER, [
            'condition' => ['background_background' => 'video'],
            'prefix_class' => '',
            'return_value' => 'video-pause-on-deintersect',
            'render_type' => 'template',
            'description' => 'Pauses the when the slides are not in the expanded state'
        ]);


        $SC->responsive_control('starting_bg_scale', 'Starting Background Scale', CM::SLIDER, [
            'condition' => ['background_background!' => 'video'],
            'range' => $SC::range(['px', 0, 1, 0.01]),
            'default' => $SC::range_default('px', 0.9),
            'themeshark_settings' => [CH::NO_TRANSITION => true],
            'render_type' => 'ui',
            'separator' => 'before',
            'selectors' => $SC::selectors([
                '{{WRAPPER}} .themeshark-expander-standard-bg' => [
                    'transform: scale({{SIZE}})'
                ],
                '{{WRAPPER}}.themeshark-scrolled .themeshark-expander-standard-bg' => [
                    'transform: scale(1)'
                ]
            ], null, false)
        ]);


        $SC->control('heading_bg_overlay', 'Background Overlay', CM::HEADING, [
            'separator' => 'before'
        ]);



        //-------BG ACTIVE / INACTIVE TABS -----//
        $this->start_controls_tabs('bg_overlay_tabs');

        $overlay_color_selectors = ['.themeshark-expander-bg-overlay' => ['background-color: {{VALUE}}']];
        $overlay_opacity_selectors = ['.themeshark-expander-bg-overlay' => ['opacity: {{SIZE}};']];

        //NORMAL
        $this->start_controls_tab('bg_overlay_tab_before', [
            'label' => $SC::_('Before Active'),
            'themeshark_settings' => ['expander_handle_bg_state_tabs' => 'show_frames']
        ]);

        $SC->control('background_overlay_color', 'Color', CM::COLOR, [
            'render_type' => 'ui',
            'themeshark_settings' => [CH::NO_TRANSITION => true],
            'default' => '#000000',
            'selectors' => $SC::selectors($overlay_color_selectors)
        ]);

        $SC->control('bg_overlay_opacity', 'Opacity', CM::SLIDER, [
            'themeshark_settings' => [CH::NO_TRANSITION => true],
            'render_type' => 'ui',
            'range' => $SC::range(['px', 0, 1, 0.01]),
            'default' => $SC::range_default('px', 0),
            'selectors' => $SC::selectors($overlay_opacity_selectors)
        ]);

        $this->end_controls_tab();

        //ACTIVE
        $this->start_controls_tab('bg_overlay_tab_after', [
            'label' => $SC::_('After Active'),
            'themeshark_settings' => ['expander_handle_bg_state_tabs' => 'hide_frames']
        ]);

        $SC->control('background_overlay_color_after', 'Color', CM::COLOR, [
            'render_type' => 'ui',
            'themeshark_settings' => [CH::NO_TRANSITION => true],
            'selectors' => $SC::selectors($overlay_color_selectors, '.themeshark-scrolled')
        ]);

        $SC->control('bg_overlay_opacity_after', 'Opacity', CM::SLIDER, [
            'themeshark_settings' => [CH::NO_TRANSITION => true],
            'render_type' => 'ui',
            'range' => $SC::range(['px', 0, 1, 0.01]),
            'default' => $SC::range_default('px', .5),
            'selectors' => $SC::selectors($overlay_opacity_selectors, '.themeshark-scrolled')
        ]);

        $this->end_controls_tab();
        $this->end_controls_tabs();
        $this->end_controls_section();


        $this->start_controls_section('section_slide_styles', [
            'label' => $SC::_('Slides'),
            'tab' => CM::TAB_STYLE
        ]);


        $SC->responsive_control('slide_height', 'Slide Height (vh)', CM::SLIDER, [
            'range' => $SC::range(['px', 100, 350]),
            'default' => $SC::range_default('px', 180),
            'separator' => 'before',
            'render_type' => 'ui',
            'selectors' => $SC::selectors([
                '.themeshark-expander' => [
                    '--slide-height: {{SIZE}}vh'
                ]
            ]),
        ]);

        $SC->control('heading_slides_content_wrapper', 'Content Wrapper', CM::HEADING, [
            'separator' => 'before'
        ]);

        $SC->responsive_control('slides_text_align', 'Text Align', CM::CHOOSE, [
            'default' => 'left',
            'render_type' => 'ui',
            'options' => $SC::options_choose(
                ['left', 'Left', 'eicon-text-align-left'],
                ['center', 'Center', 'eicon-text-align-center'],
                ['right', 'Right', 'eicon-text-align-right']
            ),
            'selectors' => $SC::selectors([
                '.themeshark-expander-content-block-inner' => [
                    'text-align: {{VALUE}}'
                ]
            ])
        ]);

        $SC->responsive_control('slide_content_offset', 'Vertical Offset', CM::SLIDER, [
            'range' => $SC::range(['px', -100, 100]),
            'default' => $SC::range_default('px', 0),
            'render_type' => 'ui',
            'selectors' => $SC::selectors([
                '.themeshark-expander' => [
                    '--slide-content-vertical-offset:{{SIZE}}{{UNIT}}'
                ]
            ])
        ]);

        $SC->responsive_control('slide_max_width', 'Max Width', CM::SLIDER, [
            'size_units' => ['px', '%'],
            'range' => $SC::range(['px', 500, 1000], ['%', 10, 100]),
            'default' => $SC::range_default('px', 750),
            'render_type' => 'ui',
            'selectors' => $SC::selectors([
                '.themeshark-expander-content-wrap' => [
                    'max-width: {{SIZE}}{{UNIT}}'
                ]
            ])
        ]);

        $SC->responsive_control('slide_padding', 'Padding', CM::DIMENSIONS, [
            'size_units' => ['px', '%'],
            'render_type' => 'ui',
            'selectors' => $SC::selectors([
                '.themeshark-expander-content-wrap' => [
                    'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}'
                ]
            ])
        ]);

        //SLIDE HEADING
        $SC->control('heading_slides_heading', 'Heading', CM::HEADING, [
            'separator' => 'before'
        ]);

        $SC->group_control('slide_heading_typography', Group_Control_Typography::get_type(), [
            'selector' =>  '{{WRAPPER}} .themeshark-expander-slide-heading',
            'separator' => 'before'
        ]);

        $SC->control('slide_heading_color', 'Color', CM::COLOR, [
            'render_type' => 'ui',
            'selectors' => $SC::selectors([
                '.themeshark-expander-slide-heading' => [
                    'color: {{VALUE}}'
                ]
            ])
        ]);

        $SC->responsive_control('slide_heading_spacing', 'Spacing', CM::SLIDER, [
            'range' => $SC::range(['px', 0, 80]),
            'render_type' => 'ui',
            'selectors' => $SC::selectors([
                '.themeshark-expander-slide-heading' => [
                    'margin-bottom:{{SIZE}}{{UNIT}}'
                ]
            ])
        ]);

        //SLIDE CONTENT
        $SC->control('heading_slides_content', 'Content', CM::HEADING, [
            'separator' => 'before'
        ]);

        $SC->group_control('slide_content_typography', Group_Control_Typography::get_type(), [
            'selector' => '{{WRAPPER}} .themeshark-expander-slide-content'
        ]);

        $SC->control('slide_content_color', 'Color', CM::COLOR, [
            'render_type' => 'ui',
            'selectors' => $SC::selectors([
                '.themeshark-expander-slide-content' => [
                    'margin-bottom:{{SIZE}}{{UNIT}}'
                ]
            ])
        ]);

        $this->end_controls_section();


        //SECTION HEADING STYLES
        $this->start_controls_section('section_heading_styles', [
            'label' => $SC::_('Heading'),
            'tab' => CM::TAB_STYLE,
        ]);

        $SC->responsive_control('heading_max_width', 'Max Width', CM::SLIDER, [
            'range' => $SC::range(['px', 500, 1600], ['%', 10, 100]),
            'default' => $SC::range_default('px', 1000),
            'render_type' => 'ui',
            'selectors' => $SC::selectors([
                '.themeshark-expander-title,
                 .themeshark-expander-sub-title' => [
                    'max-width: {{SIZE}}{{UNIT}};'
                ]
            ])
        ]);
        $SC->responsive_control('heading_margin', 'Margin', CM::DIMENSIONS, [
            'themeshark_settings' => [CH::NO_TRANSITION => true],
            'size_units' => ['px', '%'],
            'render_type' => 'ui',
            'selectors' => $SC::selectors([
                '.themeshark-expander-title-wrap-inner' => [
                    'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}'
                ]
            ])
        ]);

        $SC->responsive_control('heading_text_align', 'Text Align', CM::CHOOSE, [
            'default' => 'center',
            'render_type' => 'ui',
            'options' => $SC::options_choose(
                ['left', 'Left', 'eicon-text-align-left'],
                ['center', 'Center', 'eicon-text-align-center'],
                ['right', 'Right', 'eicon-text-align-right']
            ),
            'selectors' => $SC::selectors([
                '.themeshark-expander-title, 
                 .themeshark-expander-sub-title' => [
                    'text-align: {{VALUE}}'
                ]
            ])
        ]);

        //HEADING
        $SC->control('heading_heading', 'Heading', CM::HEADING, [
            'separator' => 'before'
        ]);

        $SC->group_control('heading_typography', Group_Control_Typography::get_type(), [
            'selector' => '{{WRAPPER}} .themeshark-expander-title',
        ]);

        $SC->control('heading_color', 'Color', CM::COLOR, [
            'global' => ['default' => Global_Colors::COLOR_PRIMARY],
            'render_type' => 'ui',
            'selectors' => $SC::selectors([
                '.themeshark-expander-title' => [
                    'color: {{VALUE}}'
                ]
            ]),
        ]);

        $SC->responsive_control('heading_spacing', 'Spacing', CM::SLIDER, [
            'range' => $SC::range(['px', 0, 80]),
            'render_type' => 'ui',
            'selectors' => $SC::selectors([
                '.themeshark-expander-title' => [
                    'margin-bottom:{{SIZE}}{{UNIT}}'
                ]
            ])
        ]);


        //SUB HEADING
        $SC->control('heading_sub_heading', 'Sub Heading', CM::HEADING, [
            'separator' => 'before'
        ]);

        $SC->group_control('sub_heading_typography', Group_Control_Typography::get_type(), [
            'selector' => '{{WRAPPER}} .themeshark-expander-sub-title',
        ]);

        $SC->control('sub_heading_color', 'Color', CM::COLOR, [
            'global' => ['default' => Global_Colors::COLOR_PRIMARY],
            'render_type' => 'ui',
            'selectors' => $SC::selectors([
                '.themeshark-expander-sub-title' => [
                    'color: {{VALUE}};'
                ]
            ]),
        ]);

        $this->end_controls_section();

        //FRAMES
        $this->start_controls_section('frame_styles', [
            'label' => $SC::_('Frame'),
            'tab' => CM::TAB_STYLE,
        ]);

        $SC->control('frame_color', 'Frame Color', CM::COLOR, [
            'themeshark_settings' => [CH::NO_TRANSITION => true],
            'default' => '#ffffff',
            'render_type' => 'ui',
            'selectors' => $SC::selectors([
                '.themeshark-expander-frame' => [
                    'background-color: {{VALUE}}'
                ]
            ])
        ]);


        $top = '.ts-frame-top';
        $right = '.ts-frame-right';
        $bottom = '.ts-frame-bottom';
        $left = '.ts-frame-left';

        $SC->responsive_control('frame_padding', 'Frame Min Width', CM::DIMENSIONS, [
            'size_units' => ['px', '%'],
            'render_type' => 'ui',
            'themeshark_settings' => [CH::NO_TRANSITION => true],
            'selectors' => $SC::selectors([
                '_vars' => ['SCROLLED' => '{{WRAPPER}}.themeshark-scrolled'],

                "{{WRAPPER}} $top" => ['min-height: {{TOP}}{{UNIT}}'],
                "%SCROLLED% $top" => ['min-height: 0{{UNIT}}'],

                "{{WRAPPER}} $right" => ['min-width: {{RIGHT}}{{UNIT}}'],
                "%SCROLLED% $right" => ['min-width: 0{{UNIT}}'],

                "{{WRAPPER}} $bottom" => ['min-height: {{BOTTOM}}{{UNIT}}'],
                "%SCROLLED% $bottom" => ['min-height: 0{{UNIT}}'],

                "{{WRAPPER}} $left" => ['min-width: {{LEFT}}{{UNIT}}'],
                "%SCROLLED% $left" => ['min-width: 0{{UNIT}}'],

            ], null, false),
        ]);

        $SC->responsive_control('frame_inner_height', 'Inner Height', CM::SLIDER, [
            'size_units' => ['px', '%'],
            'themeshark_settings' => [CH::NO_TRANSITION => true],
            'range' => $SC::range(['px', 100, 800], ['%', 10, 100]),
            'default' => $SC::range_default('px', 520),
            'selectors' => $SC::selectors([
                "$top, $bottom" => [
                    'height: calc(50% - ({{SIZE}}{{UNIT}} / 2))'
                ]
            ])
        ]);

        $SC->responsive_control('frame_inner_width', 'Inner Width', CM::SLIDER, [
            'size_units' => ['px', '%'],
            'themeshark_settings' => [CH::NO_TRANSITION => true],
            'range' => $SC::range(['px', 100, 1500], ['%', 10, 100]),
            'default' => $SC::range_default('px', 900),
            'selectors' => $SC::selectors([
                "$left, $right" => [
                    'width: calc(50% - ({{SIZE}}{{UNIT}} / 2))'
                ]
            ])
        ]);

        $this->end_controls_section();
    }


    protected function print_slide_background(array $slide, $index)
    {
        // $bg_type = $slide['slide_background_background'];
        // if (!(strlen($bg_type) > 0)) return;

        $slide_id = $slide['_id'];
        $slide_wrap_id = 'bg_' . $slide_id;
        $this->add_render_attribute($slide_wrap_id, [
            'class' => [
                'ts-expander-slide-bg',
                'elementor-repeater-item-' . $slide_id
            ],
            'data-bgid' => $slide_id
        ]);
?>
        <div <?php $this->print_render_attribute_string($slide_wrap_id); ?>>
            <div class='ts-expander-slide-bg-overlay'></div>
        </div>
    <?php
    }

    protected function print_slide(array $slide, $index)
    {
        // $bg_type = $slide['slide_background_background'];
        $slide_id = $slide['_id'];

        $background_settings = [];

        // if ($bg_type === 'classic') {
        $bg_url = $slide['background_image']['url'];
        // $bg_color = $slide['slide_background_color'];

        // $background_settings['color'] = $bg_color;
        if (strlen($bg_url) > 0) $background_settings['image'] = $bg_url;
        // }

        $this->add_render_attribute($slide_id, [
            'class' => [
                'themeshark-expander-content-block',
                'elementor-repeater-item-' . $slide_id
            ],
            'data-slide' => $slide_id,
            'data-slide_num' => $index
        ]);

        if (sizeof($background_settings) > 0) {
            $this->add_render_attribute($slide_id, 'data-background',  json_encode($background_settings));
        }
    ?>
        <div <?php $this->print_render_attribute_string($slide_id); ?>>
            <div class="themeshark-expander-content-block-inner">
                <<?php echo Utils::validate_html_tag($slide['slide_heading_tag']); ?> class='themeshark-expander-slide-heading'><?php echo $slide['title'] ?></<?php echo Utils::validate_html_tag($slide['slide_heading_tag']); ?>>
                <div class='themeshark-expander-slide-content'>
                    <?php
                    echo Helpers::esc_wysiwyg($slide['content'])
                    ?>
                </div>
            </div>
        </div>

    <?php
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();




        $slides = $settings['slides'];

        $has_title = strlen($settings['heading_text']) > 0 ? true : false;
        $has_subtitle = strlen($settings['sub_heading']) > 0 ? true : false;

        $this->add_render_attribute('wrap', [
            'class' => 'themeshark-expander',
            'style' => '--slide-count: ' . sizeof($slides),
        ]);

        $frame_dimension_keys = [
            'frame_inner_width',
            'frame_inner_height',
            'frame_inner_width_tablet',
            'frame_inner_height_tablet',
            'frame_inner_width_mobile',
            'frame_inner_height_mobile',
        ];

        $frame_dimensions = [];
        foreach ($frame_dimension_keys as $key) {
            $control = $settings[$key];
            $size = $control['size'];
            $unit = $control['unit'];
            if (!empty($size)) {
                $frame_dimensions[$key] = $size . $unit;
            }
        }

        $this->add_render_attribute('inner_wrap', [
            'class' => 'themeshark-expander-inner',
            'data-framedimensions' => json_encode($frame_dimensions)
        ]);

    ?>
        <div <?php $this->print_render_attribute_string('wrap'); ?>>

            <div <?php $this->print_render_attribute_string('inner_wrap'); ?>>
                <?php if ($has_title || $has_subtitle) : ?>
                    <div class="themeshark-expander-title-wrap">
                        <div class="themeshark-expander-title-wrap-inner">

                            <?php if ($has_title) : ?>
                                <<?php echo Utils::validate_html_tag($settings['heading_tag']) ?> class="themeshark-expander-title"><?php echo $settings['heading_text']; ?></<?php echo Utils::validate_html_tag($settings['heading_tag']); ?>>
                            <?php endif; ?>

                            <?php if ($has_subtitle) : ?>
                                <div class='themeshark-expander-sub-title'><?php echo $settings['sub_heading']; ?></div>
                            <?php endif; ?>

                        </div>
                    </div>
                <?php endif; ?>

                <div class="themeshark-expander-frame ts-frame-top"></div>
                <div class="themeshark-expander-frame ts-frame-right"></div>
                <div class="themeshark-expander-frame ts-frame-bottom"></div>
                <div class="themeshark-expander-frame ts-frame-left"></div>


                <div class='themeshark-expander-standard-bg'>
                    <?php Background_Video::render($this, 'background') ?>
                    <div class="themeshark-expander-bg-overlay"></div>
                </div>


                <div class='themeshark-expander-slide-backgrounds'>
                    <?php foreach ($slides as $index => $slide) {
                        $this->print_slide_background($slide, $index);
                    } ?>
                </div>

                <div class="themeshark-expander-content-wrap">
                    <div class="themeshark-expander-content">

                        <?php foreach ($slides as $index => $slide) {
                            $this->print_slide($slide, $index);
                        } ?>

                    </div>
                </div>
            </div>
        </div>

    <?php
    }

    protected function print_slides_background_template()
    {

    ?>
        <# var slides=settings.slides; var currentSlideId=getCurrentSlideId(); for (var i in slides) { var slide=slides[i]; var slide_id=slide._id; var slide_wrap_id='bg_' + slide_id; var slide_id_class='elementor-repeater-item-' + slide_id; view.addRenderAttribute(slide_wrap_id, { 'class' : ['ts-expander-slide-bg', slide_id_class], 'data-bgid' : slide_id }); if (currentSlideId===slide_id) { view.addRenderAttribute(slide_wrap_id, 'class' , 'active' ); } #>
            <div {{{view.getRenderAttributeString(slide_wrap_id)}}}>
                <div class='ts-expander-slide-bg-overlay'></div>

            </div>
            <# } #>
            <?php

        }

        protected function print_slides_template()
        {
            ?>
                <# var currentSlideId=getCurrentSlideId(); var slides=settings.slides; for (var i in slides) { var slide=slides[i]; var slide_id=slide._id; var background_settings=[]; var bg_url=slide.background_image.url; if (bg_url.length> 0) background_settings.image = bg_url;

                    var slide_content_id_class = 'elementor-repeater-item-' + slide_id;

                    view.addRenderAttribute(slide_id, {
                    'class': "themeshark-expander-content-block " + slide_content_id_class,
                    'data-slide': slide_id,
                    'data-slide_num': i
                    });
                    if (currentSlideId === slide_id) {
                    view.addRenderAttribute(slide_id, 'class', 'active')
                    }
                    #>


                    <div {{{view.getRenderAttributeString( slide_id )}}}>
                        <div class="themeshark-expander-content-block-inner">
                            <{{slide.slide_heading_tag}} class='themeshark-expander-slide-heading'>{{{slide.title}}}</{{slide.slide_heading_tag}}>
                            <div class='themeshark-expander-slide-content'>{{{slide.content}}}</div>
                        </div>
                    </div>

                    <# } #>


                    <?php

                }



                protected function content_template()
                {

                    ?>
                        <# function getCurrentSlideId() { var currentPageView=themeshark.themesharkControlsHandler.currentPageView; if (!currentPageView) { return null; } var activeSection=currentPageView.activeSection; if (activeSection !=='slides_section' ) { return null; } var $activeTab=currentPageView.$el.find('.editable'); if ($activeTab[0]) { return $activeTab.find(`[data-setting='_id' ]`).val(); } return null; } function getActiveSection() { var currentPageView=elementor.getPanelView().getCurrentPageView(); if (!currentPageView) { return null; } return currentPageView.activeSection; } var activeSection=getActiveSection(); var currentSlideId=getCurrentSlideId(); var slides=settings.slides; var has_title=settings.heading_text.length> 0 ? true : false;
                            var has_subtitle = settings.sub_heading.length > 0 ? true : false;

                            var slide_height = settings.slide_height;

                            view.addRenderAttribute('wrap', {
                            'class': 'themeshark-expander ts-expander-rendering themeshark-no-transition',
                            'style': '--slide-count: ' + slides.length
                            });


                            if (currentSlideId !== null) {
                            view.addRenderAttribute('_wrapper', 'class', 'themeshark-scrolled')
                            }

                            if (settings.pause_on_scroll_out === 'yes') {
                            view.addRenderAttribute('wrap', {
                            'data-pause_on_scroll_out': settings.pause_on_scroll_out
                            });
                            }

                            var frame_dimension_keys = [
                            'frame_inner_width',
                            'frame_inner_height',
                            'frame_inner_width_tablet',
                            'frame_inner_height_tablet',
                            'frame_inner_width_mobile',
                            'frame_inner_height_mobile',
                            ];

                            var frame_dimensions = {};
                            for (var key of frame_dimension_keys) {
                            var control = settings[key];
                            var size = control['size'];
                            var unit = control['unit'];
                            if (size !== '' && size !== null) {
                            frame_dimensions[key] = size + unit;
                            }
                            }

                            view.addRenderAttribute('inner_wrap', {
                            'class': 'themeshark-expander-inner',
                            'data-framedimensions': JSON.stringify(frame_dimensions),
                            });
                            #>

                            <div {{{view.getRenderAttributeString('wrap')}}}>
                                <div {{{view.getRenderAttributeString('inner_wrap')}}}>

                                    <!-- Heading -->
                                    <# if(has_title || has_subtitle){ #>
                                        <div class="themeshark-expander-title-wrap">
                                            <div class="themeshark-expander-title-wrap-inner">

                                                <# if(has_title ){ #>
                                                    <{{settings.heading_tag}} class="themeshark-expander-title">
                                                        {{{settings.heading_text}}}
                                                    </{{settings.heading_tag}}>
                                                    <# } #>

                                                        <# if(has_subtitle ){ #>
                                                            <div class='themeshark-expander-sub-title'>{{{settings.sub_heading}}}</div>
                                                            <# } #>

                                            </div>
                                        </div>
                                        <# } #>

                                            <!-- Frame -->
                                            <div class="themeshark-expander-frame ts-frame-top"></div>
                                            <div class="themeshark-expander-frame ts-frame-right"></div>
                                            <div class="themeshark-expander-frame ts-frame-bottom"></div>
                                            <div class="themeshark-expander-frame ts-frame-left"></div>

                                            <!-- STANDARD BG -->
                                            <div class='themeshark-expander-standard-bg'>
                                                <?php Background_Video::render_template('background'); ?>
                                                <div class="themeshark-expander-bg-overlay"></div>
                                            </div>


                                            <!-- Individual Slide Backgrounds -->
                                            <div class='themeshark-expander-slide-backgrounds'>
                                                <?php $this->print_slides_background_template(); ?>
                                            </div>

                                            <!-- Slides Content -->
                                            <div class="themeshark-expander-content-wrap">
                                                <div class="themeshark-expander-content">
                                                    <?php $this->print_slides_template(); ?>
                                                </div>
                                            </div>

                                </div>
                            </div>
                    <?php

                }
            }
