<?php

namespace Themeshark_Elementor\Widgets;

if (!defined('ABSPATH')) exit;

use \Elementor\Controls_Manager as CM;
use \Themeshark_Elementor\Inc\TS_Widget;
use \Themeshark_Elementor\Controls\Animations;
use \Themeshark_Elementor\Inc\Shorthand_Controls;
use \Themeshark_Elementor\Controls\Controls_Handler as CH;


/**
 * ThemeShark SVG Text Widget
 *
 * Text that is created using SVG Paths. Paths have draw animation when scrolling into view.
 * Paths are defined in /assets/front-end/js/font-data
 * 
 * @since 1.0.0
 */
class TS_SVG_Text extends TS_Widget
{
    const NAME = 'ts-svg-text';
    const TITLE = 'Draw Text';


    public static function register_styles()
    {
        self::widget_style('ts-svg-text', self::get_dir_url(__DIR__, 'ts-svg-text.css'));
    }

    public static function register_scripts()
    {
        self::widget_script('ts-svg-text', self::get_dir_url(__DIR__, 'ts-svg-text.js'));
    }


    public static function localize_editor_scripts()
    {
        self::localize_script('SVG_TEXT_FONTS', self::get_font_family('_ALL_'));
    }

    public static function editor_scripts()
    {
        self::editor_script('ts-svg-text-editor', self::get_dir_url(__DIR__, 'ts-svg-text-editor.js'));
    }

    public function get_script_depends()
    {
        return ['ts-montserrat', 'ts-svg-text'];
    }
    public function get_style_depends()
    {
        return ['ts-svg-text'];
    }

    public function get_icon()
    {
        return 'tsicon-draw-text';
    }

    public function get_categories()
    {
        return ['themeshark'];
    }

    public function get_keywords()
    {
        return self::keywords(['animated', 'svg', 'text', 'draw', 'title', 'heading']);
    }

    protected function register_controls()
    {
        $SC = new Shorthand_Controls($this);

        $this->start_controls_section('section_svg_text', [
            'label' => $SC::_('Text'),
            'tab' => CM::TAB_CONTENT,
        ]);

        $SC->control('font_family', null, CM::HIDDEN, [
            'default' => 'montserrat'
        ]);

        $SC->control('text', 'Text', CM::TEXT, [
            'dynamic' => ['active' => true],
            'default' => 'ThemeShark.com',
            'render_type' => 'ui',
            'placeholder' => $SC::_('My Text'),
            'themeshark_settings' => [
                CH::LINK_ATTRIBUTE => [
                    'attribute' => 'data-text',
                    'selector' => '{{WRAPPER}} svg.themeshark-svg-text'
                ]
            ]
        ]);

        Animations::add_controls($this, [
            'animations' => $SC::options_select(
                ['draw-svg', 'Draw']
            ),
            'defaults' => [
                'animation' => 'draw-svg',
                'animation_repeat' => 'yes',
                'animation_duration_custom' => 4
            ],
            'exclude' => [
                '_animation_duration',
                'animation_direction',
                'animation_iteration_count',
                'animation_iteration_count_custom',
            ]
        ]);

        // $SC->group_control('transition_settings', Group_Control_Transition::get_type(), [
        //     'selector' => ''
        // ]);

        $this->end_controls_section();

        $this->start_controls_section('section_style', [
            'label' => $SC::_('Styles'),
            'tab'   => CM::TAB_STYLE
        ]);

        $SC->responsive_control('width', 'Width', CM::SLIDER, [
            'default'    => ['unit' => '%'],
            'size_units' => ['%', 'px', 'vw'],
            'range'      => $SC::range(['%', 1, 100], ['px', 1, 1000], ['vw', 1, 100]),
            'selectors'  => $SC::selectors([
                '.themeshark-svg-text' => [
                    'width: {{SIZE}}{{UNIT}}'
                ],
            ])
        ]);
        $SC->responsive_control('max_width', 'Max Width', CM::SLIDER, [
            'default'    => ['unit' => '%'],
            'size_units' => ['%', 'px', 'vw'],
            'range'      => $SC::range(['%', 1, 100], ['px', 1, 1000], ['vw', 1, 100]),
            'selectors'  => $SC::selectors([
                '.themeshark-svg-text' => [
                    'max-width: {{SIZE}}{{UNIT}}'
                ],
            ])
        ]);


        $SC->control('font_size', 'Font Size', CM::SLIDER, [
            'separator' => 'before',
            'range'     => $SC::range(['px', 20, 250]),
            'default'   => $SC::range_default('px', 100),
        ]);

        $SC->responsive_control('align', 'Alignment', CM::CHOOSE, [
            'default'   => 'left',
            'options'   => $SC::choice_set_text_align(['left', 'center', 'right']),
            'selectors' => $SC::selectors([
                '.themeshark-svg-text-wrapper' => [
                    'text-align: {{VALUE}}'
                ]
            ])
        ]);

        $ts_settings = [CH::NO_TRANSITION => '{{WRAPPER}} .themeshark-svg-text path'];

        $SC->control('fill', 'Fill', CM::COLOR, [
            'themeshark_settings' => $ts_settings,
            'default'             => '#000000',
            'selectors'           => $SC::selectors([
                '.themeshark-svg-text path' => [
                    'fill: {{VALUE}}'
                ]
            ])
        ]);

        $SC->control('stroke_before', 'Stroke Before', CM::COLOR, [
            'default'             => '#000000',
            'separator'           => 'before',
            'condition'           => ['animation' => 'draw-svg'],
            'themeshark_settings' => $ts_settings,
            'selectors'           => $SC::selectors([
                '.themeshark-svg-text' => [
                    '--stroke-before: {{VALUE}}'
                ]
            ])
        ]);

        $SC->control('stroke_width_before', 'Stroke Width Before', CM::SLIDER, [
            'range'               => $SC::range(['px', 0, 20]),
            'default'             => $SC::range_default('px', 3),
            'show_label'          => false,
            'condition'           => ['animation' => 'draw-svg'],
            'themeshark_settings' => $ts_settings,
            'selectors'           => $SC::selectors([
                '.themeshark-svg-text' => [
                    '--stroke-width-before: {{SIZE}}{{UNIT}}'
                ]
            ])
        ]);

        $SC->control('stroke_after', 'Stroke After', CM::COLOR, [
            'default'             => '#000000',
            'separator'           => 'before',
            'themeshark_settings' => $ts_settings,
            'selectors'           => $SC::selectors([
                '.themeshark-svg-text' => [
                    '--stroke-after: {{VALUE}}'
                ]
            ])
        ]);

        $SC->control('stroke_width_after', 'Stroke Width After', CM::SLIDER, [
            'range'               => $SC::range(['px', 0, 20]),
            'default'             => $SC::range_default('px', 3),
            'show_label'          => false,
            'themeshark_settings' => $ts_settings,
            'selectors'           => $SC::selectors([
                '.themeshark-svg-text' => [
                    '--stroke-width-after: {{SIZE}}{{UNIT}}'
                ]
            ])
        ]);

        $SC->control('stroke_linecap', 'Stroke Linecap', CM::SELECT, [
            'default'   => 'square',
            'separator' => 'before',
            'options'   => $SC::options_select(
                ['square', 'Square'],
                ['butt', 'Butt'],
                ['round', 'Round']
            ),
            'selectors' => $SC::selectors([
                '.themeshark-svg-text path' => [
                    'stroke-linecap: {{VALUE}}'
                ]
            ])
        ]);

        $SC->control('blend_mode', 'Blend Mode', CM::SELECT, [
            'options' => $SC::options_select(
                ['', 'Normal'],
                ['multiply', 'Multiply'],
                ['screen', 'Screen'],
                ['overlay', 'Overlay'],
                ['darken', 'Darken'],
                ['lighten', 'Lighten'],
                ['color-dodge', 'Color Dodge'],
                ['saturation', 'Saturation'],
                ['color', 'Color'],
                ['difference', 'Difference'],
                ['exclusion', 'Exclusion'],
                ['hue', 'Hue'],
                ['luminosity', 'Luminosity']
            ),
            'selectors' => $SC::selectors([
                '.themeshark-svg-text' => [
                    'mix-blend-mode: {{VALUE}}'
                ]
            ])
        ]);

        $this->end_controls_section();
    }

    private static $font_data_path = __DIR__ . '/svg-font-data';


    private static function get_font_json($path)
    {
        $json_data = file_get_contents($path);
        return json_decode($json_data, true);
    }

    private static function get_font_family($family = '_ALL_')
    {
        $font_folder = self::$font_data_path;
        $families = [
            'montserrat' => self::get_font_json("$font_folder/montserrat.json")
        ];
        if ($family === '_ALL_') return $families;
        return $families[$family];
    }


    protected function render()
    {
        $settings = $this->get_settings();

        $chars_data = [];
        $font_data = self::get_font_family($settings['font_family']);
        foreach (str_split($settings['text']) as $char) {
            $path_data = $font_data[$char];
            $chars_data[$char] = $path_data;
        }

        $this->add_render_attribute('svg', [
            'class' => ['themeshark-svg-text'],
            'xmlns' => 'http://www.w3.org/2000/svg',
            'width' => 0,
            'height' => $settings['font_size']['size'],
            'viewBox' => '0 0 0 0',
            'data-lineheight' => $settings['font_size']['size'],
            'data-fontsize' => $settings['font_size']['size'],
            'data-fontfamily' => 'montserrat',
            'data-duration' => $settings['animation_duration_custom'],
            'data-text' => $settings['text'],
            'data-charpaths' => json_encode($chars_data)
        ]);

        $svg_open = '<svg ' . $this->get_render_attribute_string('svg') . '>';
        $defs = '<defs><style></style></defs>';
        $desc = '<desc>' . $settings['text'] . '</desc>';
        $svg_close = '</svg>';
        $svg_html = "$svg_open\n$defs\n$desc\n$svg_close";
?>
        <div class="themeshark-svg-text-wrapper">
            <?php echo $svg_html; ?>
        </div>
    <?php
    }


    protected function content_template()
    {
    ?>
        <# view.addRenderAttribute('svg', { 'class' : 'themeshark-svg-text' , 'xmlns' : 'http://www.w3.org/2000/svg' , 'width' : 0, 'height' : settings.font_size.size, 'viewBox' : '0 0 0 0' , 'data-lineheight' : settings.font_size.size, 'data-fontsize' : settings.font_size.size, 'data-fontfamily' : 'montserrat' , 'data-duration' : settings.animation_duration_custom, 'data-text' : settings.text, 'style' : 'animation-duration:' + settings.animation_duration_custom + 's' }); var svg_open='<svg ' + view.getRenderAttributeString('svg') + '>' ; var defs='<defs><style></style></defs>' ; var desc='<desc>' + settings.text + '</desc>' ; var svg_close='</svg>' ; var svg_html=svg_open + defs + desc + svg_close; #>

            <div class="themeshark-svg-text-wrapper">
                {{{svg_html}}}
            </div>
    <?php
    }
}
