<?php

namespace Themeshark_Elementor\Widgets;

if (!defined('ABSPATH')) exit;

use \Elementor\Utils;
use \Elementor\Repeater;
use \Elementor\Controls_Manager as CM;
use \Elementor\Group_Control_Typography;
use \Themeshark_Elementor\Inc\Globals_Fixer;
use \Themeshark_Elementor\Inc\Shorthand_Controls;
use \Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use \Themeshark_Elementor\Controls\Controls_Handler as CH;
use \Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use \Themeshark_Elementor\Inc\TS_Widget;
use Themeshark_Elementor\Inc\Helpers;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}


/**
 * ThemeShark Timeline
 *
 * Creates timeline with animated line
 * 
 * @since 1.0.0
 */
class TS_Timeline extends TS_Widget
{

    const NAME = 'ts-timeline';
    const TITLE = 'Timeline';

    public static function register_styles()
    {
        self::widget_style('ts-timeline', self::get_dir_url(__DIR__, 'ts-timeline.css'));
    }

    public static function register_scripts()
    {
        self::widget_script('ts-timeline', self::get_dir_url(__DIR__, 'ts-timeline.js'));
    }

    public function get_icon()
    {
        return 'tsicon-timeline';
    }

    public function get_keywords()
    {
        return self::keywords(['timeline', 'line', 'grow']);
    }

    public function get_style_depends()
    {
        return ['ts-timeline'];
    }

    public function get_script_depends()
    {
        return ['ts-timeline'];
    }

    public function get_categories()
    {
        return ['themeshark'];
    }

    protected function add_repeater_controls(Repeater $repeater)
    {
        $SCR = new Shorthand_Controls($repeater);
        $SCR->control('title', 'Title', CM::TEXT, [
            'render_type' => 'ui',
            'default' => 'Add Text Here',
            'themeshark_settings' => [CH::LINK_TEXT => [
                'selector' => '{{CURRENT_ITEM}} .tl-item-title'
            ]]
        ]);

        $SCR->control('content', 'Content', CM::TEXTAREA, [
            'render_type' => 'ui',
            'default' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.',
            'themeshark_settings' => [CH::LINK_TEXT => [
                'selector' => '{{CURRENT_ITEM}} .tl-item-text'
            ]]
        ]);

        $SCR->control('image', 'Image', CM::MEDIA, [
            'render_type' => 'ui',
            'default' => ['url' => Utils::get_placeholder_image_src()],
            'themeshark_settings' => [CH::LINK_ATTRIBUTE => [
                'selector' => '{{CURRENT_ITEM}} img.tl-image',
                'attribute' => 'src',
                'value' => '{{URL}}'
            ]],
        ]);
    }

    protected function get_repeater_defaults()
    {
        $defaults = [
            'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.',
            'image' => ['url' => Utils::get_placeholder_image_src()],
        ];

        return [
            array_merge($defaults, ['title' => 'First Item']),
            array_merge($defaults, ['title' => 'Second Item']),
            array_merge($defaults, ['title' => 'Third Item']),
        ];
    }

    public function register_controls()
    {

        $SC = new Shorthand_Controls($this);

        $this->start_controls_section('section_slides', [
            'label' => $SC::_('Items'),
            'tab' => CM::TAB_CONTENT,
        ]);

        $repeater = new Repeater();
        $this->add_repeater_controls($repeater);
        $SC->control('slides', 'Items', CM::REPEATER, [
            'fields' => $repeater->get_controls(),
            'default' => $this->get_repeater_defaults(),
            'separator' => 'after',
            'title_field' => '{{{ title }}}',
            'themeshark_settings' => [CH::NEW_SLIDES_ADD_HANDLERS => true],
            'render_type' => 'template'
        ]);

        $this->end_controls_section();

        $this->start_controls_section(
            'section_layout',
            [
                'label' => __('Layout', THEMESHARK_TXTDOMAIN),
                'tab' => CM::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'fade_columns',
            [
                'label' => __('Fade Columns', THEMESHARK_TXTDOMAIN),
                'type' => CM::SWITCHER,
                'default' => 'yes',
                'label_off' => __('Off', THEMESHARK_TXTDOMAIN),
                'label_on' => __('On', THEMESHARK_TXTDOMAIN),
                'return_value' => 'yes',
            ]
        );

        $this->add_responsive_control(
            'item_height',
            [
                'label' => __('Item Height', THEMESHARK_TXTDOMAIN),
                'type' => CM::SLIDER,
                'default' => [
                    'size' => 200,
                ],
                'range' => [
                    'px' => [
                        'min' => 80,
                        'max' => 500,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--tl-item-height: {{SIZE}}{{UNIT}};'
                ],
            ]
        );

        $this->add_responsive_control(
            'image_height_ratio',
            [
                'label' => __('Image Height Ratio', THEMESHARK_TXTDOMAIN),
                'type' => CM::SLIDER,
                'default' => [
                    'size' => 0.8,
                ],
                'range' => [
                    'px' => [
                        'min' => 0.01,
                        'max' => 1,
                        'step' => 0.01
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--tl-item-image-height-ratio: {{SIZE}};'
                ],
            ]
        );


        $this->add_control(
            'heading_line_offset',
            [
                'label' => __('Line Offset', THEMESHARK_TXTDOMAIN),
                'type' => CM::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'line_offset_top',
            [
                'label' => __('Top', THEMESHARK_TXTDOMAIN),
                'type' => CM::SLIDER,
                'default' => [
                    'size' => 20,
                ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 300,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .timeline-items' => 'padding-top: {{SIZE}}px;'
                ],
            ]
        );

        $this->add_responsive_control(
            'line_offset_bottom',
            [
                'label' => __('Bottom', THEMESHARK_TXTDOMAIN),
                'type' => CM::SLIDER,
                'default' => [
                    'size' => 20,
                ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 300,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .timeline-items' => 'padding-bottom: {{SIZE}}px;'
                ],
            ]
        );

        $this->add_responsive_control(
            'vert_line_margin',
            [
                'label' => __('Inside', THEMESHARK_TXTDOMAIN),
                'type' => CM::SLIDER,
                'default' => [
                    'size' => 20,
                ],
                'range' => [
                    '%' => [
                        'min' => 1,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--tl-vline-margin: {{SIZE}}%;'
                ],
            ]
        );

        $this->add_control(
            'heading_line_scroll',
            [
                'label' => __('Line Scroll Settings', THEMESHARK_TXTDOMAIN),
                'type' => CM::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'line_speed',
            [
                'label' => __('Speed', THEMESHARK_TXTDOMAIN),
                'type' => CM::SELECT,
                'default' => '1.2',
                'options' => [
                    '0.6' => __('Far Behind', THEMESHARK_TXTDOMAIN),
                    '0.8' => __('Behind', THEMESHARK_TXTDOMAIN),
                    '1.0' => __('Match Scroll', THEMESHARK_TXTDOMAIN),
                    '1.2' => __('Ahead', THEMESHARK_TXTDOMAIN),
                    '1.4' => __('Far Ahead', THEMESHARK_TXTDOMAIN),
                ],
            ]
        );

        $this->add_control(
            'scroll_offset',
            [
                'label' => __('Starting Offset', THEMESHARK_TXTDOMAIN),
                'type' => CM::SLIDER,
                'default' => [
                    'size' => 55,
                    'unit' => '%'
                ],
                'range' => [
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'line_styles',
            [
                'label' => __('Line', THEMESHARK_TXTDOMAIN),
                'tab' => CM::TAB_STYLE
            ]
        );

        $this->add_control(
            'rounded_edges',
            [
                'label' => __('Rounded Line Ends', THEMESHARK_TXTDOMAIN),
                'type' => CM::SWITCHER,
                'label_off' => __('Off', THEMESHARK_TXTDOMAIN),
                'label_on' => __('On', THEMESHARK_TXTDOMAIN),
                'return_value' => 'yes',
                'selectors' => [
                    '{{WRAPPER}} .vert-line-back, {{WRAPPER}} .hor-line-back' => 'border-radius: calc(var(--tl-vline-width-back) / 2);',
                    '{{WRAPPER}} .vert-line-front, {{WRAPPER}} .hor-line-front' => 'border-radius: calc(var(--tl-vline-width-front) / 2);'
                ]
            ]
        );

        $this->add_control(
            'heading_line_back',
            [
                'label' => __('Line Back', THEMESHARK_TXTDOMAIN),
                'type' => CM::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'line_color_back',
            [
                'label' => __('Color', THEMESHARK_TXTDOMAIN),
                'type' => CM::COLOR,
                'default' => '#d8d8d8',
                'selectors' => [
                    '{{WRAPPER}}' => '--tl-vline-color-back: {{VALUE}}',
                ]
            ]
        );

        $this->add_responsive_control(
            'line_width_back',
            [
                'label' => __('Width', THEMESHARK_TXTDOMAIN),
                'type' => CM::SLIDER,
                'default' => [
                    'size' => 7,
                    'unit' => 'px'
                ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 20,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--tl-vline-width-back: {{SIZE}}{{UNIT}};'
                ],
            ]
        );

        $this->add_responsive_control(
            'circle_diameter_back',
            [
                'label' => __('Circle Diameter', THEMESHARK_TXTDOMAIN),
                'type' => CM::SLIDER,
                'default' => [
                    'size' => 30,
                    'unit' => 'px'
                ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--tl-item-circle-diam: {{SIZE}}{{UNIT}};'
                ],
            ]
        );

        $this->add_control(
            'heading_line_front',
            [
                'label' => __('Line Front', THEMESHARK_TXTDOMAIN),
                'type' => CM::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'line_color_front',
            [
                'label' => __('Color', THEMESHARK_TXTDOMAIN),
                'type' => CM::COLOR,
                'global' => ['default' => GLobal_Colors::COLOR_ACCENT],
                Globals_Fixer::FIX => true,
                'selectors' => [
                    '{{WRAPPER}}' => '--tl-vline-color-front: {{VALUE}}',
                ]
            ]
        );

        $this->add_responsive_control(
            'line_width_front',
            [
                'label' => __('Width', THEMESHARK_TXTDOMAIN),
                'type' => CM::SLIDER,
                'default' => [
                    'size' => 7,
                    'unit' => 'px'
                ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 20,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--tl-vline-width-front: {{SIZE}}{{UNIT}};'
                ],
            ]
        );

        $this->add_responsive_control(
            'circle_diameter_front',
            [
                'label' => __('Circle Diameter', THEMESHARK_TXTDOMAIN),
                'type' => CM::SLIDER,
                'default' => [
                    'size' => 30,
                    'unit' => 'px'
                ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--tl-item-circle-diam-front: {{SIZE}}{{UNIT}};'
                ],
            ]
        );

        $this->add_control(
            'heading_horizontal_lines',
            [
                'label' => __('Horizontal Lines', THEMESHARK_TXTDOMAIN),
                'type' => CM::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'hor_line_width',
            [
                'label' => __('Width', THEMESHARK_TXTDOMAIN),
                'type' => CM::SLIDER,
                'default' => [
                    'size' => 80,
                ],
                'range' => [
                    '%' => [
                        'min' => 1,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--tl-item-hline-width: {{SIZE}}%;'
                ],
            ]
        );

        $this->add_responsive_control(
            'hor_line_offset',
            [
                'label' => __('Offset Top', THEMESHARK_TXTDOMAIN),
                'type' => CM::SLIDER,
                'default' => [
                    'size' => 80,
                    'unit' => 'px'
                ],
                'range' => [
                    '%' => [
                        'min' => 1,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .tl-item-bar-cont' => 'top: {{SIZE}}{{UNIT}};'
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'content_styles',
            [
                'label' => __('Content', THEMESHARK_TXTDOMAIN),
                'tab' => CM::TAB_STYLE
            ]
        );

        $this->add_control(
            'heading_title',
            [
                'label' => __('Title', THEMESHARK_TXTDOMAIN),
                'type' => CM::HEADING,
            ]
        );

        $this->add_responsive_control(
            'title_space_top',
            [
                'label' => __('Spacing', THEMESHARK_TXTDOMAIN),
                'type' => CM::SLIDER,
                'default' => [
                    'size' => 20,
                ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 80,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .tl-item-title' => 'margin-top: {{SIZE}}px;'
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'label' => __('Typography', THEMESHARK_TXTDOMAIN),
                'selector' => '{{WRAPPER}} .tl-item-title',
                'global' => ['default' => Global_Typography::TYPOGRAPHY_PRIMARY],
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => __('Color', THEMESHARK_TXTDOMAIN),
                'type' => CM::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tl-item-title' => 'color: {{VALUE}}',
                ],
                'global' => ['default' => Global_Colors::COLOR_PRIMARY],
                Globals_Fixer::FIX => true,
            ]
        );

        $this->add_control(
            'heading_content',
            [
                'label' => __('Content', THEMESHARK_TXTDOMAIN),
                'type' => CM::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'title_space_bottom',
            [
                'label' => __('Spacing Top', THEMESHARK_TXTDOMAIN),
                'type' => CM::SLIDER,
                'default' => [
                    'size' => 20,
                ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 80,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .tl-item-title' => 'margin-bottom: {{SIZE}}px;'
                ],
            ]
        );

        $this->add_responsive_control(
            'content_space_bottom',
            [
                'label' => __('Spacing Bottom', THEMESHARK_TXTDOMAIN),
                'type' => CM::SLIDER,
                'default' => [
                    'size' => 20,
                ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 80,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .tl-item-text' => 'margin-bottom: {{SIZE}}px;'
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'content_typography',
                'label' => __('Typography', THEMESHARK_TXTDOMAIN),
                'selector' => '{{WRAPPER}} .tl-item-text',
                'global' => ['default' => Global_Typography::TYPOGRAPHY_TEXT],
            ]
        );

        $this->add_control(
            'content_color',
            [
                'label' => __('Color', THEMESHARK_TXTDOMAIN),
                'type' => CM::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .tl-item-text' => 'color: {{VALUE}}',
                ],
                'global' => ['default' => Global_Colors::COLOR_TEXT],
                Globals_Fixer::FIX => true,
            ]
        );

        $this->end_controls_section();
    }


    private function txt_container(array $slide, array $settings, $direction)
    { ?>
        <div class="tl-item-content-<?php esc_attr_e($direction); ?> tl-text-col tl-col">
            <div class="tl-content-inner">
                <h3 class="tl-item-title"><?php esc_html_e($slide['title']); ?></h3>
                <p class="tl-item-text">
                    <?php echo Helpers::esc_wysiwyg($slide['content']); ?>
                </p>
            </div>
        </div>
    <?php
    }

    private function img_container(array $slide, array $settings, $direction)
    { ?>
        <div class="tl-item-content-<?php esc_attr_e($direction); ?> tl-image-col tl-col">
            <div class="tl-content-inner">
                <div class="tl-image-box">
                    <img class="tl-image" src="<?php echo esc_url($slide['image']['url']); ?>" />
                </div>
            </div>
        </div>
    <?php
    }

    protected function print_slide(array $slide, array $settings, $index)
    {
        $img_direction = $index % 2 === 0 ? 'left' : 'right';
        $txt_direction = $index % 2 === 0 ? 'right' : 'left';

        $slide_id = $slide['_id'];
        $element_key = "slide-$index";
        $item_render_key = "item_$slide_id";

        $this->add_render_attribute($item_render_key, [
            'class' => [
                'timeline-item',
                "tl-bar-$img_direction",
                'elementor-repeater-item-' . $slide_id
            ],
            'data-item' => $element_key
        ]);

    ?>
        <div <?php $this->print_render_attribute_string($item_render_key); ?>>

            <!-- horizontal timeline bar -->
            <div class="tl-item-bar-cont">
                <div class="circle-back"></div>
                <div class="circle-front"></div>
                <div class="hor-line-back"></div>
                <div class="hor-line-front"></div>
            </div>

            <div class="tl-item-content">
                <?php if ($img_direction === 'left') {
                    $this->img_container($slide, $settings, $img_direction);
                    $this->txt_container($slide, $settings, $txt_direction);
                } else {
                    $this->txt_container($slide, $settings, $txt_direction);
                    $this->img_container($slide, $settings, $img_direction);
                } ?>
            </div>
        </div>

    <?php
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $slides = $settings['slides'];

        $this->add_render_attribute('timeline', [
            'class' => 'themeshark-timeline',
            'data-speed' => $settings['line_speed'],
            'data-scrolloffset' => $settings['scroll_offset']['size']
        ]);

        if ($settings['fade_columns'] === 'yes') {
            $this->add_render_attribute('timeline', 'class', 'themeshark-timeline--fadein');
        }

    ?>
        <div class="themeshark-timeline-wrapper">
            <div <?php $this->print_render_attribute_string('timeline'); ?>>

                <!-- vertical timeline bar-->
                <div class="vert-bar-cont">
                    <div class="vert-line-back"></div>
                    <div class="vert-line-front"></div>
                </div>

                <div class="timeline-items">
                    <?php foreach ($slides as $index => $slide) {
                        $this->print_slide($slide, $settings, $index);
                    } ?>
                </div>

            </div>
        </div>
<?php
    }
}
