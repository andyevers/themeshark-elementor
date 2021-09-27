<?php

namespace Themeshark_Elementor\Widgets;

if (!defined('ABSPATH')) exit;

use \Elementor\Group_Control_Border;
use \Elementor\Controls_Manager as CM;
use \Elementor\Group_Control_Box_Shadow;
use \Elementor\Group_Control_Typography;
use \Elementor\Group_Control_Background;
use \Elementor\Group_Control_Text_Shadow;
use \Themeshark_Elementor\Inc\Globals_Fixer;
use \Themeshark_Elementor\Inc\Shorthand_Controls;
use \Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use \Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use \Themeshark_Elementor\Inc\TS_Widget;

/**
 * ThemeShark Hover Effects Button Widget
 *
 * Button but with additional options and hover effects
 * 
 * @since 1.0.0
 */
class TS_Button extends TS_Widget
{

    const NAME = 'ts-button';
    const TITLE = 'Hover Effects Button';

    public static function register_styles()
    {
        self::widget_style('ts-button', self::get_dir_url(__DIR__, 'ts-button.css'));
    }


    public function get_style_depends()
    {
        return ['ts-button'];
    }

    public function get_icon()
    {
        return 'tsicon-effects-button';
    }

    public function get_categories()
    {
        return ['themeshark'];
    }
    public function get_keywords()
    {
        return self::keywords(['button', 'link']);
    }

    protected function _register_controls()
    {

        $SC = new Shorthand_Controls($this);

        $effect = '.themeshark-button--effect';
        $button = '.themeshark-button';

        //----------------------------//
        //------ SECTION BUTTON ------//
        //----------------------------//

        $this->start_controls_section('section_button', [
            'label' => $SC::_('Button'),
        ]);


        $SC->control('text', 'Text', CM::TEXT, [
            'dynamic' => ['active' => true],
            'default' => $SC::_('Click Here'),
            'placeholder' => $SC::_('Click Here')
        ]);

        $SC->control('link', 'Link', CM::URL, [
            'dynamic' => ['active' => true],
            'placeholder' => $SC::_('https://your-link.com'),
            'default' => ['url' => '#'],
        ]);

        $SC->responsive_control('align', 'Alignment', CM::CHOOSE, [
            'prefix_class' => 'elementor%s-align-',
            'default' => '',
            'options' => $SC::options_choose(
                ['left', 'Left', 'eicon-text-align-left'],
                ['center', 'Center', 'eicon-text-align-center'],
                ['right', 'Right', 'eicon-text-align-right'],
                ['justify', 'Justified', 'eicon-text-align-justify']
            )
        ]);

        $this->end_controls_section();


        //----------------------------//
        //------ SECTION EFFECT ------//
        //----------------------------//

        $this->start_controls_section('section_effect',  [
            'label' => $SC::_('Effect')
        ]);


        $SC->control('effect', 'Hover Effect', CM::SELECT, [
            'default' => '-effect-corners',
            'options' => $SC::options_select(
                ['-effect-standard', 'Standard'],
                ['-effect-corners', 'Corners'],
                ['-effect-bg-slide', 'BG Slide'],
                ['-effect-cross-arrow', 'Cross Arrow']
            )
        ]);

        $SC->control('transition_duration', 'Transition Duration (s)', CM::SLIDER, [
            'range' => $SC::range(['px', 0, 3, .1]),
            'selectors' => $SC::selectors([
                $button => [
                    '--transition: {{SIZE}}s'
                ]
            ])
        ]);

        $SC->control('effect_color', 'Color', CM::COLOR, [
            'condition' => ['effect' => '-effect-cross-arrow'],
            'separator' => 'before',
            'global' => ['default' => Global_Colors::COLOR_PRIMARY],
            'selectors' => $SC::selectors([
                "$effect-cross-arrow::before,
                 $effect-cross-arrow::after" => [
                    'border-left-color: {{VALUE}}'
                ]
            ])
        ]);


        $SC->control('effect_color_hover', 'Color Hover', CM::COLOR, [
            'conditions' => [
                'relation' => 'or',
                'terms' => [
                    $SC::cond_term('effect', '==', '-effect-cross-arrow'),
                    $SC::cond_term('effect', '==', '-effect-corners')
                ]
            ],
            'global' => ['default' => Global_Colors::COLOR_ACCENT],
            'selectors' => $SC::selectors([
                "$effect-cross-arrow:hover::after, 
                 $effect-cross-arrow:focus::after" => [
                    'border-left-color: {{VALUE}}'
                ],
                "$effect-corners::before,
                 $effect-corners::after" => [
                    'border-color: {{VALUE}}'
                ]
            ]),
        ]);

        $SC->control('effect_color_bg_hover', 'Background Hover', CM::COLOR, [
            'condition' => ['effect' => '-effect-cross-arrow'],
            'default' => '#ffffff',
            'selectors' => $SC::selectors([
                "$effect-cross-arrow:hover::before, 
                 $effect-cross-arrow:focus::before" => [
                    'border-left-color: {{VALUE}}'
                ]
            ]),
        ]);


        $SC->control('blend_difference', 'Mix Blend Difference', CM::SWITCHER, [
            'label_on' => $SC::_('Yes'),
            'label_off' => $SC::_('No'),
            'return_value' => 'on',
            'default' => '',
            'description' => '<strong>' . $SC::_('Tip: ') . '</strong>' . $SC::_('If your buttons are lighter colors, set the text and background to the same color and make the hover background black when using Mix Blend Difference.'),
            'condition' => ['effect' => '-effect-bg-slide'],
            'selectors_dictionary' => ['on' => 'mix-blend-mode: difference;'],
            'prefix_class' => 'themeshark-mix-blend-',
            'selectors' => $SC::selectors([
                '.themeshark-button-text' => [
                    '{{VALUE}}'
                ]
            ])
        ]);


        $SC->responsive_control('effect_width', 'Weight', CM::SLIDER, [
            'conditions' => [
                'relation' => 'or',
                'terms' => [
                    $SC::cond_term('effect', '==', '-effect-corners'),
                    $SC::cond_term('effect', '==', '-effect-cross-arrow')
                ]
            ],
            'range' => $SC::range(['px', 0, 10]),
            'selectors' => $SC::selectors([
                ".themeshark-button" => [
                    '--effect-width: {{SIZE}}px'
                ]
            ])
        ]);

        $SC->responsive_control('effect_offset', 'Offset', CM::SLIDER, [
            'condition' => ['effect' => '-effect-corners'],
            'range' => $SC::range(['px', 0, 15]),
            'selectors' => $SC::selectors([
                ".themeshark-button" => [
                    '--effect-offset: {{SIZE}}px'
                ]
            ])
        ]);


        $SC->responsive_control('effect_size', 'Size', CM::SLIDER, [
            'conditions' => [
                'relation' => 'or',
                'terms' => [
                    $SC::cond_term('effect', '==', '-effect-corners'),
                    $SC::cond_term('effect', '==', '-effect-cross-arrow')
                ]
            ],
            'range' => $SC::range(['px', 10, 30]),
            'selectors' => $SC::selectors([
                ".themeshark-button" => [
                    '--effect-size: {{SIZE}}px'
                ]
            ])
        ]);

        $this->end_controls_section();



        //----------------------------//
        //------ SECTION STYLE -------//
        //----------------------------//

        $this->start_controls_section('section_style',  [
            'label' => $SC::_('Button'),
            'tab' => CM::TAB_STYLE,
        ]);

        $SC->group_control('typography', Group_Control_Typography::get_type(), [
            'global' => ['default' => Global_Typography::TYPOGRAPHY_ACCENT],
            'selector' => '{{WRAPPER}} .themeshark-button-text',
        ]);

        $SC->group_control('text_shadow', Group_Control_Text_Shadow::get_type(), [
            'selector' => '{{WRAPPER}} .themeshark-button-text'
        ]);


        $this->start_controls_tabs('tabs_button_style');

        $this->start_controls_tab('tab_button_normal', [
            'label' => $SC::_('Normal'),
        ]);

        $SC->control('button_text_color', 'Text Color', CM::COLOR, [
            'default' => '#ffffff',
            'selectors' => $SC::selectors([
                $button => [
                    'color: {{VALUE}}'
                ]
            ])
        ]);
        $SC->group_control('background', Group_Control_Background::get_type(), [
            'selector' => '{{WRAPPER}} .themeshark-button',
            'exclude' => ['image'],
            'fields_options' => [
                'background' => ['default' => 'classic'],
                'color' => [
                    'label'  => $SC::_('Background Color'),
                    'global' => ['default' => Global_Colors::COLOR_ACCENT],
                    'selectors' => $SC::selectors([
                        $button => [
                            'background-color: {{VALUE}}',
                            '--text-before-bg: {{VALUE}}'
                        ],
                    ])
                ],
            ]
        ]);



        $this->end_controls_tab();

        $this->start_controls_tab('tab_button_hover',  [
            'label' => $SC::_('Hover'),
        ]);

        $SC->control('hover_color', 'Text Color', CM::COLOR, [
            // 'condition' => ['blend_difference' => ''],
            'global' => ['default' => Global_Colors::COLOR_ACCENT],
            Globals_Fixer::FIX => true,
            'selectors' => $SC::selectors([
                ":not(.themeshark-mix-blend-on) $button:hover, 
                 :not(.themeshark-mix-blend-on) $button:focus" => [
                    'color: {{VALUE}}'
                ]
            ])
        ]);

        $SC->control('button_background_hover_color', 'Background Color', CM::COLOR, [
            // 'condition' => ['background_background' => 'classic'],
            'conditions' => [
                'relation' => 'or',
                'terms' => [
                    $SC::cond_term('effect', '!=', '-effect-standard'),
                    $SC::cond_term('background_background', '==', 'classic')
                ]
            ],
            'default' => '#fff',
            'selectors' => $SC::selectors([
                "$effect-corners .themeshark-text-before,
                 $effect-bg-slide .themeshark-text-before,
                 $effect-standard:hover, 
                 $effect-standard:focus,
                 $effect-cross-arrow:hover, 
                 $effect-cross-arrow:focus" => [
                    'background-color: {{VALUE}}'
                ],
            ])
        ]);




        $SC->control('button_hover_border_color', 'Border Color', CM::COLOR, [
            'condition' => ['border_border!' => ''],
            'selectors' => $SC::selectors([
                // "$button:hover, 
                //  $button:focus
                //  $effect-corners:hover::before, 
                //  $effect-corners:hover::after, 
                //  $effect-corners:focus::before, 
                //  $effect-corners:focus::after" => [
                //     'border-color: {{VALUE}}'
                // ]
                "$button:hover, 
                 $button:focus" => [
                    'border-color: {{VALUE}}'
                ]
            ])
        ]);

        $this->end_controls_tab();
        $this->end_controls_tabs();


        $SC->group_control('border', Group_Control_Border::get_type(), [
            'selector' => "{{WRAPPER}} $button",
            'separator' => 'before',
            'fields_options' => [
                'border' => [
                    'default' => 'solid'
                ],
                'width' => ['selectors' => $SC::selectors([
                    $button => [
                        '--border-top-width: {{TOP}}{{UNIT}}',
                        '--border-right-width: {{RIGHT}}{{UNIT}}',
                        '--border-bottom-width: {{BOTTOM}}{{UNIT}}',
                        '--border-left-width: {{LEFT}}{{UNIT}}'
                    ],
                ])],
                'color' => [
                    'selectors' => $SC::selectors([
                        // "$button,
                        //  $effect-corners::before,
                        //  $effect-corners::after" => [
                        //     'border-color: {{VALUE}}'
                        // ],
                        "$button" => [
                            'border-color: {{VALUE}}'
                        ],
                    ]),
                    'global' => ['default' => Global_Colors::COLOR_ACCENT]
                ],
            ]
        ]);

        $SC->responsive_control('border_radius', 'Border Radius', CM::DIMENSIONS, [
            'size_units' => ['px', '%'],
            'selectors' => $SC::selectors([
                $button => [
                    'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
                ]
            ])
        ]);

        $SC->group_control('button_box_shadow', Group_Control_Box_Shadow::get_type(), [
            'selector' => '{{WRAPPER}} .themeshark-button',
        ]);

        $SC->responsive_control('text_padding', 'Padding', CM::DIMENSIONS, [
            'size_units' => ['px', 'em', '%'],
            'separator' => 'before',
            'selectors' => $SC::selectors([
                $button => [
                    'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}'
                ]
            ])
        ]);

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();

        $this->add_render_attribute('button', [
            'class' => [
                'themeshark-button',
                'elementor-button',
                'themeshark-button-' . $settings['effect'],
            ],
            'data-corners' => '',
            'role' => 'button'
        ]);

        $this->add_link_attributes('button', $settings['link']);
        $this->add_render_attribute('text',  'class', 'themeshark-button-text');
?>

        <div class="themeshark-button-wrapper">
            <a <?php $this->print_render_attribute_string('button'); ?>>
                <span class="themeshark-button-content-wrapper">
                    <span class="themeshark-text-before-wrapper">
                        <span class="themeshark-text-before"></span>
                    </span>
                    <span <?php $this->print_render_attribute_string('text'); ?>><?php esc_html_e($settings['text']); ?></span>
                </span>
            </a>
        </div>

    <?php
    }

    protected function content_template()
    {
    ?>
        <# view.addRenderAttribute( 'button' , 'class' , [ 'themeshark-button' , 'elementor-button' , 'themeshark-button-' + settings.effect ] ); view.addRenderAttribute( 'button' , 'role' , 'button' ); view.addRenderAttribute( 'text' , 'class' , ['themeshark-button-text', 'elementor-inline-editing' ]); view.addRenderAttribute( 'text' , 'data-elementor-setting-key' , 'text' ); #>

            <div class="themeshark-button-wrap">
                <a {{{ view.getRenderAttributeString( 'button' ) }}}>

                    <span class="themeshark-button-content-wrapper">
                        <span class="themeshark-text-before-wrapper">
                            <span class="themeshark-text-before"></span>
                        </span>
                        <span {{{ view.getRenderAttributeString( 'text' ) }}}>{{{settings.text}}}</span>
                    </span>
                </a>
            </div>
    <?php
    }
}
