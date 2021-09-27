<?php

namespace Themeshark_Elementor\Widgets;

if (!defined('ABSPATH')) exit;

use Elementor\Repeater;
use Elementor\Controls_Manager as CM;
use Themeshark_Elementor\Inc\TS_Widget;
use Elementor\Group_Control_Css_Filter;
use Themeshark_Elementor\Inc\Shorthand_Controls;

class TS_Lottie extends TS_Widget
{
    const NAME = 'ts-lottie';
    const TITLE = 'Lottie Logo';

    public static function register_styles()
    {
        self::widget_style('ts-lottie', self::get_dir_url(__DIR__, 'ts-lottie.css'));
    }

    public static function localize_scripts()
    {
        self::localize_script('demoLottiesFolder', self::get_dir_url(__DIR__, 'demo-lotties'));
    }

    public function get_icon()
    {
        return 'tsicon-lottie-logo';
    }

    public static function register_scripts()
    {
        self::widget_script('ts-lottie', self::get_dir_url(__DIR__, 'ts-lottie.js'), ['lottie']);
    }

    public function get_keywords()
    {
        return self::keywords(['lottie', 'lottie image']);
    }

    public function get_style_depends()
    {
        return ['ts-lottie'];
    }
    public function get_script_depends()
    {
        return ['lottie', 'ts-lottie'];
    }

    protected function register_controls()
    {

        $SC = $this->shorthand_controls();
        $this->start_controls_section('lottie', [
            'label' => __('Lottie', THEMESHARK_TXTDOMAIN),
        ]);

        $this->add_control(
            'source',
            [
                'label' => __('Source', THEMESHARK_TXTDOMAIN),
                'type' => CM::SELECT,
                'default' => 'media_file',
                'options' => [
                    'media_file' => __('Media File', THEMESHARK_TXTDOMAIN),
                    'external_url' => __('External URL', THEMESHARK_TXTDOMAIN),
                    'demos' => $SC::_('Demos')
                ],
                'frontend_available' => true,
            ]
        );

        $SC->control('source_demo_file', 'Demo Lotties', CM::SELECT, [
            'condition'          => ['source' => 'demos'],
            'default'            => 'buildi-builders.json',
            'frontend_available' => true,
            'options'            => $SC::options_select(
                ['buildi-builders.json', 'Buildi Builders Logo'],
                ['helix-fitness.json', 'Helix Fitness Logo'],
                ['double-roof.json', 'Double Roof']
            ),
        ]);


        $this->add_control(
            'source_external_url',
            [
                'label' => __('External URL', THEMESHARK_TXTDOMAIN),
                'type' => CM::URL,
                'condition' => [
                    'source' => 'external_url',
                ],
                'dynamic' => [
                    'active' => true,
                ],
                'placeholder' => __('Enter your URL', THEMESHARK_TXTDOMAIN),
                'frontend_available' => true,
            ]
        );

        $this->add_control(
            'source_json',
            [
                'label' => __('Upload JSON File', THEMESHARK_TXTDOMAIN),
                'type' => CM::MEDIA,
                'media_type' => 'application/json',
                'frontend_available' => true,
                'condition' => [
                    'source' => 'media_file',
                ],
            ]
        );

        $this->add_responsive_control(
            'align',
            [
                'label' => __('Alignment', THEMESHARK_TXTDOMAIN),
                'type' => CM::CHOOSE,
                'options' => [
                    'left'    => [
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
                'prefix_class' => 'elementor%s-align-',
                'default' => 'center',
            ]
        );

        $this->add_control(
            'link_to',
            [
                'label' => __('Link', THEMESHARK_TXTDOMAIN),
                'type' => CM::SELECT,
                'render_type' => 'none',
                'default' => 'none',
                'options' => [
                    'none' => __('None', THEMESHARK_TXTDOMAIN),
                    'custom' => __('Custom URL', THEMESHARK_TXTDOMAIN),
                ],
                'frontend_available' => true,
            ]
        );

        $this->add_control(
            'custom_link',
            [
                'label' => __('Link', THEMESHARK_TXTDOMAIN),
                'type' => CM::URL,
                'render_type' => 'none',
                'placeholder' => __('Enter your URL', THEMESHARK_TXTDOMAIN),
                'condition' => [
                    'link_to' => 'custom',
                ],
                'dynamic' => [
                    'active' => true,
                ],
                'default' => [
                    'url' => '',
                ],
                'show_label' => false,
                'frontend_available' => true,
            ]
        );

        // lottie.
        $this->end_controls_section();

        $this->start_controls_section('settings', [
            'label' => __('Settings', THEMESHARK_TXTDOMAIN),
        ]);

        $this->add_control(
            'trigger',
            [
                'label' => __('Trigger', THEMESHARK_TXTDOMAIN),
                'type' => CM::SELECT,
                'default' => 'arriving_to_viewport',
                'options' => [
                    'arriving_to_viewport' => __('Viewport', THEMESHARK_TXTDOMAIN),
                    'on_click' => __('On Click', THEMESHARK_TXTDOMAIN),
                    'on_hover' => __('On Hover', THEMESHARK_TXTDOMAIN),
                    'none' => __('None', THEMESHARK_TXTDOMAIN),
                ],
                'frontend_available' => true,
            ]
        );

        $this->add_control('viewport', [
            'label' => 'viewport',
            // 'type' => CM::SLIDER,
            'type' => CM::HIDDEN,
            'render_type' => 'none',
            'conditions' => [
                'relation' => 'or',
                'terms' => [
                    [
                        'name' => 'trigger',
                        'operator' => '===',
                        'value' => 'arriving_to_viewport',
                    ],
                    [
                        'name' => 'trigger',
                        'operator' => '===',
                        'value' => 'bind_to_scroll',
                    ],
                ],
            ],
            'default' => [
                'sizes' => [
                    'start' => 0,
                    'end' => 100,
                ],
                'unit' => '%',
            ],
            'labels' => [
                __('Bottom', THEMESHARK_TXTDOMAIN),
                __('Top', THEMESHARK_TXTDOMAIN),
            ],
            'scales' => 1,
            'handles' => 'range',
            'frontend_available' => true,

        ]);

        $this->add_control(
            'effects_relative_to',
            [
                'label' => __('Effects Relative To', THEMESHARK_TXTDOMAIN),
                'type' => CM::SELECT,
                'render_type' => 'none',
                'condition' => [
                    'trigger' => 'bind_to_scroll',
                ],
                'default' => 'viewport',
                'options' => [
                    'viewport' => __('Viewport', THEMESHARK_TXTDOMAIN),
                    'page' => __('Entire Page', THEMESHARK_TXTDOMAIN),
                ],
                'frontend_available' => true,
            ]
        );

        $this->add_control(
            'loop',
            [
                'label' => __('Loop', THEMESHARK_TXTDOMAIN),
                'type' => CM::SWITCHER,
                'render_type' => 'none',
                'condition' => [
                    'trigger!' => 'bind_to_scroll',
                ],
                'return_value' => 'yes',
                'default' => '',
                'frontend_available' => true,
            ]
        );

        $this->add_control(
            'number_of_times',
            [
                'label' => __('Times', THEMESHARK_TXTDOMAIN),
                'type' => CM::NUMBER,
                'render_type' => 'none',
                'conditions' => [
                    'relation' => 'and',
                    'terms' => [
                        [
                            'name' => 'trigger',
                            'operator' => '!==',
                            'value' => 'bind_to_scroll',
                        ],
                        [
                            'name' => 'loop',
                            'operator' => '===',
                            'value' => 'yes',
                        ],
                    ],
                ],
                'min' => 0,
                'step' => 1,
                'frontend_available' => true,
            ]
        );

        $this->add_control(
            'link_timeout',
            [
                'label' => __('Link Timeout', THEMESHARK_TXTDOMAIN) . ' (ms)',
                'type' => CM::NUMBER,
                'render_type' => 'none',
                'conditions' => [
                    'relation' => 'and',
                    'terms' => [
                        [
                            'name' => 'link_to',
                            'operator' => '===',
                            'value' => 'custom',
                        ],
                        [
                            'name' => 'trigger',
                            'operator' => '===',
                            'value' => 'on_click',
                        ],
                        [
                            'name' => 'custom_link[url]',
                            'operator' => '!==',
                            'value' => '',
                        ],
                    ],
                ],
                'description' => __('Redirect to link after selected timeout', THEMESHARK_TXTDOMAIN),
                'min' => 0,
                'max' => 5000,
                'step' => 1,
                'frontend_available' => true,
            ]
        );

        $this->add_control(
            'on_hover_out',
            [
                'label' => __('On Hover Out', THEMESHARK_TXTDOMAIN),
                'type' => CM::SELECT,
                'render_type' => 'none',
                'condition' => [
                    'trigger' => 'on_hover',
                ],
                'default' => 'default',
                'options' => [
                    'default' => __('Default', THEMESHARK_TXTDOMAIN),
                    'reverse' => __('Reverse', THEMESHARK_TXTDOMAIN),
                    'pause' => __('Pause', THEMESHARK_TXTDOMAIN),
                ],
                'frontend_available' => true,
            ]
        );

        $this->add_control(
            'hover_area',
            [
                'label' => __('Hover Area', THEMESHARK_TXTDOMAIN),
                // 'type' => CM::SELECT,
                'render_type' => 'none',
                'condition' => [
                    'trigger' => 'on_hover',
                ],
                'default' => 'animation',
                'options' => [
                    'animation' => __('Animation', THEMESHARK_TXTDOMAIN),
                    'column' => __('Column', THEMESHARK_TXTDOMAIN),
                    'section' => __('Section', THEMESHARK_TXTDOMAIN),
                ],
                'frontend_available' => true,
            ]
        );

        $this->add_control(
            'play_speed',
            [
                'label' => __('Play Speed', THEMESHARK_TXTDOMAIN) . ' (x)',
                'type' => CM::SLIDER,
                'render_type' => 'template',
                'condition' => [
                    'trigger!' => 'bind_to_scroll',
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 1,
                ],
                'range' => [
                    'px' => [
                        'min' => 0.1,
                        'max' => 5,
                        'step' => 0.1,
                    ],
                ],
                'size_units' => ['px'],
                'dynamic' => [
                    'active' => true,
                ],
                'frontend_available' => true,
            ]
        );

        $this->add_control(
            'start_point',
            [
                'label' => __('Start Point', THEMESHARK_TXTDOMAIN),
                'type' => CM::HIDDEN,
                // 'type' => CM::SLIDER,
                'frontend_available' => true,
                'render_type' => 'none',
                'default' => [
                    'size' => '0',
                    'unit' => '%',
                ],
                'size_units' => ['%'],
            ]
        );

        $this->add_control(
            'end_point',
            [
                'label' => __('End Point', THEMESHARK_TXTDOMAIN),
                'type' => CM::HIDDEN,
                // 'type' => CM::SLIDER,
                'frontend_available' => true,
                'render_type' => 'none',
                'default' => [
                    'size' => '100',
                    'unit' => '%',
                ],
                'size_units' => ['%'],
            ]
        );

        $this->add_control(
            'reverse_animation',
            [
                'label' => __('Reverse', THEMESHARK_TXTDOMAIN),
                'type' => CM::SWITCHER,
                'render_type' => 'none',
                'conditions' => [
                    'relation' => 'and',
                    'terms' => [
                        [
                            'name' => 'trigger',
                            'operator' => '!==',
                            'value' => 'bind_to_scroll',
                        ],
                        [
                            'name' => 'trigger',
                            'operator' => '!==',
                            'value' => 'on_hover',
                        ],
                    ],
                ],
                'return_value' => 'yes',
                'default' => '',
                'frontend_available' => true,
            ]
        );

        $this->add_control(
            'renderer',
            [
                'label' => __('Renderer', THEMESHARK_TXTDOMAIN),
                'type' => CM::SELECT,
                'default' => 'svg',
                'options' => [
                    'svg' => __('SVG', THEMESHARK_TXTDOMAIN),
                    'canvas' => __('Canvas', THEMESHARK_TXTDOMAIN),
                ],
                'separator' => 'before',
                'frontend_available' => true,
            ]
        );

        $this->add_control(
            'lazyload',
            [
                'label' => __('Lazy Load', THEMESHARK_TXTDOMAIN),
                'type' => CM::SWITCHER,
                'return_value' => 'yes',
                'default' => '',
                'frontend_available' => true,
            ]
        );

        // Settings.
        $this->end_controls_section();

        $this->start_controls_section(
            'style',
            [
                'label' => __('Lottie', THEMESHARK_TXTDOMAIN),
                'tab'   => CM::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'width',
            [
                'label' => __('Width', THEMESHARK_TXTDOMAIN),
                'type' => CM::SLIDER,
                'default' => [
                    'unit' => '%',
                ],
                'tablet_default' => [
                    'unit' => '%',
                ],
                'mobile_default' => [
                    'unit' => '%',
                ],
                'size_units' => ['%', 'px', 'vw'],
                'range' => [
                    '%' => [
                        'min' => 1,
                        'max' => 100,
                    ],
                    'px' => [
                        'min' => 1,
                        'max' => 1000,
                    ],
                    'vw' => [
                        'min' => 1,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--lottie-container-width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'space',
            [
                'label' => __('Max Width', THEMESHARK_TXTDOMAIN),
                'type' => CM::SLIDER,
                'default' => [
                    'unit' => '%',
                ],
                'tablet_default' => [
                    'unit' => '%',
                ],
                'mobile_default' => [
                    'unit' => '%',
                ],
                'size_units' => ['%', 'px', 'vw'],
                'range' => [
                    '%' => [
                        'min' => 1,
                        'max' => 100,
                    ],
                    'px' => [
                        'min' => 1,
                        'max' => 1000,
                    ],
                    'vw' => [
                        'min' => 1,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--lottie-container-max-width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'separator_panel_style',
            [
                'type' => CM::DIVIDER,
                'style' => 'thick',
            ]
        );

        $this->start_controls_tabs('image_effects');

        $this->start_controls_tab(
            'normal',
            [
                'label' => __('Normal', THEMESHARK_TXTDOMAIN),
            ]
        );

        $this->add_control(
            'opacity',
            [
                'label' => __('Opacity', THEMESHARK_TXTDOMAIN),
                'type' => CM::SLIDER,
                'range' => [
                    'px' => [
                        'max' => 1,
                        'min' => 0.10,
                        'step' => 0.01,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--lottie-container-opacity: {{SIZE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Css_Filter::get_type(),
            [
                'name' => 'css_filters',
                'selector' => '{{WRAPPER}} .themeshark-lottie__container',
            ]
        );

        // Normal.
        $this->end_controls_tab();

        $this->start_controls_tab(
            'hover',
            [
                'label' => __('Hover', THEMESHARK_TXTDOMAIN),
            ]
        );

        $this->add_control(
            'opacity_hover',
            [
                'label' => __('Opacity', THEMESHARK_TXTDOMAIN),
                'type' => CM::SLIDER,
                'range' => [
                    'px' => [
                        'max' => 1,
                        'min' => 0.10,
                        'step' => 0.01,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--lottie-container-opacity-hover: {{SIZE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Css_Filter::get_type(),
            [
                'name' => 'css_filters_hover',
                'selector' => '{{WRAPPER}} .themeshark-lottie__container:hover',
            ]
        );

        $this->add_control(
            'background_hover_transition',
            [
                'label' => __('Transition Duration', THEMESHARK_TXTDOMAIN),
                'type' => CM::SLIDER,
                'range' => [
                    'px' => [
                        'max' => 3,
                        'step' => 0.1,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--lottie-container-transition-duration-hover: {{SIZE}}s',
                ],
            ]
        );

        // Hover.
        $this->end_controls_tab();

        // Image effects.
        $this->end_controls_tabs();

        // lottie style.
        $this->end_controls_section();


        $this->start_controls_section('demo_styles', [
            'condition' => ['renderer' => 'svg'],
            'label'     => $SC::_('Colors'),
            'tab'       => CM::TAB_STYLE
        ]);

        $SC->control('colors_note', null, CM::RAW_HTML, [
            'raw' => $SC::_('Use "Starting Child" and "Ending Child" to select "g" elements in the svg')
        ]);


        $repeater = new Repeater();
        $this->add_repeater_controls($repeater);

        $SC->control('styles_repeater', null, CM::REPEATER, [
            'fields'        => $repeater->get_controls(),
            'title_field'   => '{{{ label || fill || stroke }}}',
            'prevent_empty' => false
        ]);

        $this->end_controls_section();
    }

    protected function add_repeater_controls(Repeater $repeater)
    {
        $SC = new Shorthand_Controls($repeater);
        $SC->control('label', 'Label', CM::TEXT, ['render_type' => 'ui']);
        $SC->control('fill', 'Fill', CM::COLOR);
        $SC->control('stroke', 'Stroke', CM::COLOR);
        $SC->control('selector_child_start', 'Starting Child', CM::NUMBER, ['min' => 0]);
        $SC->control('selector_child_end', 'Ending Child', CM::NUMBER, ['min' => 0]);
    }

    protected function get_repeater_styles_html($slides)
    {
        $styles_string = '<style>';

        $wrapper_class = '.elementor-element.elementor-element-' . $this->get_id();

        foreach ($slides as $slide) {
            if (empty($slide['selector_child_start'])) continue;

            $fill       = $slide['fill'];
            $stroke     = $slide['stroke'];
            $start      = $slide['selector_child_start'];
            $end        = $slide['selector_child_end'];
            $end        = empty($end) ? $start : $end;
            $selector   = "$wrapper_class svg > g[clip-path] > g:nth-child(n+$start):nth-child(-n+$end) *";
            $fill_css   = !empty($fill) ? "fill: $fill; " : '';
            $stroke_css = !empty($stroke) ? "stroke:$stroke; " : '';

            $styles_string .= "$selector { $fill_css $stroke_css }";
        }
        $styles_string .= '</style>';

        return $styles_string;
    }


    protected function render()
    {
        $settings         = $this->get_settings_for_display();
        $slides           = $settings['styles_repeater'];
        $widget_container = '<div class="themeshark-lottie__container"><div class="themeshark-lottie__animation"></div></div>';

        if (count($slides) > 0) echo $this->get_repeater_styles_html($slides);

        if (!empty($settings['custom_link']['url']) && 'custom' === $settings['link_to']) {
            $this->add_link_attributes('url', $settings['custom_link']);
            $widget_container = sprintf('<a class="themeshark-lottie__container__link" %1$s>%2$s</a>', $this->get_render_attribute_string('url'), $widget_container);
        }

        echo $widget_container;
    }

    protected function content_template()
    {
?>
        <# function get_repeater_styles_html(slides) { var styles_string='<style>' ; var wrapper_class='.elementor-element.elementor-element-' + view.model.id; for (slide of slides) { if (slide['selector_child_start'].length <=0) { continue; } var fill=slide['fill'], stroke=slide['stroke'], start=slide['selector_child_start'], end=slide['selector_child_end'], end=end.length <=0 ? start : end, selector=wrapper_class + ' svg > g[clip-path] > g:nth-child(n+' + start + '):nth-child(-n+' + end + ') *' , fill_css=fill.length> 0 ? 'fill: ' + fill + '; ' : '',
            stroke_css = stroke.length > 0 ? 'stroke: ' + stroke + '; ' : '';

            styles_string += selector + '{ ' + fill_css + stroke_css + ' }';
            }
            styles_string += '</style>';

            return styles_string;
            }

            var slides = settings['styles_repeater'];
            var widget_container = '<div class="themeshark-lottie__container">';
                widget_container += '<div class="themeshark-lottie__animation"></div>';
                widget_container += '</div>';


            if (slides.length > 0) {
            print(get_repeater_styles_html(slides));
            }

            if (settings.custom_link.url && 'custom' === settings.link_to) {
            widget_container = '<a class="themeshark-lottie__container__link" href="' + settings.custom_link.url + '">' + widget_container + '</a>';
            }
            print(widget_container);
            #>
    <?php
    }
}
