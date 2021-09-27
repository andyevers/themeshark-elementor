<?php

namespace Themeshark_Elementor\Widgets;

if (!defined('ABSPATH')) exit;

use Elementor\Controls_Manager as CM;
use Elementor\Group_Control_Border;

trait TS_Swiper_Trait
{
    /**
     * @var $attribute_swiper Outermost attribute - .themeshark-swiper-outer-wrap
     * @var $attribute_swiper target for swiper.js - .swiper-container
     * @var $attribute_wrapper Inside swiper container - .swiper-wrapper
     * @var $attribute_slide Slide Item attribute - .swiper-slide
     * @var $attribute_slide_inner Slide Item inner wrap - .swiper-slide-inner 
     * @var $attribute_nav_dots Wrap for nav dots - .swiper-pagination
     * @var $attribute_nav_arrows Wrap for nav arrows - .swiper-arrows
     */

    // RENDER ATTRIBUTES
    //----------------------------------------------------
    public $attribute_swiper_outer  = 'swiper_outer_wrap';
    public $attribute_swiper        = 'swiper';
    public $attribute_wrapper       = 'wrapper';
    public $attribute_slide         = 'slide';
    public $attribute_slide_inner   = 'slide_inner';
    public $attribute_nav_dots      = 'nav_dots';
    public $attribute_nav_arrows    = 'nav_arrows';

    public $control_key_slides      = 'slides';
    public $outer_wrap_class        = 'themeshark-carousel-widget';


    /**
     * Call this in register_styles()
     */
    public static function register_default_styles()
    {
        self::widget_style('ts-swiper', self::get_dir_url(__DIR__, 'ts-swiper.css'));
    }

    /**
     * Call this in register_scripts()
     */
    public static function register_default_scripts()
    {
        self::widget_script('ts-swiper', self::get_dir_url(__DIR__, 'ts-swiper.js'));
    }


    /**
     * General slide styling
     */
    public function section_slide_style()
    {
        $SC = $this->shorthand_controls();

        $slide_inner = '.swiper-slide-inner';

        $this->start_controls_section('section_slide_style', [
            'label'     => $SC::_('Slide'),
            'tab'       => CM::TAB_STYLE
        ]);

        $SC->responsive_control('slide_spacing', 'Spacing', CM::SLIDER, [
            'default'   => $SC::range_default('px', 5),
            'range'     => $SC::range(['px', 0, 30]),
            'selectors' => $SC::selectors([
                $slide_inner => [
                    'margin-left: {{SIZE}}{{UNIT}}',
                    'margin-right: {{SIZE}}{{UNIT}}'
                ]
            ])
        ]);

        $SC->control('slide_head_background', 'Background Color', CM::COLOR, [
            'default'   => '#fff',
            'selectors' => $SC::selectors([
                $slide_inner => [
                    'background-color: {{VALUE}}'
                ]
            ]),
        ]);

        $SC->group_control('slide_border', Group_Control_Border::get_type(), [
            'selector'      => "{{WRAPPER}} $slide_inner",
            'separator'     => 'before',
        ]);

        $SC->control('slide_border_radius', 'Border Radius', CM::DIMENSIONS, [
            'selectors' => $SC::selectors([
                $slide_inner => [
                    'border-radius:{{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}'
                ]
            ])
        ]);

        $this->end_controls_section();
    }

    /**
     * Controls section for arrows and dots styles
     */
    public function section_navigation_style()
    {
        $SC = $this->shorthand_controls();

        // SELECTORS & CONDITIONS
        //----------------------------------------------------
        $swiper_outer_wrap  = '.themeshark-swiper-outer-wrap';
        $nav_arrow_prev     = '.elementor-swiper-button.elementor-swiper-button-prev';
        $nav_arrow_next     = '.elementor-swiper-button.elementor-swiper-button-next';
        $nav_dot            = '.swiper-pagination-bullet';

        $condition_arrows   = ['navigation' => ['arrows', 'both']];
        $condition_dots     = ['navigation' => ['dots', 'both']];


        $this->start_controls_section('section_style_navigation', [
            'label'         => $SC::_('Navigation'),
            'tab'           => CM::TAB_STYLE,
            'condition'     => ['navigation' => ['arrows', 'dots', 'both']],
        ]);


        // ARROWS
        //------------------------------------------
        $SC->control('heading_style_arrows', 'Arrows', CM::HEADING, [
            'condition'     => $condition_arrows,
            'separator'     => 'before',
        ]);

        $SC->control('arrows_position', 'Position', CM::SELECT, [
            'condition'     => $condition_arrows,
            'prefix_class'  => 'themeshark-arrows-position-',
            'default'       => 'outside',
            'options'       => $SC::options_select(
                ['inside',  'Inside'],
                ['outside', 'Outside']
            ),
        ]);

        $SC->responsive_control('arrows_size', 'Size', CM::SLIDER, [
            'condition'     => $condition_arrows,
            'range'         => $SC::range(['px', 20, 60]),
            'selectors'     => $SC::selectors([
                "$nav_arrow_prev, 
                 $nav_arrow_next" => [
                    'font-size: {{SIZE}}{{UNIT}};'
                ]
            ])
        ]);

        $SC->control('arrows_color', 'Color', CM::COLOR, [
            'condition'     => $condition_arrows,
            'selectors'     => $SC::selectors([
                "$nav_arrow_prev, 
                 $nav_arrow_next" => [
                    'color: {{VALUE}}'
                ]
            ]),
        ]);

        $SC->responsive_control('arrow_spacing', 'Distance', CM::SLIDER, [
            'range' => $SC::range(['px', 0, 50]),
            'selectors' => $SC::selectors([
                $swiper_outer_wrap => [
                    '--distance-arrows: {{SIZE}}{{UNIT}}'
                ]
            ])
        ]);

        // DOTS
        //----------------------------------------------------
        $SC->control('heading_style_dots', 'Dots', CM::HEADING, [
            'condition'     => $condition_dots,
            'separator'     => 'before'
        ]);

        $SC->control('dots_position', 'Position', CM::SELECT, [
            'condition'     => $condition_dots,
            'default'       => 'outside',
            'prefix_class'  => 'themeshark-pagination-position-',
            'options'       => $SC::options_select(
                ['outside', 'Outside'],
                ['inside',  'Inside']
            )
        ]);

        $SC->responsive_control('dots_size', 'Size', CM::SLIDER, [
            'condition'     => $condition_dots,
            'range'         => $SC::range(['px', 5, 10]),
            'selectors'     => $SC::selectors([
                $nav_dot => [
                    'width: {{SIZE}}{{UNIT}}',
                    'height: {{SIZE}}{{UNIT}}',
                ]
            ])
        ]);

        $SC->control('dots_color', 'Color', CM::COLOR, [
            'condition'     => $condition_arrows,
            'selectors'     => $SC::selectors([
                $nav_dot => [
                    'background-color: {{VALUE}}'
                ]
            ]),
        ]);

        $SC->responsive_control('dots_spacing', 'Distance', CM::SLIDER, [
            'range' => $SC::range(['px', 0, 50]),
            'selectors' => $SC::selectors([
                $swiper_outer_wrap => [
                    '--distance-dots: {{SIZE}}{{UNIT}}'
                ]
            ])
        ]);

        $this->end_controls_section();
    }


    /**
     * Controls section for slide settings
     */
    public function section_additional_options()
    {
        $SC = $this->shorthand_controls();


        // ADDITIONAL SETTINGS
        //----------------------------------------------------
        $this->start_controls_section('section_additional_options', [
            'label'         => $SC::_('Additional Options'),
        ]);

        $SC->control('autoplay', 'Autoplay', CM::SELECT, [
            'default'            => 'yes',
            'frontend_available' => true,
            'options'            => $SC::options_select(
                ['yes', 'Yes'],
                ['no',  'No']
            ),
        ]);

        $SC->control('pause_on_hover', 'Pause on Hover', CM::SELECT, [
            'condition'          => ['autoplay' => 'yes'],
            'default'            => 'yes',
            'render_type'        => 'none',
            'frontend_available' => true,
            'options'            => $SC::options_select(
                ['yes', 'Yes'],
                ['no',  'No']
            ),
        ]);

        $SC->control('pause_on_interaction', 'Pause on Interaction', CM::SELECT, [
            'condition'          => ['autoplay' => 'yes'],
            'frontend_available' => true,
            'default'            => 'yes',
            'options'            => $SC::options_select(
                ['yes', 'Yes'],
                ['no',  'No']
            ),

        ]);

        $SC->control('autoplay_speed', 'Autoplay Speed', CM::NUMBER, [
            'condition'          => ['autoplay' => 'yes'],
            'frontend_available' => true,
            'default'            => 5000,
            'render_type'        => 'none',
        ]);


        // Loop requires a re-render so no 'render_type = none'
        $SC->control('infinite', 'Infinite Loop', CM::SELECT, [
            'default'            => 'yes',
            'frontend_available' => true,
            'options'            => $SC::options_select(
                ['yes', 'Yes'],
                ['no',  'No']
            ),
        ]);

        $SC->control('effect', 'Effect', CM::SELECT, [
            'condition'          => ['slides_to_show' => '1'],
            'default'            => 'slide',
            'frontend_available' => true,
            'options'            => $SC::options_select(
                ['slide', 'Slide'],
                ['fade',  'Fade']
            ),
        ]);

        $SC->control('speed', 'Animation Speed', CM::NUMBER, [
            'default'            => 500,
            'render_type'        => 'none',
            'frontend_available' => true,
        ]);

        $SC->control('direction', 'Direction', CM::SELECT, [
            'default'       => 'ltr',
            'options'       => $SC::options_select(
                ['ltr', 'Left'],
                ['rtl', 'Right']
            )
        ]);

        $this->end_controls_section();
    }



    /**
     * Controls: Slides to Show, Slides to Scroll, Navigation
     */
    public function controls_slide_settings()
    {
        $SC = $this->shorthand_controls();

        $slides_to_show = range(1, 10);
        $slides_to_show = array_combine($slides_to_show, $slides_to_show);


        // SLIDE SETTINGS
        //----------------------------------------------------
        $SC->responsive_control('slides_to_show', 'Slides to Show', CM::SELECT, [
            'options'            => ['' => $SC::_('Default')] + $slides_to_show,
            'frontend_available' => true
        ]);

        $SC->responsive_control('slides_to_scroll', 'Slides to Scroll', CM::SELECT, [
            'condition'          => ['slides_to_show!' => '1'],
            'options'            => ['' => $SC::_('Default')] + $slides_to_show,
            'description'        => $SC::_('Set how many slides are scrolled per swipe.'),
            'frontend_available' => true
        ]);

        $SC->control('navigation', 'Navigation', CM::SELECT, [
            'default'            => 'both',
            'frontend_available' => true,
            'options'            => $SC::options_select(
                ['both',   'Arrows and Dots'],
                ['arrows', 'Arrows'],
                ['dots',   'Dots'],
                ['none',   'None']
            ),
        ]);
    }


    /**
     * Adds render attributes for: container, wrapper, slide, & slide inner
     */
    public function add_default_render_attributes()
    {
        $this->add_render_attribute($this->attribute_swiper_outer, 'class', 'themeshark-swiper-outer-wrap');
        $this->add_render_attribute($this->attribute_swiper, 'class', ['swiper-container', 'themeshark-swiper']);
        $this->add_render_attribute($this->attribute_wrapper, 'class', 'swiper-wrapper');
        $this->add_render_attribute($this->attribute_slide, 'class', 'swiper-slide');
        $this->add_render_attribute($this->attribute_slide_inner, 'class', 'swiper-slide-inner');

        $this->add_render_attribute($this->attribute_nav_dots, 'class', [
            'swiper-pagination',
            'swiper-pagination-' . $this->get_ID()
        ]);

        $this->add_render_attribute($this->attribute_nav_arrows, 'class', [
            'swiper-arrows',
            'swiper-arrows-' . $this->get_ID()
        ]);
    }


    /**
     * Whether to show dots, arrows, or both
     */
    public function get_nav_settings()
    {
        $settings       = $this->get_settings_for_display();
        $show_dots      = (in_array($settings['navigation'], ['dots', 'both']));
        $show_arrows    = (in_array($settings['navigation'], ['arrows', 'both']));

        return ['show_dots' => $show_dots, 'show_arrows' => $show_arrows];
    }


    /**
     * When using render_default_layout(), this is used to render the individual swiper slides
     */
    public function render_slide($settings, $slide, $index = 0)
    {
    }

    /**
     * When using render_default_layout(), this is called before default layout renders
     */
    public function on_before_render_default_layout()
    {
    }
    /**
     * When using render_default_layout(), this is called before after layout renders
     */
    public function on_after_render_default_layout()
    {
    }

    /**
     * Render default layout. if using this, use function $this->render_slide($slide, $index) to create individual slides
     */
    public function render_default_layout()
    {
        $this->on_before_render_default_layout();
        $this->add_default_render_attributes();

        $settings       = $this->get_settings_for_display();
        $slides         = $settings[$this->control_key_slides];
        $nav_settings   = $this->get_nav_settings();
        $show_dots      = $nav_settings['show_dots'];
        $show_arrows    = $nav_settings['show_arrows']; ?>

        <div <?php $this->print_render_attribute_string($this->attribute_swiper_outer); ?>>

            <div <?php $this->print_render_attribute_string($this->attribute_swiper); ?>>

                <div <?php $this->print_render_attribute_string($this->attribute_wrapper); ?>>

                    <?php foreach ($slides as $index => $slide) : ?>

                        <div <?php $this->print_render_attribute_string($this->attribute_slide); ?>>

                            <div <?php $this->print_render_attribute_string($this->attribute_slide_inner); ?>>

                                <?php $this->render_slide($settings, $slide, $index); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php if (1 < count($slides)) $this->render_nav($show_dots, $show_arrows); ?>
        </div>
    <?php $this->on_after_render_default_layout();
    }

    /**
     * Render Arrows and dots
     */
    public function render_nav($show_dots = true, $show_arrows = true)
    {
        if ($show_dots === false && $show_arrows === false) return;
        $SC = $this->shorthand_controls(); ?>

        <div class='themeshark-swiper-navigation'>

            <?php if ($show_dots) : ?>
                <div <?php $this->print_render_attribute_string($this->attribute_nav_dots); ?>></div>
            <?php endif; ?>

            <?php if ($show_arrows) : ?>

                <div <?php $this->print_render_attribute_string($this->attribute_nav_arrows); ?>>

                    <div class="elementor-swiper-button elementor-swiper-button-prev">
                        <i class="eicon-chevron-left" aria-hidden="true"></i>
                        <span class="elementor-screen-only"> <?php $SC::_('Previous'); ?></span>
                    </div>

                    <div class="elementor-swiper-button elementor-swiper-button-next">
                        <i class="eicon-chevron-right" aria-hidden="true"></i>
                        <span class="elementor-screen-only"> <?php $SC::_('Next'); ?> </span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
<?php }
}
