<?php

namespace Themeshark_Elementor\Widgets\Posts\Skins;

use Elementor\Controls_Manager as CM;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Group_Control_Css_Filter;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Typography;
use Elementor\Skin_Base as Elementor_Skin_Base;
use Elementor\Widget_Base;
use Themeshark_Elementor\Inc\Shorthand_Controls;
use ElementorPro\Plugin;
use Elementor\Utils;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

abstract class Skin_Base extends Elementor_Skin_Base
{

    /**
     * @var \Themeshark_Elementor\Widgets\Posts\TS_Posts $parent 
     */
    public $parent;

    /**
     * @var string Save current permalink to avoid conflict with plugins the filters the permalink during the post render.
     */
    protected $current_permalink;

    protected function _register_controls_actions()
    {
        add_action('elementor/element/ts-posts/section_layout/before_section_end', [$this, 'register_controls']);
        add_action('elementor/element/ts-posts/section_query/after_section_end', [$this, 'register_style_sections']);
    }

    private $_SC = null;

    /**
     * @return \Themeshark_Elementor\Inc\Shorthand_Controls
     */
    public function shorthand_controls()
    {
        if ($this->_SC === null) $this->_SC = new Shorthand_Controls($this);
        return $this->_SC;
    }

    public function register_style_sections(Widget_Base $widget)
    {
        $this->parent = $widget;

        $this->register_design_controls();
    }

    public function register_controls(Widget_Base $widget)
    {
        $this->parent = $widget;

        $this->register_columns_controls();
        $this->register_post_count_control();
        $this->register_thumbnail_controls();
        $this->register_title_controls();
        $this->register_excerpt_controls();
        $this->register_meta_data_controls();
        $this->register_read_more_controls();
        $this->register_link_controls();
        $this->register_date_badge_controls();
    }

    public function register_design_controls()
    {
        $this->register_design_layout_controls();
        $this->register_design_image_controls();
        $this->register_design_content_controls();
    }


    // THUMBNAIL
    //-----------------------------------------------

    protected function register_thumbnail_controls()
    {

        $SC = $this->shorthand_controls();

        $this->add_control(
            'thumbnail',
            [
                'label' => __('Image Position', THEMESHARK_TXTDOMAIN),
                'type' => CM::SELECT,
                'default' => 'top',
                'options' => [
                    'top' => __('Top', THEMESHARK_TXTDOMAIN),
                    'left' => __('Left', THEMESHARK_TXTDOMAIN),
                    'right' => __('Right', THEMESHARK_TXTDOMAIN),
                    'none' => __('None', THEMESHARK_TXTDOMAIN),
                ],
                'prefix_class' => 'themeshark-posts--thumbnail-',
            ]
        );


        $SC->control('thumbnail_flip_responsive', 'Stack Break Point', CM::SELECT, [
            'condition'    => [$this->get_control_id('thumbnail') => ['left', 'right']],
            'prefix_class' => 'themeshark-post-stack-break-',
            'default'      => 'none',
            'options'      => $SC::options_select(
                ['none',   'None'],
                ['tablet', 'Tablet'],
                ['mobile', 'Mobile']
            )
        ]);

        //HIDDEN - NOT FUNCTIONING CURRENTLY
        $this->add_control(
            'masonry',
            [
                'label' => __('Masonry', THEMESHARK_TXTDOMAIN),
                'type' => CM::HIDDEN,
                'label_off' => __('Off', THEMESHARK_TXTDOMAIN),
                'label_on' => __('On', THEMESHARK_TXTDOMAIN),
                'condition' => [
                    $this->get_control_id('columns!') => '1',
                    $this->get_control_id('thumbnail') => 'top',
                ],
                'render_type' => 'ui',
                'frontend_available' => true,
            ]
        );

        $this->add_group_control(
            Group_Control_Image_Size::get_type(),
            [
                'name' => 'thumbnail_size',
                'default' => 'medium',
                'exclude' => ['custom'],
                'condition' => [
                    $this->get_control_id('thumbnail!') => 'none',
                ],
                'prefix_class' => 'themeshark-posts--thumbnail-size-',
            ]
        );

        $this->add_responsive_control(
            'item_ratio',
            [
                'label' => __('Image Ratio', THEMESHARK_TXTDOMAIN),
                'type' => CM::SLIDER,
                'default' => [
                    'size' => 0.66,
                ],
                'tablet_default' => [
                    'size' => '',
                ],
                'mobile_default' => [
                    'size' => 0.5,
                ],
                'range' => [
                    'px' => [
                        'min' => 0.1,
                        'max' => 2,
                        'step' => 0.01,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .themeshark-posts-container .themeshark-post__thumbnail' => 'padding-bottom: calc( {{SIZE}} * 100% );',
                ],
                'condition' => [
                    $this->get_control_id('thumbnail!') => 'none',
                    // $this->get_control_id('masonry') => '',
                ],
            ]
        );

        $this->add_responsive_control(
            'image_width',
            [
                'label' => __('Image Width', THEMESHARK_TXTDOMAIN),
                'type' => CM::SLIDER,
                'range' => [
                    '%' => [
                        'min' => 10,
                        'max' => 100,
                    ],
                    'px' => [
                        'min' => 10,
                        'max' => 600,
                    ],
                ],
                'default' => [
                    'size' => 100,
                    'unit' => '%',
                ],
                'tablet_default' => [
                    'size' => '',
                    'unit' => '%',
                ],
                'mobile_default' => [
                    'size' => 100,
                    'unit' => '%',
                ],
                'size_units' => ['%', 'px'],
                'selectors' => [
                    '{{WRAPPER}} .themeshark-post__thumbnail__link' => 'width: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    $this->get_control_id('thumbnail!') => 'none',
                ],
            ]
        );
    }


    protected function register_date_badge_controls()
    {
        $SC = $this->shorthand_controls();

        $SC->control('show_date_badge', 'Date Badge', CM::SWITCHER, [
            'separator' => 'before',
            'label_on'  => $SC::_('Show'),
            'label_off' => $SC::_('Hide'),
            'default'   => 'yes',
            'separator' => 'before',
        ]);

        $SC->control('date_badge_type', 'Badge Type', CM::HIDDEN, [
            'condition' => [$this->get_control_id('show_date_badge') => 'yes'],
            'default'   => 'square',
        ]);

        $SC->responsive_control('date_badge_type_display', 'Badge Display', CM::SELECT, [
            'condition' => [$this->get_control_id('show_date_badge') => 'yes'],
            'default' => '',
            'options' => $SC::options_select(
                ['', 'Normal'],
                ['hidden',  'Hidden']
            ),
            'selectors_dictionary' => [
                'hidden' => 'display: none',
                '' => 'display:flex',
            ],
            'selectors' => $SC::selectors([
                '.themeshark-post__date-badge' => [
                    '{{VALUE}}'
                ]
            ])
        ]);
    }


    // COLUMNS
    //-----------------------------------------------

    protected function register_columns_controls()
    {
        $this->add_responsive_control(
            'columns',
            [
                'label' => __('Columns', THEMESHARK_TXTDOMAIN),
                'type' => CM::SELECT,
                'default' => '3',
                'tablet_default' => '2',
                'mobile_default' => '1',
                'options' => [
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                    '5' => '5',
                    '6' => '6',
                ],
                'prefix_class' => 'elementor-grid%s-',
                'frontend_available' => true,
            ]
        );
    }


    // POST COUNT
    //-----------------------------------------------

    protected function register_post_count_control()
    {
        $this->add_control(
            'posts_per_page',
            [
                'label' => __('Posts Per Page', THEMESHARK_TXTDOMAIN),
                'type' => CM::NUMBER,
                'default' => 6,
            ]
        );
    }


    // TITLE
    //-----------------------------------------------

    protected function register_title_controls()
    {
        $this->add_control(
            'show_title',
            [
                'label' => __('Title', THEMESHARK_TXTDOMAIN),
                'type' => CM::SWITCHER,
                'label_on' => __('Show', THEMESHARK_TXTDOMAIN),
                'label_off' => __('Hide', THEMESHARK_TXTDOMAIN),
                'default' => 'yes',
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'title_tag',
            [
                'label' => __('Title HTML Tag', THEMESHARK_TXTDOMAIN),
                'type' => CM::SELECT,
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
                'default' => 'h3',
                'condition' => [
                    $this->get_control_id('show_title') => 'yes',
                ],
            ]
        );
    }



    // EXCERPT
    //-----------------------------------------------

    protected function register_excerpt_controls()
    {
        $SC = $this->shorthand_controls();

        $SC->control('show_excerpt', 'Excerpt', CM::SWITCHER, [
            'label_on'  => $SC::_('Show'),
            'label_off' => $SC::_('Hide'),
            'default'   => 'yes',
        ]);

        $SC->control('excerpt_length', 'Excerpt Length', CM::NUMBER, [
            'condition' => [$this->get_control_id('show_excerpt') => 'yes'],
            'default'   => apply_filters('excerpt_length', 25),
        ]);

        $SC->control('excerpt_more_text', 'More Text', CM::SELECT, [
            'condition' => [$this->get_control_id('show_excerpt') => 'yes'],
            'options'   => $SC::options_select(
                ['',    'None'],
                ['...', 'Dots']
            )
        ]);

        $SC->responsive_control('excerpt_max_lines', 'Max Lines', CM::NUMBER, [
            'condition' => [$this->get_control_id('show_excerpt') => 'yes'],
            'min'       => 1,
            'selectors' => $SC::selectors([
                '.themeshark-post__excerpt' => [
                    '-webkit-line-clamp: {{VALUE}}'
                ]
            ])
        ]);
    }


    // READ MORE
    //-----------------------------------------------

    protected function register_read_more_controls()
    {
        $this->add_control(
            'show_read_more',
            [
                'label' => __('Read More', THEMESHARK_TXTDOMAIN),
                'type' => CM::SWITCHER,
                'label_on' => __('Show', THEMESHARK_TXTDOMAIN),
                'label_off' => __('Hide', THEMESHARK_TXTDOMAIN),
                'default' => 'yes',
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'read_more_text',
            [
                'label' => __('Read More Text', THEMESHARK_TXTDOMAIN),
                'type' => CM::TEXT,
                'default' => __('Read More »', THEMESHARK_TXTDOMAIN),
                'condition' => [
                    $this->get_control_id('show_read_more') => 'yes',
                ],
            ]
        );
    }

    // LINK CONTROLS
    //-----------------------------------------------

    protected function register_link_controls()
    {
        $this->add_control(
            'open_new_tab',
            [
                'label' => __('Open in new window', THEMESHARK_TXTDOMAIN),
                'type' => CM::SWITCHER,
                'label_on' => __('Yes', THEMESHARK_TXTDOMAIN),
                'label_off' => __('No', THEMESHARK_TXTDOMAIN),
                'default' => 'no',
                'render_type' => 'none',
            ]
        );
    }



    protected function get_optional_link_attributes_html()
    {

        $settings = $this->parent->get_settings();
        $new_tab_setting_key = $this->get_control_id('open_new_tab');
        $optional_attributes_html = 'yes' === $settings[$new_tab_setting_key] ? 'target="_blank"' : '';

        return $optional_attributes_html;
    }

    // META DATA
    //-----------------------------------------------

    protected function register_meta_data_controls()
    {
        $this->add_control(
            'meta_data',
            [
                'label' => __('Meta Data', THEMESHARK_TXTDOMAIN),
                'label_block' => true,
                'type' => CM::SELECT2,
                'default' => ['date', 'comments'],
                'multiple' => true,
                'options' => [
                    'author' => __('Author', THEMESHARK_TXTDOMAIN),
                    'date' => __('Date', THEMESHARK_TXTDOMAIN),
                    'time' => __('Time', THEMESHARK_TXTDOMAIN),
                    'comments' => __('Comments', THEMESHARK_TXTDOMAIN),
                    'modified' => __('Date Modified', THEMESHARK_TXTDOMAIN),
                ],
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'meta_separator',
            [
                'label' => __('Separator Between', THEMESHARK_TXTDOMAIN),
                'type' => CM::TEXT,
                'default' => '///',
                'selectors' => [
                    '{{WRAPPER}} .themeshark-post__meta-data span + span:before' => 'content: "{{VALUE}}"',
                ],
                'condition' => [
                    $this->get_control_id('meta_data!') => [],
                ],
            ]
        );
    }

    /**
     * Style Tab
     */
    protected function register_design_layout_controls()
    {
        $this->start_controls_section(
            'section_design_layout',
            [
                'label' => __('Layout', THEMESHARK_TXTDOMAIN),
                'tab' => CM::TAB_STYLE,
            ]
        );

        $this->add_control(
            'column_gap',
            [
                'label' => __('Columns Gap', THEMESHARK_TXTDOMAIN),
                'type' => CM::SLIDER,
                'default' => [
                    'size' => 30,
                ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--grid-column-gap: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->add_control(
            'row_gap',
            [
                'label' => __('Rows Gap', THEMESHARK_TXTDOMAIN),
                'type' => CM::SLIDER,
                'default' => [
                    'size' => 35,
                ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'frontend_available' => true,
                'selectors' => [
                    '{{WRAPPER}}' => '--grid-row-gap: {{SIZE}}{{UNIT}}',
                ],
            ]
        );



        $this->add_control(
            'alignment',
            [
                'label' => __('Alignment', THEMESHARK_TXTDOMAIN),
                'type' => CM::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __('Left', THEMESHARK_TXTDOMAIN),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => __('Center', THEMESHARK_TXTDOMAIN),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => __('Right', THEMESHARK_TXTDOMAIN),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'prefix_class' => 'themeshark-posts--align-',
            ]
        );

        $this->end_controls_section();
    }

    protected function register_design_image_controls()
    {
        $this->start_controls_section(
            'section_design_image',
            [
                'label' => __('Image', THEMESHARK_TXTDOMAIN),
                'tab' => CM::TAB_STYLE,
                'condition' => [
                    $this->get_control_id('thumbnail!') => 'none',
                ],
            ]
        );

        $this->add_control(
            'img_border_radius',
            [
                'label' => __('Border Radius', THEMESHARK_TXTDOMAIN),
                'type' => CM::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .themeshark-post__thumbnail' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    $this->get_control_id('thumbnail!') => 'none',
                ],
            ]
        );

        $this->add_control(
            'image_spacing',
            [
                'label' => __('Spacing', THEMESHARK_TXTDOMAIN),
                'type' => CM::SLIDER,
                'range' => [
                    'px' => [
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}}.themeshark-posts--thumbnail-left .themeshark-post__thumbnail__link' => 'margin-right: {{SIZE}}{{UNIT}}',
                    '{{WRAPPER}}.themeshark-posts--thumbnail-right .themeshark-post__thumbnail__link' => 'margin-left: {{SIZE}}{{UNIT}}',
                    '{{WRAPPER}}.themeshark-posts--thumbnail-top .themeshark-post__thumbnail__link' => 'margin-bottom: {{SIZE}}{{UNIT}}',
                ],
                'default' => [
                    'size' => 20,
                ],
                'condition' => [
                    $this->get_control_id('thumbnail!') => 'none',
                ],
            ]
        );

        $this->start_controls_tabs('thumbnail_effects_tabs');

        $this->start_controls_tab(
            'normal',
            [
                'label' => __('Normal', THEMESHARK_TXTDOMAIN),
            ]
        );

        $this->add_group_control(
            Group_Control_Css_Filter::get_type(),
            [
                'name' => 'thumbnail_filters',
                'selector' => '{{WRAPPER}} .themeshark-post__thumbnail img',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'hover',
            [
                'label' => __('Hover', THEMESHARK_TXTDOMAIN),
            ]
        );

        $this->add_group_control(
            Group_Control_Css_Filter::get_type(),
            [
                'name' => 'thumbnail_hover_filters',
                'selector' => '{{WRAPPER}} .themeshark-post:hover .themeshark-post__thumbnail img',
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();
    }

    protected function register_design_content_controls()
    {
        $this->start_controls_section(
            'section_design_content',
            [
                'label' => __('Content', THEMESHARK_TXTDOMAIN),
                'tab' => CM::TAB_STYLE,
            ]
        );

        $this->add_control(
            'heading_title_style',
            [
                'label' => __('Title', THEMESHARK_TXTDOMAIN),
                'type' => CM::HEADING,
                'condition' => [
                    $this->get_control_id('show_title') => 'yes',
                ],
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => __('Color', THEMESHARK_TXTDOMAIN),
                'type' => CM::COLOR,
                'global' => [
                    'default' => Global_Colors::COLOR_SECONDARY,
                ],
                'selectors' => [
                    '{{WRAPPER}} .themeshark-post__title, {{WRAPPER}} .themeshark-post__title a' => 'color: {{VALUE}};',
                ],
                'condition' => [
                    $this->get_control_id('show_title') => 'yes',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'global' => [
                    'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
                ],
                'selector' => '{{WRAPPER}} .themeshark-post__title, {{WRAPPER}} .themeshark-post__title a',
                'condition' => [
                    $this->get_control_id('show_title') => 'yes',
                ],
            ]
        );

        $this->add_control(
            'title_spacing',
            [
                'label' => __('Spacing', THEMESHARK_TXTDOMAIN),
                'type' => CM::SLIDER,
                'range' => [
                    'px' => [
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .themeshark-post__title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    $this->get_control_id('show_title') => 'yes',
                ],
            ]
        );

        $this->add_control(
            'heading_meta_style',
            [
                'label' => __('Meta', THEMESHARK_TXTDOMAIN),
                'type' => CM::HEADING,
                'separator' => 'before',
                'condition' => [
                    $this->get_control_id('meta_data!') => [],
                ],
            ]
        );

        $this->add_control(
            'meta_color',
            [
                'label' => __('Color', THEMESHARK_TXTDOMAIN),
                'type' => CM::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .themeshark-post__meta-data' => 'color: {{VALUE}};',
                ],
                'condition' => [
                    $this->get_control_id('meta_data!') => [],
                ],
            ]
        );

        $this->add_control(
            'meta_separator_color',
            [
                'label' => __('Separator Color', THEMESHARK_TXTDOMAIN),
                'type' => CM::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .themeshark-post__meta-data span:before' => 'color: {{VALUE}};',
                ],
                'condition' => [
                    $this->get_control_id('meta_data!') => [],
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'meta_typography',
                'global' => [
                    'default' => Global_Typography::TYPOGRAPHY_SECONDARY,
                ],
                'selector' => '{{WRAPPER}} .themeshark-post__meta-data',
                'condition' => [
                    $this->get_control_id('meta_data!') => [],
                ],
            ]
        );

        $this->add_control(
            'meta_spacing',
            [
                'label' => __('Spacing', THEMESHARK_TXTDOMAIN),
                'type' => CM::SLIDER,
                'range' => [
                    'px' => [
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .themeshark-post__meta-data' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    $this->get_control_id('meta_data!') => [],
                ],
            ]
        );

        $this->add_control(
            'heading_excerpt_style',
            [
                'label' => __('Excerpt', THEMESHARK_TXTDOMAIN),
                'type' => CM::HEADING,
                'separator' => 'before',
                'condition' => [
                    $this->get_control_id('show_excerpt') => 'yes',
                ],
            ]
        );

        $this->add_control(
            'excerpt_color',
            [
                'label' => __('Color', THEMESHARK_TXTDOMAIN),
                'type' => CM::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .themeshark-post__excerpt' => 'color: {{VALUE}};',
                ],
                'condition' => [
                    $this->get_control_id('show_excerpt') => 'yes',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'excerpt_typography',
                'global' => [
                    'default' => Global_Typography::TYPOGRAPHY_TEXT,
                ],
                'selector' => '{{WRAPPER}} .themeshark-post__excerpt',
                'condition' => [
                    $this->get_control_id('show_excerpt') => 'yes',
                ],
            ]
        );

        $this->add_control(
            'excerpt_spacing',
            [
                'label' => __('Spacing', THEMESHARK_TXTDOMAIN),
                'type' => CM::SLIDER,
                'range' => [
                    'px' => [
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .themeshark-post__excerpt' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    $this->get_control_id('show_excerpt') => 'yes',
                ],
            ]
        );

        $this->add_control(
            'heading_readmore_style',
            [
                'label' => __('Read More', THEMESHARK_TXTDOMAIN),
                'type' => CM::HEADING,
                'separator' => 'before',
                'condition' => [
                    $this->get_control_id('show_read_more') => 'yes',
                ],
            ]
        );

        $this->add_control(
            'read_more_color',
            [
                'label' => __('Color', THEMESHARK_TXTDOMAIN),
                'type' => CM::COLOR,
                'global' => [
                    'default' => Global_Colors::COLOR_ACCENT,
                ],
                'selectors' => [
                    '{{WRAPPER}} .themeshark-post__read-more' => 'color: {{VALUE}};',
                ],
                'condition' => [
                    $this->get_control_id('show_read_more') => 'yes',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'read_more_typography',
                'selector' => '{{WRAPPER}} .themeshark-post__read-more',
                'global' => [
                    'default' => Global_Typography::TYPOGRAPHY_ACCENT,
                ],
                'condition' => [
                    $this->get_control_id('show_read_more') => 'yes',
                ],
            ]
        );

        $this->add_control(
            'read_more_spacing',
            [
                'label' => __('Spacing', THEMESHARK_TXTDOMAIN),
                'type' => CM::SLIDER,
                'range' => [
                    'px' => [
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .themeshark-post__text' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    $this->get_control_id('show_read_more') => 'yes',
                ],
            ]
        );

        $SC = $this->shorthand_controls();






        $this->add_control(
            'heading_date_badge_style',
            [
                'label' => __('Badge', THEMESHARK_TXTDOMAIN),
                'type' => CM::HEADING,
                'separator' => 'before',
                'condition' => [
                    $this->get_control_id('show_date_badge') => 'yes',
                ],
            ]
        );


        $badge_size = $this->get_control_id('date_badge_size');
        $this->add_control(
            'date_badge_position',
            [
                'label' => 'Badge Position',
                'type' => CM::CHOOSE,
                'options' => $SC::choice_set_h_align(['left', 'right']),
                'default' => 'left',
                'selectors' => $SC::selectors([
                    '.themeshark-post__date-badge' => [
                        'top: calc({{' . $badge_size . '.SIZE || 75}}{{' . $badge_size . '.UNIT || px}} / -2);',
                        '{{VALUE}}: calc({{' . $badge_size . '.SIZE || 75}}{{' . $badge_size . '.UNIT || px}}/ -2);'
                    ]
                ]),
                'condition' => [
                    $this->get_control_id('show_date_badge') => 'yes',
                ],
            ]
        );

        $this->add_control(
            'date_badge_bg_color',
            [
                'label' => __('Background Color', THEMESHARK_TXTDOMAIN),
                'type' => CM::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .themeshark-post__date-badge' => 'background-color: {{VALUE}};',
                ],
                'global' => [
                    'default' => Global_Colors::COLOR_ACCENT,
                ],
                'condition' => [
                    $this->get_control_id('show_date_badge') => 'yes',
                ],
            ]
        );

        $this->add_control(
            'date_badge_color',
            [
                'label' => __('Text Color', THEMESHARK_TXTDOMAIN),
                'type' => CM::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .themeshark-post__date-badge' => 'color: {{VALUE}};',
                ],
                'condition' => [
                    $this->get_control_id('show_date_badge') => 'yes',
                ],
            ]
        );

        $this->add_control(
            'date_badge_radius',
            [
                'label' => __('Border Radius', THEMESHARK_TXTDOMAIN),
                'type' => CM::SLIDER,
                'range' => [
                    'px' => [
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .themeshark-post__date-badge' => 'border-radius: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    $this->get_control_id('show_date_badge') => 'yes',
                ],
            ]
        );

        $this->add_responsive_control(
            'date_badge_size',
            [
                'label' => __('Size', THEMESHARK_TXTDOMAIN),
                'type' => CM::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 50,
                        'max' => 250,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .themeshark-post__date-badge' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    $this->get_control_id('show_date_badge') => 'yes',
                ],
            ]
        );

        $this->add_responsive_control(
            'date_badge_margin',
            [
                'label' => __('Margin', THEMESHARK_TXTDOMAIN),
                'type' => CM::SLIDER,
                'range' => [
                    'px' => [
                        'max' => 50,
                    ],
                ],
                'default' => [
                    'size' => 20,
                ],
                'selectors' => [
                    '{{WRAPPER}} .themeshark-post__date-badge' => 'margin: {{SIZE}}{{UNIT}}',
                ],
                'condition' => [
                    $this->get_control_id('show_date_badge') => 'yes',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'date_badge_day_typography',
                'label' => $SC::_('Day Typography'),
                'selector' => '{{WRAPPER}} .themeshark-post__date-badge__day',
                'condition' => [
                    $this->get_control_id('show_date_badge') => 'yes',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'date_badge_month_typography',
                'label' => $SC::_('Month Typography'),
                'selector' => '{{WRAPPER}} .themeshark-post__date-badge__month',
                'condition' => [
                    $this->get_control_id('show_date_badge') => 'yes',
                ],
            ]
        );



        $this->end_controls_section();
    }

    public function render()
    {

        $this->parent->query_posts();


        /** @var \WP_Query $query */
        $query = $this->parent->get_query();


        if (!$query->found_posts) {
            return;
        }

        $this->render_loop_header();

        // It's the global `wp_query` it self. and the loop was started from the theme.
        if ($query->in_the_loop) {
            $this->current_permalink = get_permalink();
            $this->render_post();
        } else {
            while ($query->have_posts()) {
                $query->the_post();

                $this->current_permalink = get_permalink();
                $this->render_post();
            }
        }

        wp_reset_postdata();

        $this->render_loop_footer();
    }

    public function filter_excerpt_length()
    {
        return $this->get_instance_value('excerpt_length');
    }

    public function filter_excerpt_more($more)
    {
        return '';
    }

    public function get_container_class()
    {
        return 'themeshark-posts--skin-' . $this->get_id();
    }

    protected function render_thumbnail()
    {
        $thumbnail = $this->get_instance_value('thumbnail');

        if ('none' === $thumbnail && !Plugin::elementor()->editor->is_edit_mode()) {
            return;
        }

        $settings = $this->parent->get_settings();
        $setting_key = $this->get_control_id('thumbnail_size');
        $settings[$setting_key] = [
            'id' => get_post_thumbnail_id(),
        ];
        $thumbnail_html = Group_Control_Image_Size::get_attachment_image_html($settings, $setting_key);

        if (empty($thumbnail_html)) {
            return;
        }

        $optional_attributes_html = $this->get_optional_link_attributes_html();

?>
        <a class="themeshark-post__thumbnail__link" href="<?php echo esc_url($this->current_permalink); ?>" <?php echo $optional_attributes_html; ?>>
            <div class="themeshark-post__thumbnail"><?php echo $thumbnail_html; ?></div>
        </a>
    <?php
    }

    protected function render_title()
    {
        if (!$this->get_instance_value('show_title')) {
            return;
        }

        $optional_attributes_html = $this->get_optional_link_attributes_html();

        $tag = $this->get_instance_value('title_tag');
    ?>
        <<?php echo Utils::validate_html_tag($tag); ?> class="themeshark-post__title">
            <a href="<?php echo esc_url($this->current_permalink); ?>" <?php echo $optional_attributes_html; ?>>
                <?php the_title(); ?>
            </a>
        </<?php echo Utils::validate_html_tag($tag); ?>>
    <?php
    }

    protected function render_excerpt()
    {
        if (!$this->get_instance_value('show_excerpt'))  return;

        $excerpt         = get_the_excerpt();
        $num_words       = $this->get_instance_value('excerpt_length');
        $more            = $this->get_instance_value('excerpt_more_text');
        $trimmed_excerpt = wp_trim_words($excerpt, $num_words, $more); ?>

        <div class="themeshark-post__excerpt">
            <?php echo $trimmed_excerpt; ?>
        </div>

    <?php
    }

    protected function render_read_more()
    {
        if (!$this->get_instance_value('show_read_more')) {
            return;
        }

        $optional_attributes_html = $this->get_optional_link_attributes_html();

    ?>
        <a class="themeshark-post__read-more" href="<?php echo $this->current_permalink; ?>" <?php echo $optional_attributes_html; ?>>
            <?php esc_html_e($this->get_instance_value('read_more_text')); ?>
        </a>
    <?php
    }


    protected function render_date_badge()
    {
        if (empty($this->get_instance_value('show_date_badge'))) return;
        $day   = get_the_date('d');
        $month = get_the_date('M');
    ?>
        <div class='themeshark-post__date-badge'>
            <div class='themeshark-post__date-badge__day'><?php esc_html_e($day); ?></div>
            <div class='themeshark-post__date-badge__month'><?php esc_html_e($month); ?></div>
        </div>
    <?php
    }

    protected function render_post_header()
    {
    ?>
        <article <?php post_class(['themeshark-post themeshark-grid-item']); ?>>
        <?php
    }

    protected function render_post_footer()
    {
        ?>
        </article>
    <?php
    }

    protected function render_text_header()
    {
    ?>
        <div class="themeshark-post__text">
        <?php
    }

    protected function render_text_footer()
    {
        ?>
        </div>
    <?php
    }

    protected function render_loop_header()
    {
        $classes = [
            'themeshark-posts-container',
            'themeshark-posts',
            'elementor-has-item-ratio',
            $this->get_container_class(),
        ];

        /** @var \WP_Query $wp_query */
        $wp_query = $this->parent->get_query();

        // Use grid only if found posts.
        if ($wp_query->found_posts) {
            $classes[] = 'elementor-grid';
        }

        $this->parent->add_render_attribute('container', [
            'class' => $classes,
        ]);

    ?>
        <div <?php echo $this->parent->get_render_attribute_string('container'); ?>>
        <?php
    }

    protected function render_loop_footer()
    {
        ?>
        </div>
        <?php



        $parent_settings = $this->parent->get_settings();
        if ('' === $parent_settings['pagination_type']) {
            return;
        }


        $page_limit = $this->parent->get_query()->max_num_pages;
        if ('' !== $parent_settings['pagination_page_limit']) {
            $page_limit = min($parent_settings['pagination_page_limit'], $page_limit);
        }

        if (2 > $page_limit) {
            return;
        }

        $this->parent->add_render_attribute('pagination', 'class', 'themeshark-pagination');

        $has_numbers = in_array($parent_settings['pagination_type'], ['numbers', 'numbers_and_prev_next']);
        $has_prev_next = in_array($parent_settings['pagination_type'], ['prev_next', 'numbers_and_prev_next']);

        $links = [];

        if ($has_numbers) {
            $paginate_args = [
                'type' => 'array',
                'current' => $this->parent->get_current_page(),
                'total' => $page_limit,
                'prev_next' => false,
                'show_all' => 'yes' !== $parent_settings['pagination_numbers_shorten'],
                'before_page_number' => '<span class="elementor-screen-only">' . __('Page', THEMESHARK_TXTDOMAIN) . '</span>',
            ];

            if (is_singular() && !is_front_page()) {
                global $wp_rewrite;
                if ($wp_rewrite->using_permalinks()) {
                    $paginate_args['base'] = trailingslashit(get_permalink()) . '%_%';
                    $paginate_args['format'] = user_trailingslashit('%#%', 'single_paged');
                } else {
                    $paginate_args['format'] = '?page=%#%';
                }
            }

            $links = paginate_links($paginate_args);
        }

        if ($has_prev_next) {
            $prev_next = $this->parent->get_posts_nav_link($page_limit);
            array_unshift($links, $prev_next['prev']);
            $links[] = $prev_next['next'];
        }

        ?>
        <nav class="themeshark-pagination" role="navigation" aria-label="<?php esc_attr_e('Pagination', THEMESHARK_TXTDOMAIN); ?>">
            <?php echo implode(PHP_EOL, $links); ?>
        </nav>
    <?php
    }

    protected function render_meta_data()
    {
        /** @var array $settings e.g. [ 'author', 'date', ... ] */
        $settings = $this->get_instance_value('meta_data');
        if (empty($settings)) {
            return;
        }
    ?>
        <div class="themeshark-post__meta-data">
            <?php
            if (in_array('author', $settings)) {
                $this->render_author();
            }

            if (in_array('date', $settings)) {
                $this->render_date_by_type();
            }

            if (in_array('time', $settings)) {
                $this->render_time();
            }

            if (in_array('comments', $settings)) {
                $this->render_comments();
            }
            if (in_array('modified', $settings)) {
                $this->render_date_by_type('modified');
            }
            ?>
        </div>
    <?php
    }

    protected function render_author()
    {
    ?>
        <span class="themeshark-post-author">
            <?php the_author(); ?>
        </span>
    <?php
    }

    /**
     * @deprecated since 3.0.0 Use `Skin_Base::render_date_by_type()` instead
     */
    protected function render_date()
    {
        // _deprecated_function( __METHOD__, '3.0.0', 'Skin_Base::render_date_by_type()' );
        $this->render_date_by_type();
    }

    protected function render_date_by_type($type = 'publish')
    {
    ?>
        <span class="themeshark-post-date">
            <?php
            switch ($type):
                case 'modified':
                    $date = get_the_modified_date();
                    break;
                default:
                    $date = get_the_date();
            endswitch;
            /** This filter is documented in wp-includes/general-template.php */
            echo apply_filters('the_date', $date, get_option('date_format'), '', '');
            ?>
        </span>
    <?php
    }

    protected function render_time()
    {
    ?>
        <span class="themeshark-post-time">
            <?php the_time(); ?>
        </span>
    <?php
    }

    protected function render_comments()
    {
    ?>
        <span class="themeshark-post-avatar">
            <?php comments_number(); ?>
        </span>
<?php
    }

    protected function render_post()
    {
        $this->render_post_header();
        $this->render_date_badge();
        $this->render_thumbnail();
        $this->render_text_header();
        $this->render_title();
        $this->render_meta_data();
        $this->render_excerpt();
        $this->render_read_more();
        $this->render_text_footer();
        $this->render_post_footer();
    }

    public function render_amp()
    {
    }
}
