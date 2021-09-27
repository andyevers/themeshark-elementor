<?php

namespace Themeshark_Elementor\Widgets;

use \Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use \Themeshark_Elementor\Inc\TS_Widget;
use \Elementor\Controls_Manager as CM;
use Elementor\Group_Control_Background;
use \Elementor\Group_Control_Image_Size;
use \Elementor\Group_Control_Border;
use \Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Css_Filter;
use \Elementor\Group_Control_Typography;
use Themeshark_Elementor\Inc\Shorthand_Controls;

use Elementor\Utils;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}


class TS_Gallery extends TS_Widget
{

    const NAME = 'ts-gallery';
    const TITLE = 'ThemeShark Gallery';

    public static function register_styles()
    {
        self::widget_style('ts-gallery', self::get_dir_url(__DIR__, 'ts-gallery.css'));
    }

    public function get_style_depends()
    {
        return ['ts-gallery'];
    }

    public function get_icon()
    {
        return 'tsicon-themeshark-gallery';
    }

    public function get_keywords()
    {
        return self::keywords(['image', 'photo', 'visual', 'gallery']);
    }

    protected function register_controls()
    {
        $SC = new Shorthand_Controls($this);

        $this->start_controls_section('section_gallery',  [
            'label' => $SC::_('Image Gallery'),
        ]);

        $SC->control('wp_gallery', 'Add Images', CM::GALLERY, [
            'show_label' => false,
            'dynamic' => ['active' => true],
        ]);

        $SC->control('gallery_layout', 'Layout', CM::SELECT, [
            'default' => 'grid',
            'options' => $SC::options_select(
                ['grid', 'Grid'],
                ['justified', 'Justified']
                // ['masonry', 'Masonry']
            ),
            'prefix_class' => 'themeshark-gallery-',
            'render_type' => 'template'
        ]);

        $gallery_columns = range(1, 10);
        $gallery_columns = array_combine($gallery_columns, $gallery_columns);

        $SC->responsive_control('columns', 'Columns', CM::SELECT, [
            'condition' => ['gallery_layout!' => 'justified'],
            'default' => 4,
            'options' => $gallery_columns,
            'selectors' => $SC::selectors([
                '.themeshark-gallery' => [
                    '--columns: {{VALUE}}'
                ]
            ])
        ]);


        $SC->responsive_control('image_height', 'Height', CM::SLIDER, [
            'range' => $SC::range(['px', 50, 600]),
            'default' => $SC::range_default('px', 240),
            'condition' => ['gallery_layout' => 'justified'],
            'selectors' => $SC::selectors([
                '.themeshark-gallery-item-image' => [
                    'height: {{SIZE}}{{UNIT}};'
                ]
            ])
        ]);

        $SC->responsive_control('image_gap', 'Spacing', CM::SLIDER, [
            'range' => $SC::range(['px', 0, 100]),
            'default' => $SC::range_default('px', 10),
            'selectors' => $SC::selectors([
                '.themeshark-gallery' => [
                    '--gap: {{SIZE}}{{UNIT}}'
                ]
            ])
        ]);

        $SC->control('aspect_ratio', 'Aspect Ratio', CM::SELECT, [
            'condition' => ['gallery_layout' => 'grid'],
            'render_type' => 'template',
            'default' => '3:2',
            'options' => [
                '1:1' => '1:1',
                '3:2' => '3:2',
                '4:3' => '4:3',
                '9:16' => '9:16',
                '16:9' => '16:9',
                '21:9' => '21:9',
            ],
        ]);

        $SC->group_control('thumbnail', Group_Control_Image_Size::get_type(), [
            'exclude' => ['custom'],
            'separator' => 'none',
            'default' => 'medium',
        ]);

        $SC->control('use_lightbox', 'Lightbox', CM::SELECT, [
            'default' => 'yes',
            'options' => $SC::options_select(
                ['yes', 'Yes'],
                ['no', 'No']
            ),
        ]);


        $SC->control('overlay_title', 'Title', CM::SELECT, [
            'default' => '',
            'separator' => 'before',
            'options' => $SC::options_select(
                ['', 'None'],
                ['title', 'Title'],
                ['caption', 'Caption'],
                ['alt', 'Alt'],
                ['description', 'Description']
            )
        ]);

        $this->end_controls_section();









        $this->start_controls_section('section_gallery_images', [
            'label' => $SC::_('Image'),
            'tab' => CM::TAB_STYLE,
        ]);

        $this->start_controls_tabs('image_style_tabs');

        $this->start_controls_tab('image_style_normal', [
            'label' => $SC::_('Normal')
        ]);

        $SC->group_control('image_border', Group_Control_Border::get_type(), [
            'selector' => '{{WRAPPER}} .themeshark-gallery-item'
        ]);

        $SC->responsive_control('image_border_radius', 'Border Radius', CM::DIMENSIONS, [
            'size_units' => ['px', '%'],
            'selectors' => $SC::selectors([
                '.themeshark-gallery-item' => [
                    'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}'
                ]
            ])
        ]);

        $SC->group_control('image_box_shadow', Group_Control_Box_Shadow::get_type(), [
            'selector' => '{{WRAPPER}} .themeshark-gallery-item'
        ]);

        $SC->group_control('image_css_filters', Group_Control_Css_Filter::get_type(), [
            'selector' => '{{WRAPPER}} .themeshark-gallery-item'
        ]);

        $this->end_controls_tab();
        $this->start_controls_tab('image_style_hover', [
            'label' => $SC::_('Hover')
        ]);

        $SC->group_control('image_border_hover', Group_Control_Border::get_type(), [
            'selector' => '{{WRAPPER}} .themeshark-gallery-item:hover'
        ]);

        $SC->group_control('image_box_shadow_hover', Group_Control_Box_Shadow::get_type(), [
            'selector' => '{{WRAPPER}} .themeshark-gallery-item:hover'
        ]);


        $SC->responsive_control('image_border_radius_hover', 'Border Radius', CM::DIMENSIONS, [
            'size_units' => ['px', '%'],
            'selectors' => $SC::selectors([
                '.themeshark-gallery-item:hover' => [
                    'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}'
                ]
            ])
        ]);


        $SC->group_control('image_css_filters_hover', Group_Control_Css_Filter::get_type(), [
            'selector' => '{{WRAPPER}} .themeshark-gallery-item:hover'
        ]);


        $this->end_controls_tab();
        $this->end_controls_tabs();


        $SC->control('image_hover_animation', 'Hover Animation', CM::SELECT, [
            'groups'             => $SC::get_hover_effect_groups(['move', 'zoom']),
            'separator'          => 'before',
            'frontend_available' => true,
            'render_type'        => 'template',
        ]);

        $SC->control('image_animation_duration', 'Animation Duration (ms)', CM::SLIDER, [
            'range'     => $SC::range(['px', 0, 3000]),
            'default'   => $SC::range_default('px', 800),
            'selectors' => $SC::selectors([
                '.themeshark-gallery-item-image' => [
                    'transition-duration: {{SIZE}}ms',
                ]
            ])
        ]);


        $this->end_controls_section();

        $this->start_controls_section('section_gallery_overlay', [
            'label' => $SC::_('Overlay'),
            'tab' => CM::TAB_STYLE,
        ]);


        $overlay = '.themeshark-gallery-item-overlay';

        $this->start_controls_tabs('overlay_style_tabs');

        $this->start_controls_tab('overlay_style_normal', [
            'label' => $SC::_('Normal')
        ]);
        $SC->group_control('overlay_background', Group_Control_Background::get_type(), [
            'selector' => "{{WRAPPER}} $overlay",
            'exclude' => ['image'],
        ]);

        $this->end_controls_tab();

        $this->start_controls_tab('overlay_style_hover', [
            'label' => $SC::_('Hover')
        ]);

        $SC->group_control('overlay_background_hover', Group_Control_Background::get_type(), [
            'selector' => "{{WRAPPER}} .themeshark-gallery-item:hover $overlay",
            'exclude' => ['image'],
            'fields_options' => [
                'background' => ['default' => 'classic'],
                'color' => ['default' => 'rgba(0,0,0,.5)']
            ]
        ]);

        $this->end_controls_tab();

        $this->end_controls_tabs();


        $SC->control('overlay_blend_mode', 'Blend Mode', CM::SELECT, [
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
                'luminosity' => 'Luminosity',
            ],
            'selectors' => $SC::selectors([
                $overlay => [
                    'mix-blend-mode: {{VALUE}}'
                ]
            ])
        ]);


        $SC->control('background_overlay_hover_animation', 'Hover Animation', CM::SELECT, [
            'separator' => 'before',
            'default' => '',
            'frontend_available' => true,
            'render_type' => 'template',
            'options' => array_merge($SC::options_select(['', 'None']), $SC::get_hover_effect_groups('enter')),
            // 'options' => $SC::get_hover_effect_groups('enter'),
        ]);

        $SC->control('overlay_animation_duration', 'Animation Duration (ms)', CM::SLIDER, [
            'range' => $SC::range(['px', 0, 3000]),
            'default' => $SC::range_default('px', 800),
            'render_type' => 'ui',
            'selectors' => $SC::selectors([
                '.themeshark-gallery-item-overlay' => [
                    'transition: {{SIZE}}ms'
                ]
            ])
        ]);

        $this->end_controls_section();




        $this->start_controls_section('item_content_style', [
            'label' => $SC::_('Content'),
            'tab' => CM::TAB_STYLE,
        ]);

        $SC->control('content_alignment', 'Alignment', CM::CHOOSE, [
            'options'   => $SC::choice_set_text_align(['left', 'center', 'right']),
            'default'   => 'center',
            'selectors' => $SC::selectors([
                '.themeshark-gallery-item-text' => [
                    'text-align: {{VALUE}}'
                ]
            ])
        ]);


        $SC->control('content_vertical_position', 'Vertical Position', CM::CHOOSE, [
            'options' => $SC::choice_set_v_align(['top', 'center', 'bottom']),
            'selectors_dictionary' => [
                'top' => 'margin-top: 0px; margin-bottom: auto;',
                'center' => 'margin-top: auto; margin-bottom:auto;',
                'bottom' => 'margin-top: auto; margin-bottom:0px;'
            ],
            'selectors' => $SC::selectors([
                '.themeshark-gallery-item-text' => [
                    '{{VALUE}}'
                ]
            ])
        ]);

        $SC->responsive_control('content_padding', 'Padding', CM::DIMENSIONS, [
            'size_units' => ['px', 'em', '%'],
            'default' => ['size' => 20,],
            'selectors' => $SC::selectors([
                '.themeshark-gallery-item-text' => [
                    'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}'
                ]
            ])
        ]);

        $SC->control('heading_title', 'Title', CM::HEADING, [
            'separator' => 'before',
            'condition' => ['overlay_title!' => ''],
        ]);

        $SC->control('title_color', 'Color', CM::COLOR, [
            'condition' => ['overlay_title!' => ''],
            'default' => '#fff',
            'selectors' => $SC::selectors([
                '.themeshark-gallery-item-text' => [
                    'color: {{VALUE}}'
                ]
            ]),
        ]);

        $SC->group_control('title_typography', Group_Control_Typography::get_type(), [
            'global' => ['default' => Global_Typography::TYPOGRAPHY_PRIMARY],
            'selector' => '{{WRAPPER}} .themeshark-gallery-item-text',
            'condition' => ['overlay_title!' => '',],
        ]);

        $SC->control('content_hover_animation', 'Hover Animation', CM::SELECT, [
            'options' => array_merge($SC::options_select(['', 'None']), $SC::get_hover_effect_groups('fade-in')),
            'separator' => 'before',
            'frontend_available' => true,
            'render_type' => 'template'
        ]);

        $SC->control('content_animation_duration', 'Animation Duration (ms)', CM::SLIDER, [
            'range' => $SC::range(['px', 0, 3000]),
            'default' => $SC::range_default('px', 800),
            'selectors' => $SC::selectors([
                '.themeshark-gallery-item-content' => [
                    'transition: {{SIZE}}ms'
                ]
            ])
        ]);

        $this->end_controls_section(); // overlay_content
    }


    protected function get_masonry_columns($column_count = 4, $image_ids)
    {
        $column_heights = $column_images = []; //create column keys
        for ($i = 0; $i < $column_count; $i++) {
            $column_heights[$i] = 0;
            $column_images[$i] = [];
        }

        foreach ($image_ids as $image_id) {
            $min_height_col = array_keys($column_heights, min($column_heights));
            $image_data = $this->get_image_data($image_id);
            $image_height_adjusted = $this->get_aspect_ratio_percent($image_data['aspect_ratio']);

            foreach ($column_heights as $col_key => $col_height) {
                if (in_array($col_key, $min_height_col)) {
                    $column_images[$col_key][] = $image_id;
                    $column_heights[$col_key] += $image_height_adjusted;
                    break;
                }
            }
        }

        return $column_images;
    }

    protected function add_lightbox_data_atts($render_att, $image_id)
    {
        $lightbox_group_id = 'all-' . $this->get_id();
        $this->add_lightbox_data_attributes($render_att, $image_id, 'yes', $lightbox_group_id);
    }


    protected function get_aspect_ratio_percent($ratio)
    {
        $ratio = explode(':', $ratio);
        $percent = intval($ratio[1]) / intval($ratio[0]);
        return $percent;
    }


    protected function get_image_data($image_id)
    {
        $attachment = get_post($image_id);
        $image_src = wp_get_attachment_image_src($image_id, $this->get_settings('thumbnail_size'));
        $image_data = [
            'alt' => get_post_meta($attachment->ID, '_wp_attachment_image_alt', true),
            'media' => wp_get_attachment_image_src($image_id, 'full')['0'],
            'src' => $image_src['0'],
            'width' => $image_src['1'],
            'height' => $image_src['2'],
            'caption' => $attachment->post_excerpt,
            'description' => $attachment->post_content,
            'title' => $attachment->post_title,
            'aspect_ratio' => $image_src['1'] . ':' . $image_src['2'],
            'id' => $image_id
        ];
        return $image_data;
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();

        $use_lightbox = $settings['use_lightbox'] === 'yes';

        if (!$settings['wp_gallery']) return;

        $this->add_render_attribute('themeshark_gallery', ['class' => ['themeshark-gallery']]);

        $image_ids = wp_list_pluck($settings['wp_gallery'], 'id');

        $layout = $settings['gallery_layout'];

        foreach ($image_ids as $image_id) {


            $image_data = $this->get_image_data($image_id);

            //___GALLERY ITEM WRAP___//
            $item_render_att = "gallery_item_$image_id";

            $href = $image_data['media'];
            $src = $image_data['src'];

            $this->add_render_attribute($item_render_att, [
                'class' => ['themeshark-gallery-item', 'ts-hover-effect'],
            ]);


            if ($use_lightbox) {
                $this->add_render_attribute($item_render_att, 'href', $href);
                $this->add_lightbox_data_atts($item_render_att, $image_id);
            }

            //___GALLERY ITEM IMAGE___//
            $image_render_att = "gallery_item_image_$image_id";
            $this->add_render_attribute($image_render_att, [
                'class' => 'themeshark-gallery-item-image',
                'style' => "background-image: url('$src');",
            ]);

            //___SET ASPECT RATIO___//

            $aspect_ratio_percent = $this->get_aspect_ratio_percent(
                $layout === 'grid' ? $settings['aspect_ratio'] : $image_data['aspect_ratio']
            );

            if ($layout === 'justified') {
                $this->add_render_attribute($item_render_att, 'style', "--ratio: $aspect_ratio_percent;");
            } else {
                $padding_percent = $aspect_ratio_percent * 100 . '%';
                $this->add_render_attribute($image_render_att, 'style', "padding-bottom: $padding_percent;");
            }
        }

?>
        <div <?php $this->print_render_attribute_string('themeshark_gallery'); ?>>
            <?php

            if ($layout === 'masonry') {
                //MASONRY LAYOUT
                $columns = $this->get_masonry_columns($settings['columns'], $image_ids);
                foreach ($columns as $column_image_ids) : ?>

                    <div class='themeshark-gallery-column'>
                        <?php foreach ($column_image_ids as $image_id) {
                            $this->render_gallery_item($image_id);
                        } ?>
                    </div>

            <?php //STANDARD LAYOUT
                endforeach;
            } else {
                foreach ($image_ids as $image_id) {
                    $this->render_gallery_item($image_id);
                }
            } ?>
        </div>
    <?php

    }

    private function render_gallery_item($image_id)
    {
        $settings = $this->get_settings();
        $SC = new Shorthand_Controls($this);
        $use_lightbox = $settings['use_lightbox'] === 'yes';
        $tag = $use_lightbox ? 'a' : 'div';
        $image_data = $this->get_image_data($image_id);
        $text_source = $this->get_settings('overlay_title');
        $text = isset($image_data[$text_source]) ? $image_data[$text_source] : '';

        $this->add_render_attribute("gallery_overlay_$image_id", 'class', 'themeshark-gallery-item-overlay');
        $this->add_render_attribute("gallery_content_$image_id", 'class', 'themeshark-gallery-item-content');

        $SC->ensure_hover_effect_attribute("gallery_overlay_$image_id", $settings['background_overlay_hover_animation']);
        $SC->ensure_hover_effect_attribute("gallery_item_image_$image_id", $settings['image_hover_animation']);
        $SC->ensure_hover_effect_attribute("gallery_content_$image_id", $settings['content_hover_animation']);

    ?>
        <<?php echo Utils::validate_html_tag($tag)  . ' ' . $this->get_render_attribute_string("gallery_item_$image_id"); ?>>
            <div <?php $this->print_render_attribute_string("gallery_item_image_$image_id"); ?>>
            </div>

            <div <?php $this->print_render_attribute_string("gallery_overlay_$image_id"); ?>>
            </div>

            <?php if (!empty($text)) : ?>
                <div <?php $this->print_render_attribute_string("gallery_content_$image_id"); ?>>
                    <div class='themeshark-gallery-item-text'>
                        <?php esc_html_e($text); ?>
                    </div>
                </div>
            <?php endif; ?>
        </<?php echo Utils::validate_html_tag($tag); ?>>
<?php
    }
}
