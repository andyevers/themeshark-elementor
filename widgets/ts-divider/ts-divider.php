<?php

namespace Themeshark_Elementor\Widgets;

if (!defined('ABSPATH'))  exit;

use \Elementor\Controls_Manager as CM;
use \Elementor\Group_Control_Typography;
use \Themeshark_Elementor\Inc\TS_Widget;
use \Themeshark_Elementor\Controls\Animations;
use \Themeshark_Elementor\Inc\Shorthand_Controls;
use \Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use \Themeshark_Elementor\Controls\Controls_Handler as CH;
use \Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use \Themeshark_Elementor\Controls\Group_Control_Transform;
use Elementor\Utils;

/**
 * TS Divider
 *
 * Elementor widget that displays a line that divides different elements in the
 * page.
 *
 * @since 1.0.0
 */
class TS_Divider extends TS_Widget
{
    public static function register_styles()
    {
        self::widget_style('ts-divider', self::get_dir_url(__DIR__, 'ts-divider.css'));
    }

    const NAME = 'ts-divider';
    const TITLE = 'Effects Divider';


    public function get_style_depends()
    {
        return ['ts-divider'];
    }

    public function get_script_depends()
    {
        return ['ts-divider'];
    }


    public function get_icon()
    {
        return 'tsicon-effects-divider';
    }

    public function get_categories()
    {
        return ['themeshark'];
    }


    public function get_keywords()
    {
        return self::keywords(['divider', 'hr', 'line', 'border']);
    }

    public function _register_controls()
    {
        $SC = new Shorthand_Controls($this);

        $this->start_controls_section('section_divider', [
            'label' => $SC::_('Divider')
        ]);

        $SC->control('style', 'Style', CM::SELECT, [
            'frontend_available' => true,
            'prefix_class' => 'themeshark-divider-style-',
            'default' => '-line-circle',
            'render_type' => 'template',
            'options' => $SC::options_select(
                ['-normal', 'Normal'],
                ['-line-circle', 'Line Circle']
            )
        ]);


        $SC->responsive_control('width', 'Width', CM::SLIDER, [
            'condition' => ['flip_vertical!' => '-flip-vertical'],
            'size_units' => ['%', 'px', 'vw'],
            'range' => $SC::range(['px', 0, 1000], ['%', 0, 100], ['vw', 0, 100]),
            'default' => $SC::range_default('px', 300),
            'selectors' => $SC::selectors([
                '.themeshark-divider' => [
                    '--width: {{SIZE}}{{UNIT}}'
                ]
            ])
        ]);

        $SC->responsive_control('vertical_height', 'Height', CM::SLIDER, [
            'condition' => ['flip_vertical' => '-flip-vertical'],
            'size_units' => ['px', 'vw'],
            'range' => $SC::range(['px', 0, 1000], ['vw', 0, 100]),
            'default' => $SC::range_default('px', 300),
            'selectors' => $SC::selectors([
                '.themeshark-divider' => [
                    '--width: {{SIZE}}{{UNIT}}'
                ]
            ])
        ]);

        $SC->responsive_control('align', 'Alignment', CM::CHOOSE, [
            'render_type' => 'ui',
            'options' => $SC::options_choose(
                ['left', 'Left', 'eicon-text-align-left'],
                ['center', 'Center', 'eicon-text-align-center'],
                ['right', 'Right', 'eicon-text-align-right']
            ),
            'selectors_dictionary' => [
                'left' => 'left:0px;',
                'center' => 'left: 50%; --translate-x: -50%;',
                'right' => 'right:0px;',
            ],
            'selectors' => $SC::selectors([
                '.themeshark-divider-inner' => [
                    '{{VALUE}}'
                ]
            ])
        ]);


        $SC->control('flip_vertical', 'Flip Vertical', CM::SWITCHER, [
            'label_on' => $SC::_('Yes'),
            'label_off' => $SC::_('No'),
            'return_value' => '-flip-vertical',
            'prefix_class' => 'themeshark-divider-'
        ]);


        $SC->control('look', 'Add Text', CM::CHOOSE, [
            'separator' => 'before',
            'toggle' => false,
            'default' => 'line',
            'options' => $SC::options_choose(
                ['line', 'None', 'eicon-ban'],
                ['line_text', 'Text', 'eicon-t-letter-bold']
            )
        ]);

        $SC->control('element_alignment', 'Circle/Text Alignment', CM::CHOOSE, [
            'toggle' => false,
            'render_type' => 'ui',
            'default' => 'right',
            'conditions' => [
                'relation' => 'or',
                'terms' => [
                    $SC::cond_term('look', '!=', 'line'),
                    $SC::cond_term('style', '==', '-line-circle'),
                ]
            ],
            'options' => $SC::options_choose(
                ['left', 'Left', 'eicon-h-align-left'],
                ['right', 'Right', 'eicon-h-align-right']
            ),
            'selectors_dictionary' => [
                'left' => '
                --border-radius: var(--height) 0px 0px var(--height); 
                --circle-left: var(--circle-offset); 
                --circle-right:auto; 
                --text-left: auto; 
                --text-right: var(--text-offset); 
                --text-margin: 0px var(--text-spacing) 0px 0px; 
                --text-margin-vert: 0px 0px var(--text-spacing) 0px; 
                --border-radius-vert: var(--height) var(--height) 0px 0px;
                --text-padding: var(--text-short-offset) var(--text-far-offset) var(--text-short-offset) 0;
                --text-padding-vert: 0 var(--text-short-offset) var(--text-far-offset) var(--text-short-offset);',

                'right' => '
                --border-radius: 0px var(--height) var(--height) 0px; 
                --circle-right: var(--circle-offset); 
                --circle-left:auto; 
                --text-right: auto; 
                --text-left: var(--text-offset); 
                --text-margin: 0px 0px 0px var(--text-spacing); 
                --text-margin-vert: var(--text-spacing) 0px 0px 0px; 
                --border-radius-vert: 0px 0px var(--height) var(--height);
                --text-padding: var(--text-short-offset) 0 var(--text-short-offset) var(--text-far-offset);
                --text-padding-vert: var(--text-far-offset) var(--text-short-offset) 0 var(--text-short-offset);'
            ],
            'selectors' => $SC::selectors([
                '.themeshark-divider' => [
                    '{{VALUE}}'
                ]
            ])
        ]);

        $SC->control('text_tag', 'HTML Tag', CM::SELECT, [
            'condition' => ['look' => 'line_text'],
            'default' => 'span',
            'render_type' => 'ui',
            'themeshark_settings' => [
                CH::LINK_REPLACE_TAG => [
                    'selector' => '.themeshark-divider-text'
                ],
                CH::NO_TRANSITION => '{{WRAPPER}}, {{WRAPPER}} .themeshark-divider-inner, {{WRAPPER}} .themeshark-divider-text',
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
            ],
        ]);

        $SC->control('text', 'Text', CM::TEXT, [
            'condition' => ['look' => 'line_text'],
            'dynamic' => ['active' => true],
            'default' => $SC::_('Divider'),
            'render_type' => 'ui',
            'themeshark_settings' => [
                CH::LINK_TEXT => [
                    'selector' => '{{WRAPPER}} .themeshark-divider-text'
                ]
            ]
        ]);

        $SC->control('icon', 'Icon', CM::ICONS, [
            'condition' => ['look' => 'line_icon'],
            'default' => [
                'value' => 'fas fa-star',
                'library' => 'fa-solid',
            ],
        ]);


        $this->end_controls_section();


        $this->start_controls_section('section_divider_style', [
            'label' => $SC::_('Divider'),
            'tab' => CM::TAB_STYLE,
        ]);

        $SC->control('color', 'Color', CM::COLOR, [
            'global' => ['default' => Global_Colors::COLOR_ACCENT],
            'selectors' => $SC::selectors([
                '.themeshark-divider' => [
                    '--background-color: {{VALUE}}'
                ]
            ])
        ]);


        $SC->control('weight', 'Weight', CM::SLIDER, [
            'range' => $SC::range(['px', 1, 15]),
            'default' => $SC::range_default('px', 5),
            'selectors' => $SC::selectors([
                '.themeshark-divider' => [
                    '--height: {{SIZE}}{{UNIT}}'
                ]
            ])
        ]);

        $SC->responsive_control('gap', 'Gap', CM::SLIDER, [
            'range' => $SC::range(['px', 2, 50]),
            'default' => $SC::range_default('px', 15),
            'selectors' => $SC::selectors([
                '.themeshark-divider' => [
                    'margin-top: {{SIZE}}{{UNIT}}',
                    'margin-bottom: {{SIZE}}{{UNIT}}'
                ]
            ])
        ]);


        $SC->control('heading_circle', 'Circle', CM::HEADING, [
            'condition' => ['style' => '-line-circle'],
            'separator' => 'before'
        ]);


        $SC->control('circle_color', 'Color', CM::COLOR, [
            'condition' => ['style' => '-line-circle'],
            'selectors' => $SC::selectors([
                '.themeshark-divider-inner .themeshark-divider-circle' => [
                    'background-color: {{VALUE}}'
                ]
            ])
        ]);

        $SC->responsive_control('circle_diameter', 'Diameter', CM::SLIDER, [
            'condition' => ['style' => '-line-circle'],
            'range' => $SC::range(['px', 2, 50]),
            'default' => $SC::range_default('px', 15),
            'selectors' => $SC::selectors([
                '.themeshark-divider' => [
                    '--diameter: {{SIZE}}{{UNIT}}'
                ]
            ])
        ]);


        $SC->control('transform_divider', null, CM::DIVIDER);

        $SC->group_control('transform', Group_Control_Transform::get_type(), [
            'selector' => '{{WRAPPER}} .themeshark-divider',
        ]);

        $this->end_controls_section();

        $this->start_controls_section('section_text_style', [
            'tab' => CM::TAB_STYLE,
            'condition' => ['look' => 'line_text'],
            'label' => $SC::_('Text'),
        ]);


        $SC->control('text_conlor', 'Color', CM::COLOR, [
            'global' => ['default' => Global_Colors::COLOR_SECONDARY],
            'selectors' => $SC::selectors([
                '.themeshark-divider-text' => [
                    'color:{{VALUE}}'
                ]
            ])
        ]);

        $SC->group_control('typography', Group_Control_Typography::get_type(), [
            'global' => ['default' => Global_Typography::TYPOGRAPHY_SECONDARY],
            'selector' => '{{WRAPPER}} .themeshark-divider-text',
        ]);


        $SC->control('text_spacing', 'Spacing', CM::SLIDER, [
            'range' => $SC::range(['px', 1, 30]),
            'default' => $SC::range_default('px', 1),
            'selectors' => $SC::selectors([
                '.themeshark-divider' => [
                    '--text-spacing: {{SIZE}}{{UNIT}}'
                ]
            ])
        ]);


        $this->end_controls_section();


        $this->start_controls_section('section_divider_animations', [
            'label' => $SC::_('Animation'),
            'tab' => CM::TAB_STYLE
        ]);

        Animations::add_controls($this, [
            'animations' => $SC::options_select(
                ['themeshark-divider-effect--expand', 'Expand']
            ),
            'alternative_selectors' => $SC::options_select(
                ['.themeshark-divider-inner', 'Divider Bar'],
                ['.themeshark-divider-text', 'Divider Text'],
                ['.themeshark-divider-circle', 'Divider Circle']
            ),
            'defaults' => [
                'animation' => 'themeshark-divider-effect--expand',
                'animation_repeat' => 'yes'
            ]
        ]);


        $SC->control('transform_origin', 'Animation Origin', CM::CHOOSE, [
            'condition' => ['animation!' => ''],
            'toggle' => false,
            'render_type' => 'ui',
            'separator' => 'before',
            'options' => $SC::options_choose(
                ['left', 'Left', 'eicon-h-align-left'],
                ['center', 'Center', 'eicon-h-align-center'],
                ['right', 'Right', 'eicon-h-align-right']
            ),
            'themeshark_settings' => [
                CH::RESET_WRAPPER_CLASS => 'animated'
            ],
            'selectors_dictionary' => [
                'left' => '--hor-transform-origin: left; --vert-transform-origin: top;',
                'center' => '--hor-transform-origin: center; --vert-transform-origin: center;',
                'right' => '--hor-transform-origin: right; --vert-transform-origin: bottom;',
            ],
            'selectors' => $SC::selectors([
                '.themeshark-divider' => [
                    '{{VALUE}}'
                ]
            ]),
        ]);


        $this->end_controls_section();


        $this->start_controls_section('section_icon_style', [
            'label' => __('Icon', THEMESHARK_TXTDOMAIN),
            'tab' => CM::TAB_STYLE,
            'condition' => ['look' => 'line_icon'],
        ]);

        $SC->control('icon_view', 'View', CM::SELECT, [
            'default' => 'default',
            'options' => $SC::options_select(
                ['default', 'Default'],
                ['stacked', 'Stacked'],
                ['framed', 'Framed']
            )
        ]);


        $this->end_controls_section();
    }

    public function render()
    {
        $settings = $this->get_settings_for_display();

        $direction = 'normal';
        if ($settings['flip_vertical'] === '-flip-vertical') {
            $direction = $settings['element_alignment'] === 'left' ? 'up' : 'down';
        }

        $this->add_render_attribute('wrapper', [
            'class' => ['themeshark-divider',],
            'data-orientation' => $direction
        ]);
?>

        <div <?php $this->print_render_attribute_string('wrapper'); ?>>
            <div class='themeshark-divider-inner'>

                <?php if ($settings['style'] === '-line-circle') : ?>
                    <div class='themeshark-divider-circle'></div>
                <?php endif; ?>

                <?php if ($settings['look'] === 'line_text') : ?>
                    <<?php echo Utils::validate_html_tag($settings['text_tag']); ?> class='themeshark-divider-text'>
                        <?php esc_html_e($settings['text']); ?>
                    </<?php echo Utils::validate_html_tag($settings['text_tag']); ?>>
                <?php endif; ?>

            </div>
        </div>
    <?php
    }


    public function content_template()
    { ?>
        <# function getScrollSettingsStrings(groupName){ var prefix=groupName + '_' ; var toggle_string='yes' ; var toggle_key=[prefix + 'toggle' ]; var margin_top=settings[prefix + 'margin_top' ]; var margin_bottom=settings[prefix + 'margin_bottom' ]; var margin_top_string='0px' ; var margin_bottom_string='0px' ; if(Object.keys(settings).includes(toggle_key)){ toggle=settings[toggle_key]; } if(margin_top){ margin_top_string=margin_top['size'] + margin_top['unit']; } if(margin_bottom){ margin_bottom_string=margin_bottom['size'] + margin_bottom['unit']; } return (JSON.stringify({ 'toggle' : toggle_string, 'margin_top' : margin_top_string, 'margin_bottom' : margin_bottom_string })); } var direction='normal' ; if(settings.flip_vertical==='yes' ){ if(settings.element_alignment==='left' ){ direction='up' ; }else{ direction='down' ; } } view.addRenderAttribute('wrapper', { 'class' : [ 'themeshark-divider' , 'themeshark-divider-style-' + settings.style ], 'data-scroll-settings' : getScrollSettingsStrings('scroll_settings'), 'data-orientation' : direction }); var scroll_settings=settings.scroll_settings; var title_html='<' + settings.text_tag + ' class="themeshark-divider-text">' ; title_html +=settings.text; title_html +='</' + settings.text_tag + '>' ; if(settings.flip_vertical==='yes' ){ view.addRenderAttribute('wrapper', 'class' , 'themeshark-divider--flip-vertical' ); } #>

            <div {{{view.getRenderAttributeString( 'wrapper' )}}}>
                <div class='themeshark-divider-inner'>
                    <# if(settings.style==='-line-circle' ){ #>
                        <div class='themeshark-divider-circle'></div>
                        <# } #>
                            <# if(settings.look==='line_text' ){ #>
                                {{{title_html}}}
                                <# } #>
                </div>
            </div>
    <?php
    }
}
