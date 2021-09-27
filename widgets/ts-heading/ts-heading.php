<?php

namespace Themeshark_Elementor\Widgets;

if (!defined('ABSPATH')) exit;

use \Elementor\Group_Control_Border;
use \Elementor\Controls_Manager as CM;
use \Elementor\Group_Control_Typography;
use \Elementor\Group_Control_Background;
use \Elementor\Group_Control_Text_Shadow;
use \Themeshark_Elementor\Controls\Animations;
use \Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use \Themeshark_Elementor\Controls\Controls_Handler as CH;
use Themeshark_Elementor\Controls\Group_Control_Transform;
use \Themeshark_Elementor\Inc\TS_Widget;
use Themeshark_Elementor\Inc\Helpers;

/**
 * ThemeShark Heading Widget
 *
 * Heading Widget with extra options such as scroll animations and text backgrounds
 * 
 * @since 1.0.0
 */
class TS_Heading extends TS_Widget
{
    public static function register_styles()
    {
        self::widget_style('ts-heading', self::get_dir_url(__DIR__, 'ts-heading.css'));
    }

    const NAME = 'ts-heading';
    const TITLE = 'Effects Heading';


    public function get_style_depends()
    {
        return ['ts-heading'];
    }

    public function get_script_depends()
    {
        return ['ts-heading', 'scroll-observer'];
    }

    public function get_icon()
    {
        return 'tsicon-effects-heading';
    }

    public function get_categories()
    {
        return ['themeshark'];
    }

    public function get_keywords()
    {
        return self::keywords(['heading', 'title', 'text']);
    }

    protected function register_controls()
    {

        $SC = $this->shorthand_controls();
        $this->start_controls_section('section_title', [
            'label' => $SC::_('Title'),
        ]);

        $SC->control('title', 'Title', CM::TEXTAREA, [
            'dynamic' => ['active' => true,],
            'placeholder' => $SC::_('Enter your title'),
            'default' => $SC::_('Add Your Heading Text Here'),
            'render_type' => 'ui',
            'themeshark_settings' => [
                CH::LINK_HTML => [
                    'selector' => '{{WRAPPER}} .themeshark-heading-text'
                ]
            ]
        ]);
        $SC->control('link', 'Link', CM::URL, [
            'dynamic' => ['active' => true],
            'default' => ['url' => ''],
            'separator' => 'before',
        ]);

        $SC->control('header_size', 'HTML Tag', CM::SELECT, [
            'default' => 'h2',
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

        $SC->responsive_control('align', 'Alignment', CM::CHOOSE, [
            // 'default' => 'center',
            'options' => $SC::options_choose(
                ['left', 'Left', 'eicon-text-align-left'],
                ['center', 'Center', 'eicon-text-align-center'],
                ['right', 'Right', 'eicon-text-align-right']
            ),
            'selectors' => $SC::selectors([
                '{{WRAPPER}} .themeshark-heading' => [
                    'text-align: {{VALUE}}'
                ],
                '{{WRAPPER}}.themeshark-heading--effect-blur .themeshark-heading' => [
                    'transform-origin: center {{VALUE}}'
                ]
            ], null, false)
        ]);

        $SC->control('view', 'View', CM::HIDDEN, [
            'default' => 'traditional'
        ]);

        $this->end_controls_section();

        $this->start_controls_section('section_title_style', [
            'label' => $SC::_('Title'),
            'tab' => CM::TAB_STYLE,
        ]);


        $SC->control('use_gradient', 'Text Background', CM::SWITCHER, [
            'label_on'             => $SC::_('Yes'),
            'label_off'            => $SC::_('No'),
            'return_value'         => 'yes',
            'default'              => 'no',
            'render_type'          => 'template',
            'selectors_dictionary' => [
                'yes' => '-webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; color: transparent'
            ],
            'selectors' => $SC::selectors([
                '.themeshark-heading-text' => [
                    '{{VALUE}}'
                ]
            ])
        ]);

        $SC->group_control('text_background', Group_Control_Background::get_type(), [
            'fields_options' => [
                '__all' => ['responsive' => false],
                'background' => ['frontend_available' => true],
                'color' => ['global' => ['default' => Global_Colors::COLOR_ACCENT]],
                'attachment_alert' => [
                    'raw' => $SC::_("Notes: <br>1: Attachment Fixed works only on desktop. <br>2: Browsers that are known to not work well with fixed text backgrounds will use attachment: scroll."),
                ]
            ],
            'condition' => ['use_gradient' => 'yes'],
            'selector' => '{{WRAPPER}} .themeshark-heading--gradient .themeshark-heading-text'
        ]);

        $SC->control('title_color', 'Text Color', CM::COLOR, [
            'condition' => ['use_gradient!' => 'yes'],
            'global' => ['default' => Global_Colors::COLOR_PRIMARY],
            'selectors' => $SC::selectors([
                '.themeshark-heading-text,
                 .themeshark-heading-text a' => [
                    'color: {{VALUE}}'
                ]
            ])
        ]);

        $SC->control('title_divider', null, CM::DIVIDER);

        $SC->group_control('typography', Group_Control_Typography::get_type(), [
            'selector' => '{{WRAPPER}} .themeshark-heading-text',
        ]);


        $SC->group_control('text_shadow', Group_Control_Text_Shadow::get_type(), [
            'selector' => '{{WRAPPER}} .themeshark-heading-text',
        ]);

        $SC->group_control('text_transform', Group_Control_Transform::get_type(), [
            'selector' => '{{WRAPPER}} .themeshark-heading-text',
        ]);

        $SC->control('blend_mode', 'Blend Mode', CM::SELECT, [
            'options' => [
                '' => $SC::_('Normal'),
                'multiply' => 'Multiply',
                'screen' => 'Screen',
                'overlay' => 'Overlay',
                'darken' => 'Darken',
                'lighten' => 'Lighten',
                'color-dodge' => 'Color Dodge',
                'saturation' => 'Saturation',
                'color' => 'Color',
                'difference' => 'Difference',
                'exclusion' => 'Exclusion',
                'hue' => 'Hue',
                'luminosity' => 'Luminosity',
            ],

            'selectors' => $SC::selectors([
                '.themeshark-heading' => [
                    'mix-blend-mode: {{VALUE}}'
                ],
            ]),
        ]);

        //FIRST LETTER STYLES 
        $SC->control('first_letter_styles', 'First Letter Styles', CM::SWITCHER, [
            'label_off' => $SC::_('Off'),
            'label_on' => $SC::_('On'),
            'return_value' => 'on',
            'default' => 'off',
            'separator' => 'before'
        ]);


        $SC->control('fl_use_gradient', 'Text Background', CM::SWITCHER, [
            'condition' => ['first_letter_styles' => 'on'],
            'label_on' => $SC::_('Yes'),
            'label_off' => $SC::_('No'),
            'return_value' => 'yes',
            'default' => 'no',
            'selectors' => $SC::selectors([
                '.themeshark-heading-text::first-letter' => [
                    '-webkit-background-clip: text',
                    'background-clip: text',
                    '-webkit-text-fill-color: #00000000',
                    'color: transparent',
                ]
            ])
        ]);

        $SC->group_control('fl_text_background', Group_Control_Background::get_type(), [
            'conditions' => ['relation' => 'and', 'terms' => [
                $SC::cond_term('fl_use_gradient', '==', 'yes'),
                $SC::cond_term('first_letter_styles', '==', 'on')
            ]],
            'fields_options' => [
                '__all' => ['responsive' => false],
                'background' => ['frontend_available' => true],
                'attachment_alert' => [
                    'raw' => $SC::_("Notes: <br>1: Attachment Fixed works only on desktop. <br>2: Browsers that are known to not work well with fixed text backgrounds will use attachment: scroll."),
                ]
            ],
            'selector' => '{{WRAPPER}} .themeshark-heading-text::first-letter',
        ]);


        $SC->control('fl_color', 'Color', CM::COLOR, [
            'conditions' => ['relation' => 'and', 'terms' => [
                $SC::cond_term('fl_use_gradient', '!=', 'yes'),
                $SC::cond_term('first_letter_styles', '==', 'on')
            ]],
            'selectors' => $SC::selectors([
                '.themeshark-heading-text::first-letter' => [
                    'color: {{VALUE}};'
                ]
            ])
        ]);


        $SC->group_control('fl_typography', Group_Control_Typography::get_type(), [
            'condition' => ['first_letter_styles' => 'on'],
            'selector' => '{{WRAPPER}} .themeshark-heading-text::first-letter'
        ]);

        $SC->group_control('fl_border', Group_Control_Border::get_type(), [
            'condition' => ['first_letter_styles' => 'on'],
            'separator' => 'before',
            'selector' => '{{WRAPPER}} .themeshark-heading-text::first-letter',
        ]);

        $SC->responsive_control('fl_margin', 'Margin', CM::DIMENSIONS, [
            'condition' => ['first_letter_styles' => 'on'],
            'size_units' => ['px'],
            'selectors' => $SC::selectors([
                '.themeshark-heading-text::first-letter' => [
                    'margin-top: {{TOP}}{{UNIT}}',
                    'margin-right: {{RIGHT}}{{UNIT}}',
                    'margin-bottom: {{BOTTOM}}{{UNIT}}',
                    'margin-left: {{LEFT}}{{UNIT}}'
                ]
            ])
        ]);

        $SC->responsive_control('fl_padding', 'Padding', CM::DIMENSIONS, [
            'condition' => ['first_letter_styles' => 'on'],
            'size_units' => ['px'],
            'selectors' => $SC::selectors([
                '.themeshark-heading-text::first-letter' => [
                    'padding-top: {{TOP}}{{UNIT}}',
                    'padding-right: {{RIGHT}}{{UNIT}}',
                    'padding-bottom: {{BOTTOM}}{{UNIT}}',
                    'padding-left: {{LEFT}}{{UNIT}}'
                ]
            ])
        ]);

        $this->end_controls_section();


        $this->start_controls_section('section_title_scroll_animation', [
            'label' => $SC::_('Animation'),
            'tab' => CM::TAB_STYLE,
        ]);

        Animations::add_controls($this, [
            'animations' => $SC::options_select(
                ['themeshark-heading--effect-bar', 'Bottom Bar'],
                ['themeshark-heading--effect-blur', 'Blur Fade'],
                ['themeshark-heading--effect-mask', 'Mask']
            ),
            'defaults' => [
                'animation' => 'themeshark-heading--effect-bar'
            ]
        ]);

        $SC->control('effect_color', 'Effect Color', CM::COLOR, [
            'global' => ['default' => Global_Colors::COLOR_ACCENT],
            'separator' => 'before',
            'selectors' => $SC::selectors([
                '.themeshark-heading' => [
                    '--effect-color: {{VALUE}};'
                ]
            ]),
            'conditions' => ['relation' => 'or', 'terms' => [
                $SC::cond_term('animation', '==', 'themeshark-heading--effect-mask'),
                $SC::cond_term('animation', '==', 'themeshark-heading--effect-bar')
            ]]
        ]);

        $SC->control('effect_width', 'Effect Width', CM::SLIDER, [
            'condition' => ['animation' => 'themeshark-heading--effect-bar'],
            'range' => $SC::range(['px', 1, 15]),
            'selectors' => $SC::selectors([
                '.themeshark-heading' => [
                    '--effect-width: {{SIZE}}px'
                ]
            ])
        ]);

        $this->end_controls_section();
    }

    private function add_firefox_text_bg_compatibility($settings)
    {
        if (Helpers::get_browser_name() !== 'firefox') return;

        $is_fixed_text_bg    = isset($settings['text_background_attachment']) && $settings['text_background_attachment'] === 'fixed';
        $is_fixed_text_fl_bg = isset($settings['fl_text_background_attachment']) && $settings['fl_text_background_attachment'] === 'fixed';

        if ($is_fixed_text_bg) $this->add_render_attribute('title', 'class', 'bg-attachment-default');
        if ($is_fixed_text_fl_bg) $this->add_render_attribute('title', 'class', 'bg-attachment-default-fl');
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();

        $this->add_firefox_text_bg_compatibility($settings);

        if ('' === $settings['title']) return;

        if ($settings['animation'] === '-effect-blur') {
            $align = $settings['align'];

            $origin_library = [
                'left' => 'center left',
                'center' => 'center',
                'right' => 'center right'
            ];
            $this->add_render_attribute('wrap', 'style', 'transform-origin: ' . $origin_library[$align]);
        }

        $animation = $settings['animation'];
        $this->add_render_attribute('wrap', 'class', ['themeshark-heading', "themeshark-heading-$animation"]);

        if ($settings['use_gradient'] === 'yes') {
            $this->add_render_attribute('wrap', 'class', 'themeshark-heading--gradient');
        }
        // if ($settings['animation'] !== '-none') {
        //     $this->add_render_attribute('wrap', 'class', 'themeshark-has-scroll-effect');
        // }

        $this->add_render_attribute('title', 'class', 'themeshark-heading-text');
        $this->add_inline_editing_attributes('title');

        $title = $settings['title'];

        if (!empty($settings['link']['url'])) {
            $this->add_link_attributes('url', $settings['link']);

            $title = sprintf('<a %1$s>%2$s</a>', $this->get_render_attribute_string('url'), $title);
        }

        $title_html = sprintf('<%1$s %2$s>%3$s</%1$s>', $settings['header_size'], $this->get_render_attribute_string('title'), $title);

?>

        <div <?php $this->print_render_attribute_string('wrap'); ?>>
            <div class='themeshark-heading-text-inner-wrap'>
                <?php
                echo Helpers::esc_wysiwyg($title_html);
                ?>
            </div>
        </div>
<?php
    }
}
