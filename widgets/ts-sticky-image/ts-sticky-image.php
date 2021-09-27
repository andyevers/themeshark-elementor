<?php

namespace Themeshark_Elementor\Widgets;

if (!defined('ABSPATH')) exit;

use \Elementor\Utils;
use \Elementor\Group_Control_Border;
use \Elementor\Controls_Manager as CM;
use \Elementor\Group_Control_Css_Filter;
use \Elementor\Group_Control_Box_Shadow;
use \Themeshark_Elementor\Inc\Shorthand_Controls;
use \Themeshark_Elementor\Controls\Controls_Handler as CH;
use \Themeshark_Elementor\Controls\Group_Control_Transition;
use \Themeshark_Elementor\Inc\TS_Widget;
use Themeshark_Elementor\Controls\Group_Control_Transform;

class TS_Image extends TS_Widget
{

    const NAME = 'ts-sticky-image';
    const TITLE = 'Sticky Styles Image';

    public static function register_styles()
    {
        self::widget_style('ts-sticky-image', self::get_dir_url(__DIR__, 'ts-sticky-image.css'));
    }

    public function get_style_depends()
    {
        return ['ts-sticky-image'];
    }

    public function get_icon()
    {
        return 'tsicon-sticky-styles-image';
    }

    public function get_categories()
    {
        return ['themeshark'];
    }

    public function get_keywords()
    {
        return self::keywords(['image ', 'photo', 'sticky', 'styles']);
    }

    protected function register_controls()
    {
        $SC = new Shorthand_Controls($this);

        $images = '.themeshark-image img'; // standard and sticky image

        $this->start_controls_section('section_image', [
            'label' => $SC::_('Image'),
        ]);

        $this->start_controls_tabs('images');

        // IMAGE NORMAL
        $this->start_controls_tab('image_tab_normal', ['label' => $SC::_('Noraml')]);

        $SC->control('image', 'Choose Image', CM::MEDIA, [
            'dynamic' => ['active' => true],
            'default' => ['url' => Utils::get_placeholder_image_src()],
            'render_type' => 'template'
        ]);

        // $SC->group_control('image', Group_Control_Image_Size::get_type(), ['default' => 'large']);
        $SC->add_image_size_control('image', ['default' => 'large']);
        $this->end_controls_tab();

        //IMAGE STICKY
        $this->start_controls_tab('image_tab_sticky', ['label' => $SC::_('Sticky')]);
        $SC->sticky_duplicate_control('image_sticky', 'image', ['default' => ['url' => '']]);
        $SC->add_image_size_control('image_sticky', ['default' => 'large'], [], true);
        // $SC->sticky_group_control('image_sticky', Group_Control_Image_Size::get_type(), ['default' => 'large']);
        $this->end_controls_tab();

        $this->end_controls_tabs();


        $SC->responsive_control('align', 'Alignment', CM::CHOOSE, [
            'options' => $SC::choice_set_text_align(['left', 'center', 'right']),
            'separator' => 'before',
            'selectors' => $SC::selectors([
                '.themeshark-image-inner' => [
                    'text-align: {{VALUE}}'
                ],
            ])
        ]);

        $SC->control('link_to', 'Link', CM::SELECT, [
            'default' => 'none',
            'options' => $SC::options_select(
                ['none', 'None'],
                ['file', 'Media File'],
                ['custom', 'Custom URL']
            )
        ]);

        $SC->control('link', 'Link', CM::URL, [
            'dynamic' => ['active' => true],
            'placeholder' => 'https://your-link.com',
            'condition' => ['link_to' => 'custom'],
            'show_label' => false,
        ]);
        $SC->control('open_lightbox', 'Lightbox', CM::SELECT, [
            'condition' => ['link_to' => 'file'],
            'default' => 'default',
            'options' => $SC::options_select(
                ['default', 'Default'],
                ['yes', 'Yes'],
                ['no', 'No']
            )
        ]);


        $this->end_controls_section();

        $this->start_controls_section('section_style_image', [
            'label' => $SC::_('Image'),
            'tab'   => CM::TAB_STYLE,
        ]);

        $SC->control('object_fit', 'Object Cover', CM::SWITCHER, [
            'label' => $SC::_('Object Cover'),
            'return_value' => 'yes',
            'default' => '',
            'description' => $SC::_('Crops image to prevent distortion when setting both width and height properties.'),
            'selectors_dictionary' => ['yes' => 'object-fit: cover'],
            'selectors' => $SC::selectors([
                $images => [
                    '{{VALUE}}'
                ]
            ])
        ]);


        $SC->responsive_control('object_position', 'Object Position', CM::CHOOSE, [
            'condition' => ['object_fit' => 'yes'],
            'themeshark_settings' => [CH::NO_TRANSITION => true],
            'default' => 'center',
            'options' => $SC::choice_set_v_align(['top', 'center', 'bottom']),
            'selectors' => $SC::selectors([
                'img' => ['object-position: {{VALUE}}']
            ])
        ]);


        $SC->control('blend_mode', 'Blend Mode', CM::SELECT, [
            'options' => [
                ''            => $SC::_('Normal'),
                'multiply'    => 'Multiply',
                'screen'      => 'Screen',
                'overlay'     => 'Overlay',
                'darken'      => 'Darken',
                'lighten'     => 'Lighten',
                'color-dodge' => 'Color Dodge',
                'saturation'  => 'Saturation',
                'color'       => 'Color',
                'difference'  => 'Difference',
                'exclusion'   => 'Exclusion',
                'hue'         => 'Hue',
                'luminosity'  => 'Luminosity',
            ],

            'selectors' => $SC::selectors([
                '{{WRAPPER}}' => [
                    'mix-blend-mode: {{VALUE}};'
                ],
            ], null, false),
        ]);

        $this->start_controls_tabs('dimensions');
        $this->start_controls_tab('dimensions_normal', [
            'label' => $SC::_('Normal')
        ]);

        //-------------------------//
        //----- NORMAL STYLES -----//
        //-------------------------//

        //transition settings
        $group_image_transition_settings = [
            'selector' => '{{WRAPPER}} .themeshark-image img, {{WRAPPER}} .themeshark-image-inner'
        ];
        $SC->group_control('image_transition', Group_Control_Transition::get_type(), $group_image_transition_settings);

        $group_image_transform_settings = [
            'selector' => '{{WRAPPER}} .themeshark-image-inner'
        ];
        $SC->group_control('image_transform', Group_Control_Transform::get_type(), $group_image_transform_settings);

        $WIDTH_SETTINGS = [
            'default' => ['unit' => '%'],
            'size_units' => ['%', 'px', 'vw'],
            'themeshark_settings' => [CH::NO_TRANSITION => true],
            'range' => $SC::range(['%', 1, 100], ['px', 1, 1000], ['vw', 1, 100]),
        ];

        $SC->responsive_control('width', 'Width', CM::SLIDER, array_merge([
            'separator' => 'before',
            'selectors' => $SC::selectors([
                $images => [
                    'width: {{SIZE}}{{UNIT}}'
                ],
            ])
        ], $WIDTH_SETTINGS));

        $SC->responsive_control('max_width', 'Max Width', CM::SLIDER, array_merge([
            'selectors' => $SC::selectors([
                $images => [
                    'max-width: {{SIZE}}{{UNIT}}'
                ]
            ])
        ], $WIDTH_SETTINGS));

        $SC->responsive_control('height', 'Height', CM::SLIDER, [
            'size_units' => ['px', 'vh'],
            'range' => $SC::range(['px', 1, 1000], ['vh', 1, 100]),
            'themeshark_settings' => [CH::NO_TRANSITION => true],
            'selectors' => $SC::selectors([
                $images => [
                    'height: {{SIZE}}{{UNIT}}'
                ]
            ])
        ]);

        //opacity
        $SC->control('opacity', 'Opacity', CM::SLIDER, [
            'separator' => 'before',
            'range' => $SC::range(['px', 0, 1, 0.01]),
            'selectors' => $SC::selectors([
                '.themeshark-image-inner .themeshark-image-default img' => [
                    'opacity: {{SIZE}}'
                ]
            ])
        ]);

        //css filters
        $group_css_filters_settings = [
            'selector' => '{{WRAPPER}} .themeshark-image img'
        ];
        $SC->group_control('css_filters', Group_Control_Css_Filter::get_type(), $group_css_filters_settings);

        $this->end_controls_tab();

        //-------------------------//
        //----- STICKY STYLES -----//
        //-------------------------//

        $this->start_controls_tab('dimensions_hover', [
            'label' => $SC::_('Sticky')
        ]);

        $SC->sticky_group_control('image_transition_sticky', Group_Control_Transition::get_type(), $group_image_transition_settings);
        $SC->sticky_group_control('image_transform_sticky', Group_Control_Transform::get_type(), $group_image_transform_settings);
        $SC->sticky_duplicate_control('width_sticky', 'width');
        $SC->sticky_duplicate_control('max_width_sticky', 'max_width');
        $SC->sticky_duplicate_control('height_sticky', 'height');
        $SC->sticky_duplicate_control('opacity_sticky', 'opacity');
        $SC->sticky_group_control('css_filters_sticky', Group_Control_Css_Filter::get_type(), $group_css_filters_settings);

        $this->end_controls_tab();
        $this->end_controls_tabs();
        $this->end_controls_section();


        //SECTION BORDER
        $this->start_controls_section('section_style_border', [
            'label' => $SC::_('Border'),
            'tab'   => CM::TAB_STYLE,
        ]);

        $this->start_controls_tabs('border');
        $this->start_controls_tab('border_normal', [
            'label' => $SC::_('Normal')
        ]);

        // image border
        $group_border_settings = [
            'selector' => '{{WRAPPER}} .themeshark-image img',
            'fields_options' => ['color' => ['themeshark_settings' => [CH::NO_TRANSITION => true]]]
        ];
        $SC->group_control('image_border', Group_Control_Border::get_type(), $group_border_settings);

        //border radius
        $SC->responsive_control('image_border_radius', 'Border Radius', CM::DIMENSIONS, [
            'size_units' => ['px', '%'],
            'selectors' => $SC::selectors([
                $images => [
                    'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}'
                ]
            ]),
        ]);

        //box shadow
        $group_image_box_shadow_settings = [
            'exclude' => ['box_shadow_position'],
            'selector' => "{{WRAPPER}} $images",
        ];
        $SC->group_control('image_box_shadow', Group_Control_Box_Shadow::get_type(), $group_image_box_shadow_settings);

        $this->end_controls_tab();

        //STICKY TABS
        $this->start_controls_tab('border_sticky', [
            'label' => $SC::_('Sticky')
        ]);

        $SC->sticky_group_control('image_border_sticky', Group_Control_Border::get_type(), $group_border_settings);
        $SC->sticky_duplicate_control('image_border_radius_sticky', 'image_border_radius');
        $SC->sticky_group_control('image_box_shadow_sticky', Group_Control_Box_Shadow::get_type(), $group_image_box_shadow_settings);

        $this->end_controls_tab();
        $this->end_controls_tabs();
        $this->end_controls_section();
    }


    /**
     * Render image widget output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @since 1.0.0
     * @access protected
     */
    protected function render()
    {
        $SC = new Shorthand_Controls($this);
        $settings = $this->get_settings_for_display();

        if (empty($settings['image']['url'])) {
            return;
        }

        $this->add_render_attribute('wrapper', 'class', 'themeshark-image');

        $has_sticky_image = !empty($settings['image_sticky']['id']);

        if ($has_sticky_image) {
            $this->add_render_attribute('wrapper', 'class', 'themeshark-image--has-sticky-image');
        }

        $link = $this->get_link_url($settings);

        if ($link) {
            $this->add_link_attributes('link', $link);

            if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
                $this->add_render_attribute('link', [
                    'class' => 'elementor-clickable',
                ]);
            }

            if ('custom' !== $settings['link_to']) {
                $this->add_lightbox_data_attributes('link', $settings['image']['id'], $settings['open_lightbox']);
            }
        } ?>


        <div <?php $this->print_render_attribute_string('wrapper'); ?>>


            <div class='themeshark-image-inner'>
                <?php if ($link) : ?>
                    <a <?php $this->print_render_attribute_string('link'); ?>>
                    <?php endif; ?>
                    <span class='themeshark-image-default'>
                        <?php echo $SC->get_image_html('image'); ?>
                    </span>
                    <?php if ($has_sticky_image) : ?>
                        <span class='themeshark-image-sticky'>
                            <?php echo $SC->get_image_html('image_sticky'); ?>
                        </span>
                    <?php endif; ?>
                    <?php if ($link) : ?>

                    </a>

                <?php endif; ?>
            </div>
        </div>
    <?php
    }

    protected function content_template()
    {
    ?>
        <# if (settings.image.url) { var image={ id: settings.image.id, url: settings.image.url, size: settings.image_size, dimension: settings.image_custom_dimension, model: view.getEditModel() }; var image_sticky={ id: settings.image_sticky.id, url: settings.image_sticky.url, size: settings.image_sticky_size, dimension: settings.image_sticky_custom_dimension, model: view.getEditModel() }; var image_url=elementor.imagesManager.getImageUrl(image); var image_sticky_url=elementor.imagesManager.getImageUrl(image_sticky); if (!image_url) { return; } if (settings.image_sticky.id) { view.addRenderAttribute('wrapper', 'class' , 'themeshark-image--has-sticky-image' ); } var link_url; if ('custom'===settings.link_to) { link_url=settings.link.url; } if ('file'===settings.link_to) { link_url=settings.image.url; } view.addRenderAttribute('wrapper', { class: ['themeshark-image'], }); #>

            <div {{{view.getRenderAttributeString( 'wrapper' )}}}>

                <div class='themeshark-image-inner'>
                    <# if ( link_url ) { #>
                        <a class="elementor-clickable" data-elementor-open-lightbox="{{ settings.open_lightbox }}" href="{{ link_url }}">
                            <# } #>

                                <span class='themeshark-image-default'>
                                    <img src="{{ image_url }}" />
                                </span>

                                <# if(settings.image_sticky.id) { #>
                                    <span class='themeshark-image-sticky'>
                                        <img src="{{image_sticky_url}}" />
                                    </span>
                                    <# } #>

                </div>
                <# if ( link_url ) { #>
                    </a>
                    <# } #>
            </div>

            <# } #>

        <?php
    }

    private function get_link_url($settings)
    {
        if ('none' === $settings['link_to']) {
            return false;
        }

        if ('custom' === $settings['link_to']) {
            if (empty($settings['link']['url'])) {
                return false;
            }

            return $settings['link'];
        }

        return [
            'url' => $settings['image']['url'],
        ];
    }
}
