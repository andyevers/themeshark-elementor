<?php

namespace Themeshark_Elementor\Widgets;

if (!defined('ABSPATH')) exit;

use \Elementor\Utils;
use \Elementor\Controls_Manager as CM;
use \Elementor\Group_Control_Image_Size;
use \Themeshark_Elementor\Inc\TS_Widget;
use \Themeshark_Elementor\Inc\Shorthand_Controls;
use \Themeshark_Elementor\Controls\Controls_Handler;
use Themeshark_Elementor\Inc\TS_Error;

require_once __DIR__ . '/hover-image-template.php';

class TS_Image_Link extends TS_Widget
{
    use Hover_Image_Template;

    const NAME = 'ts-image-link';
    const TITLE = 'Content Image';

    public static function register_styles()
    {
        self::register_template_styles();
    }

    public function get_keywords()
    {
        return self::keywords(['image link', 'link', 'image', 'hover', 'effects', 'post']);
    }

    public function get_style_depends()
    {
        return ['ts-hover-image'];
    }
    public function get_icon()
    {
        return 'tsicon-content-image';
    }


    public function section_image()
    {
        $SC = new Shorthand_Controls($this);

        $this->start_controls_section('section_image', [
            'label' => $SC::_('Image'),
        ]);

        $this->add_control_effect();

        $SC->control('image', 'Choose Image', CM::MEDIA, [
            'default' => ['url' => Utils::get_placeholder_image_src()],
            'dynamic' => ['active' => true],
        ]);

        $this->add_control_image_size();

        $SC->control('title_text', 'Title & Description', CM::TEXT, [
            'dynamic' => ['active' => true],
            'default' => $SC::_('This is title'),
            'placeholder' => $SC::_('Enter your title'),
            'label_block' => true,
            'render_type' => 'ui',
            'themeshark_settings' => [
                Controls_Handler::LINK_TEXT => [
                    'selector' => '{{WRAPPER}} .themeshark-hover-image-title'
                ]
            ]
        ]);

        $SC->control('description_text', 'Content', CM::TEXTAREA, [
            'dynamic' => ['active' => true],
            'default' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.',
            'placeholder' => $SC::_('Enter your description'),
            'separator' => 'none',
            'rows' => 6,
            'show_label' => false,
            'render_type' => 'ui',
            'themeshark_settings' => [
                Controls_Handler::LINK_TEXT => [
                    'selector' => '{{WRAPPER}} .themeshark-hover-image-description'
                ]
            ]
        ]);


        $SC->responsive_control('position', 'Alignment', CM::CHOOSE, [
            'default' => 'center',
            'options' => $SC::choice_set_h_align(['left', 'center', 'right']),
            'selectors_dictionary' => [
                'left' => 'margin-right: auto;',
                'center' => 'margin-right: auto; margin-left: auto;',
                'right' => 'margin-left: auto;'
            ],
            'selectors' => $SC::selectors([
                '.themeshark-hover-image' => [
                    '{{VALUE}}'
                ]
            ]),
            'separator' => 'before',
            'toggle' => false
        ]);


        $SC->control('link', 'Link', CM::URL, [
            'dynamic' => ['active' => true],
            'placeholder' => $SC::_('https://your-link.com'),
            'separator' => 'before',
        ]);


        $this->add_control_title_size();






        $SC->control('show_readmore', 'Show Read More', CM::SWITCHER, [
            'condition' => [$this->control_key_skin => 'card'],
            'return_value' => 'yes',
            'default' => 'yes'
        ]);

        $SC->control('readmore_text', 'Read More Text', CM::TEXT, [
            'condition' => ['show_readmore' => 'yes', $this->control_key_skin => 'card'],
            'default' => $SC::_('Read More'),
            'render_type' => 'ui',
            'themeshark_settings' => [
                Controls_Handler::LINK_TEXT => [
                    'selector' => '{{WRAPPER}} .themeshark-readmore-text'
                ]
            ]
        ]);
        $SC->control('readmore_bar_icon', 'Icon', CM::ICONS, [
            'condition' => ['show_readmore' => 'yes', $this->control_key_skin => 'card'],
            'fa4compatibility' => 'icon',
            'default' => [
                'value' => 'fas fa-arrow-right',
                'library' => 'fa-solid',
            ],
        ]);





        $this->end_controls_section();
    }


    public function register_controls()
    {
        $this->section_image();
        // $this->section_readmore();
        $this->section_effect();
        $this->section_image_style();
        $this->section_content_style();
        $this->section_readmore_bar_style();
        $this->section_border_style();
    }

    public function get_image_html($settings)
    {
        $image_html = Group_Control_Image_Size::get_attachment_image_html($settings, $this->group_image_size_name, 'image');
        return $image_html;
    }

    public function render()
    {
        $settings = $this->get_settings();
        $image_html = $this->get_image_html($settings);

        $wrap_tag = 'div';
        if (!empty($settings['link']['url'])) {
            $this->add_link_attributes($this->attribute_item_wrap, $settings['link']);
            $wrap_tag = 'a';
        }

        $this->add_default_render_attributes();

        $this->render_standard_layout($wrap_tag, $image_html, $settings['title_text'], $settings['description_text']);
    }
}
