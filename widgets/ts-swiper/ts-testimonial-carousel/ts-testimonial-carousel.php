<?php

namespace Themeshark_Elementor\Widgets\TS_Swiper;

if (!defined('ABSPATH')) exit;

use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Themeshark_Elementor\Inc\TS_Widget;
use Elementor\Controls_Manager as CM;
use Elementor\Group_Control_Typography;
use Themeshark_Elementor\Inc\Shorthand_Controls;
use \Elementor\Repeater;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Themeshark_Elementor\Inc\Globals_Fixer;
use Themeshark_Elementor\Inc\Helpers;
use Elementor\Group_Control_Box_Shadow;

require_once THEMESHARK_PATH . 'widgets/ts-swiper/ts-swiper-trait.php';

class TS_Testimonial_Carousel extends TS_Widget
{
    use \Themeshark_Elementor\Widgets\TS_Swiper_Trait;

    const NAME = 'ts-testimonial-carousel';
    const TITLE = 'Testimonials';

    public static function register_styles()
    {
        self::register_default_styles();
        self::widget_style('ts-testimonial-carousel', self::get_dir_url(__DIR__, 'ts-testimonial-carousel.css'));
    }
    public static function register_scripts()
    {
        self::register_default_scripts();
    }

    public function get_script_depends()
    {
        return ['ts-swiper'];
    }

    public function get_style_depends()
    {
        return ['ts-swiper', 'ts-testimonial-carousel'];
    }

    public function get_icon()
    {
        return 'tsicon-testimonials';
    }


    public function get_keywords()
    {
        return self::keywords(['testimonials', 'carousel', 'slider']);
    }

    protected function add_repeater_controls(Repeater $repeater)
    {
        $SCR = new Shorthand_Controls($repeater);

        $SCR->control('content', 'Content', CM::TEXTAREA, [
            'dynamic'   => ['active' => true]
        ]);

        $SCR->control('image', 'Image', CM::MEDIA, [
            'dynamic'   => ['active' => true]
        ]);

        $SCR->control('name', 'Name', CM::TEXT, [
            'default'   => $SCR::_('John Doe'),
            'dynamic'   => ['active' => true],
        ]);

        $SCR->control('title', 'Title', CM::TEXT, [
            'default'   => $SCR::_('CEO'),
            'dynamic'   => ['active' => true],
        ]);
    }

    protected function get_repeater_defaults()
    {
        $placeholder_image_src = Helpers::get_placeholder_gravatar();
        $defaults = [
            'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.',
            'image'   => ['url' => $placeholder_image_src]
        ];

        $default_1 = array_merge(['name' => 'John Doe', 'title' => 'President'], $defaults);
        $default_2 = array_merge(['name' => 'Stacey Smith', 'title' => 'Manager'], $defaults);
        $default_3 = array_merge(['name' => 'Bob Thompson', 'title' => 'Accountant'], $defaults);

        return [$default_1, $default_2, $default_3];
    }

    protected function register_controls()
    {
        $SC = $this->shorthand_controls();


        // SELECTORS
        //----------------------------------------------------
        $slide_item         = '.themeshark-testimonial-carousel-slide';
        $slide_item_inner   = '.themeshark-testimonial-carousel-slide-inner';
        $slide_item_head    = '.reviewer-card-head';
        $image_wrap_inner   = '.reviewer-image-wrap-inner';
        $text_testimonial   = '.reviewer-testimonial';
        $text_title         = '.review-details-title';
        $text_name          = '.review-details-name';

        $this->start_controls_section('section_image_carousel', [
            'label'     => $SC::_('Slides'),
        ]);


        $repeater = new Repeater();
        $this->add_repeater_controls($repeater);
        $SC->control($this->control_key_slides, 'Add Images', CM::REPEATER, [
            'title_field'   => '{{{ name }}}',
            'dynamic'       => ['active' => true],
            'fields'        => $repeater->get_controls(),
            'default'       => $this->get_repeater_defaults(),
            'render_type'   => 'template'
        ]);

        $SC->add_image_size_control('thumbnail');

        $this->controls_slide_settings();

        $this->end_controls_section();

        $this->section_additional_options();
        $this->section_slide_style();

        //update default border
        $this->update_control('slide_border_border', ['default' => 'solid']);
        $this->update_control('slide_border_color', ['default' => '#e1e8ed']);


        // SECTION IMAGE STYLE
        //----------------------------------------------------
        $this->start_controls_section('section_image_style', [
            'label'     => $SC::_('Image'),
            'tab'       => CM::TAB_STYLE,
        ]);

        $SC->control('use_image', 'Use Image', CM::SWITCHER, [
            'return_value'  => 'yes',
            'default'       => 'yes'
        ]);

        $SC->responsive_control('image_size', 'Size', CM::SLIDER, [
            'condition'     => ['use_image' => 'yes'],
            'default'       => $SC::range_default('px', 70),
            'range'         => $SC::range(['px', 35, 200]),
            'selectors'     => $SC::selectors([
                $slide_item => [
                    '--image-size: {{SIZE}}{{UNIT}}',
                ]
            ])
        ]);

        $SC->control('image_spacing', 'Spacing', CM::SLIDER, [
            'condition'     => ['use_image' => 'yes'],
            'range'         => $SC::range(['px', 0, 50]),
            'selectors'     => $SC::selectors([
                $slide_item => [
                    '--image-spacing: {{SIZE}}{{UNIT}}'
                ]
            ])
        ]);

        $SC->control('image_border', 'Border', CM::SWITCHER, [
            'condition'     => ['use_image' => 'yes'],
            'separator'     => 'before',
            'selectors'     => $SC::selectors([
                $image_wrap_inner => [
                    'border-style: solid'
                ]
            ])
        ]);

        $SC->control('image_border_color', 'Border Color', CM::COLOR, [
            'condition'         => ['image_border' => 'yes', 'use_image' => 'yes'],
            'global'            => ['default' => Global_Colors::COLOR_ACCENT],
            Globals_Fixer::FIX  => true,
            'selectors'         => $SC::selectors([
                $image_wrap_inner => [
                    'border-color: {{VALUE}}'
                ]
            ]),
        ]);

        $SC->group_control('image_box_shadow', Group_Control_Box_Shadow::get_type(), [
            'selector' => "{{WRAPPER}} $image_wrap_inner"
        ]);

        $SC->responsive_control('image_border_width', 'Border Width', CM::SLIDER, [
            'condition'     => ['image_border' => 'yes', 'use_image' => 'yes'],
            'range'         => $SC::range(['px', 0, 20]),
            'selectors'     => $SC::selectors([
                $image_wrap_inner => [
                    'border-width: {{SIZE}}{{UNIT}}'
                ]
            ])
        ]);

        $SC->control('image_border_radius', 'Border Radius', CM::SLIDER, [
            'default'       => $SC::range_default('px', 50),
            'condition'     => ['use_image' => 'yes'],
            'selectors'     => $SC::selectors([
                $image_wrap_inner => [
                    'border-radius: {{SIZE}}{{UNIT}}'
                ]
            ])
        ]);



        $this->end_controls_section();



        // SECTION CONTENT STYLE
        //----------------------------------------------------
        $this->start_controls_section('section_content_style', [
            'label'     => $SC::_('Content'),
            'tab'       => CM::TAB_STYLE,
        ]);

        $SC->control('content_alignment', 'Alignment', CM::CHOOSE, [
            'options'       => $SC::choice_set_text_align(['left', 'center', 'right']),
            'default'       => 'center',
            'prefix_class'  => 'testimonial-slide-align-',
            'toggle'        => false,
            'selectors'     => $SC::selectors([
                $slide_item_inner => [
                    'text-align:{{VALUE}}'
                ]
            ]),
        ]);

        $SC->control('content_invert', 'Invert Layout', CM::SWITCHER, [
            'condition'     => ['content_alignment' => 'center'],
            'prefix_class'  => 'themeshark-testimonials-content-',
            'return_value'  => 'reverse',
            'selectors'     => $SC::selectors([
                '.themeshark-testimonial-carousel-slide-figure' => [
                    'flex-direction: column-reverse;'
                ]
            ]),

        ]);

        $SC->control('heading_content_head', 'Content Head', CM::HEADING, [
            'separator'     => 'before'
        ]);

        $SC->control('content_head_background', 'Background Color', CM::COLOR, [
            Globals_Fixer::FIX  => true,
            'global'            => ['default' => Global_Colors::COLOR_PRIMARY],
            'selectors'         => $SC::selectors([
                $slide_item_head => [
                    'background-color: {{VALUE}}'
                ]
            ]),
        ]);

        $SC->control('content_head_padding', 'Padding', CM::DIMENSIONS, [
            'selectors'     => $SC::selectors([
                $slide_item => [
                    '--padding-head-top: {{TOP}}{{UNIT}}',
                    '--padding-head-right: {{RIGHT}}{{UNIT}}',
                    '--padding-head-bottom:{{BOTTOM}}{{UNIT}}',
                    '--padding-head-left: {{LEFT}}{{UNIT}}',
                ]
            ])
        ]);

        $SC->control('content_head_border_bottom_color', 'Border Bottom Color', CM::COLOR, [
            'selectors'     => $SC::selectors([
                $slide_item_head => [
                    'border-bottom-color: {{VALUE}}'
                ]
            ]),
        ]);

        $SC->control('content_head_border_bottom_width', 'Border Bottom Width', CM::SLIDER, [
            'range'         => $SC::range(['px', 0, 20]),
            'selectors'     => $SC::selectors([
                $slide_item => [
                    '--border-head-bottom-width:{{SIZE}}{{UNIT}}'
                ]
            ])
        ]);


        //NAME
        $SC->control('heading_name', 'Name', CM::HEADING, [
            'separator'     => 'before'
        ]);

        $SC->control('name_color', 'Text Color', CM::COLOR, [
            'default'       => '#fff',
            'selectors'     => $SC::selectors([
                $text_name => [
                    'color: {{VALUE}}'
                ]
            ])
        ]);

        $SC->group_control('name_typography', Group_Control_Typography::get_type(), [
            'global'        => ['default' => Global_Typography::TYPOGRAPHY_PRIMARY],
            'selector'      => "{{WRAPPER}} $text_name"
        ]);

        $SC->control('name_spacing_top', 'Spacing Top', CM::SLIDER, [
            'size_units'    => ['px', '%'],
            'default'       => $SC::range_default('px'),
            'range'         => $SC::range(['px', 0, 50], ['%', 0, 100]),
            'selectors'     => $SC::selectors([
                $text_name => [
                    'margin-top:{{SIZE}}{{UNIT}}'
                ]
            ])
        ]);
        //TITLE
        $SC->control('heading_title', 'Title', CM::HEADING, [
            'separator'     => 'before'
        ]);

        $SC->control('title_color', 'Text Color', CM::COLOR, [
            'default'       => '#fff',
            'selectors'     => $SC::selectors([
                $text_title => [
                    'color:{{VALUE}}'
                ]
            ])
        ]);

        $SC->group_control('title_typography', Group_Control_Typography::get_type(), [
            'global'        => ['default' => Global_Typography::TYPOGRAPHY_SECONDARY],
            'selector'      => "{{WRAPPER}} $text_title"
        ]);

        $SC->control('title_spacing_top', 'Spacing Top', CM::SLIDER, [
            'size_units'    => ['px', '%'],
            'default'       => $SC::range_default('px'),
            'range'         => $SC::range(['px', 0, 50], ['%', 0, 100]),
            'selectors'     => $SC::selectors([
                $text_title => [
                    'margin-top:{{SIZE}}{{UNIT}}'
                ]
            ])
        ]);

        //TESTIMONIAL
        $SC->control('content_testimonial_heading', 'Testimonial', CM::HEADING, [
            'separator'     => 'before'
        ]);

        $SC->control('content_color', 'Text Color', CM::COLOR, [
            'global'        => ['default' => Global_Colors::COLOR_TEXT],
            'selectors'     => $SC::selectors([
                $text_testimonial => [
                    'color: {{VALUE}}'
                ]
            ]),
        ]);

        $SC->group_control('content_typography', Group_Control_Typography::get_type(), [
            'global'        => ['default' => Global_Typography::TYPOGRAPHY_TEXT],
            'selector'      => "{{WRAPPER}} $text_testimonial",
        ]);

        $SC->responsive_control('content_testimonial_padding', 'Padding', CM::DIMENSIONS, [
            'selectors'     => $SC::selectors([
                $text_testimonial => [
                    'padding-right: {{RIGHT}}{{UNIT}}',
                    'padding-bottom: {{BOTTOM}}{{UNIT}}',
                    'padding-left: {{LEFT}}{{UNIT}}'
                ],
                $slide_item => [
                    '--padding-content-top: {{TOP}}{{UNIT}}',
                ]
            ])
        ]);


        $this->end_controls_section();

        $this->section_navigation_style();
    }

    public function on_before_render_default_layout()
    {
        $this->add_render_attribute($this->attribute_swiper, 'class', 'themeshark-testimonial-carousel');
        $this->add_render_attribute($this->attribute_wrapper, 'class', 'themeshark-testimonial-carousel-wrapper');
        $this->add_render_attribute($this->attribute_slide, 'class', 'themeshark-testimonial-carousel-slide');
        $this->add_render_attribute($this->attribute_slide_inner, 'class', 'themeshark-testimonial-carousel-slide-inner');
    }
    protected function render()
    {
        $this->render_default_layout();
    }


    public function render_slide($settings, $slide, $index)
    {
        $SC         = $this->shorthand_controls();
        $use_image  = $settings['use_image'] === 'yes';
        $image_html = $SC->get_image_html('image', $slide, $settings['thumbnail_size']);
        $image_html = empty($image_html) ? Helpers::get_placeholder_gravatar('standard', true) : $image_html; ?>

        <figure class='themeshark-testimonial-carousel-slide-figure'>

            <div class='reviewer-card-head'>

                <cite class='reviewer-details'>

                    <span class='review-details-name'><?php esc_html_e($slide['name']); ?></span>

                    <span class='review-details-title'><?php esc_html_e($slide['title']); ?></span>
                </cite>

                <?php if ($use_image) : ?>

                    <div class='reviewer-image-wrap'>

                        <div class='reviewer-image-wrap-inner'><?php echo Helpers::esc_wysiwyg($image_html); ?></div>
                    </div>
                <?php endif; ?>
            </div>
            <blockquote class='reviewer-testimonial'><?php echo Helpers::esc_wysiwyg($slide['content']); ?></blockquote>
        </figure>
<?php }
}
