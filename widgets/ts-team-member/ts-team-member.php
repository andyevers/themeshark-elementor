<?php

namespace Themeshark_Elementor\Widgets;

if (!defined('ABSPATH')) exit;

use \Elementor\Utils;
use \Elementor\Repeater;
use \Elementor\Controls_Manager as CM;
use \Elementor\Group_Control_Typography;
use \Themeshark_Elementor\Inc\Shorthand_Controls;
use \Themeshark_Elementor\Controls\Controls_Handler as CH;
use \Themeshark_Elementor\Inc\TS_Widget;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Css_Filter;
use \Themeshark_Elementor\Widgets\TS_Team_Member\Skins;
use Themeshark_Elementor\Inc\Helpers;

if (!defined('ABSPATH')) exit;


class TS_Team_Member extends TS_Widget
{
    const NAME = 'ts-team-member';
    const TITLE = 'Team Member';

    public static function register_styles()
    {
        self::widget_style('ts-team-member', self::get_dir_url(__DIR__, 'ts-team-member.css'));
    }

    public function get_icon()
    {
        return 'tsicon-team-member';
    }

    private $SC = null;

    /**
     * @return \Themeshark_Elementor\Inc\Shorthand_Controls
     */
    private function get_SC()
    {
        if (is_null($this->SC)) {
            $this->SC = new Shorthand_Controls($this);
        }
        return $this->SC;
    }


    public function get_style_depends()
    {
        return ['ts-team-member'];
    }

    public function get_keywords()
    {
        return self::keywords(['staff', 'employee', 'team', 'bio']);
    }


    public function _register_skins()
    {
        $skins_dir = __DIR__ . '/skins';
        require_once "$skins_dir/skin-slide-content.php";

        $this->add_skin(new Skins\Skin_Slide($this));
    }


    public function add_section_content()
    {
        $SC = $this->get_SC();
        $this->start_controls_section('section_content', [
            'label' => $SC::_('Content')
        ]);

        $SC->control('photo', 'Photo', CM::MEDIA, [
            'dynamic' => ['active' => true],
            'default' => ['url' => Utils::get_placeholder_image_src()]
        ]);

        $SC->add_image_size_control('photo');

        $SC->control('name', 'Name', CM::TEXT, [
            'placeholder' => $SC::_('Name'),
            'default' => $SC::_('John Smith'),
            'dynamic' => ['active' => true],
            'render_type' => 'ui',
            'themeshark_settings' => [
                CH::LINK_TEXT => [
                    'selector' => '{{WRAPPER}} .themeshark-team-member-name'
                ]
            ]
        ]);

        $SC->control('name_tag', 'Name HTML Tag', CM::SELECT, [
            'default' => 'h3',
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
            'render_type' => 'ui',
            'themeshark_settings' => [
                CH::LINK_REPLACE_TAG => [
                    'selector' => '{{WRAPPER}} .themeshark-team-member-name'
                ]
            ]
        ]);


        $SC->control('position', 'Position', CM::TEXT, [
            'separator' => 'before',
            'placeholder' => $SC::_('Position'),
            'default' => $SC::_('President'),
            'dynamic' => ['active' => true],
            'render_type' => 'ui',
            'themeshark_settings' => [
                CH::LINK_TEXT => [
                    'selector' => '{{WRAPPER}} .themeshark-team-member-position'
                ]
            ]
        ]);

        $SC->control('description', 'Description', CM::TEXTAREA, [
            'placeholder' => $SC::_('Description'),
            'default' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam.',
            'dynamic' => ['active' => true],
            'rows' => 8,
            'render_type' => 'ui',
            'themeshark_settings' => [
                CH::LINK_TEXT => [
                    'selector' => '{{WRAPPER}} .themeshark-team-member-description'
                ]
            ]
        ]);

        $SC->control('show_social_icons', 'Show Social Icons', CM::SWITCHER, [
            'return_value' => 'yes',
            'default' => 'yes'
        ]);

        $this->end_controls_section();
    }

    public function add_section_social_repeater()
    {
        //------------------------------//
        //--------- SOCIAL ICONS -------//
        //------------------------------//
        $SC = $this->get_SC();
        $this->start_controls_section('section_content_social_link', [
            'condition' => ['show_social_icons' => 'yes'],
            'label' => $SC::_('Social Icon'),

        ]);

        $repeater = new Repeater();
        $SCR = new Shorthand_Controls($repeater);

        $SCR->control('link', 'Link', CM::URL, [
            'default'     => ['url' => ''],
            'placeholder' => 'https://your-link.com/'
        ]);

        $SCR->control('icon', 'Icon', CM::ICONS, [
            'fa4compatibility' => 'social',
            'default' => [
                'value' => 'fab fa-facebook',
                'library' => 'fa-brands',
            ],
            'recommended' => [
                'fa-brands' => $SC::get_recommended_social_brands(),
                'fa-solid' => [
                    'envelope',
                    'link',
                    'rss',
                ],
            ]
        ]);

        $SC->control('social_repeater', null, CM::REPEATER, [
            'title_field' => '<# var migrated = "undefined" !== typeof __fa4_migrated, social = ( "undefined" === typeof social ) ? false : social; #>{{{ elementor.helpers.getSocialNetworkNameFromIcon( icon, social, true, migrated, true ) }}}',
            'fields'  => $repeater->get_controls(),
            'default' => [[
                'link' => 'https://themeshark.com/',
                'icon' => ['value' => 'fab fa-facebook-f', 'library' => 'fa-brands'],
            ], [
                'link' => 'https://themeshark.com/',
                'icon' => ['value' => 'fab fa-twitter', 'library' => 'fa-brands'],
            ], [
                'link' => 'https://themeshark.com/',
                'icon' => ['value' => 'fab fa-linkedin-in', 'library' => 'fa-brands'],
            ]],
        ]);

        $this->end_controls_section();
    }

    public function add_section_container_style()
    {

        //------------------------------//
        //------ CONTAINER STYLE -------//
        //------------------------------//
        $SC = $this->get_SC();
        $this->start_controls_section('section_container_style', [
            'label' => $SC::_('Container'),
            'tab' => CM::TAB_STYLE,
        ]);

        $container = '.themeshark-team-member';


        $SC->control('container_align', 'Container Align', CM::CHOOSE, [
            'options' => $SC::choice_set_h_align(['left', 'center', 'right']),
            'selectors_dictionary' => [
                'left' => 'margin-right: auto;',
                'center' => 'margin-left:auto; margin-right: auto;',
                'right' => 'margin-left:auto'
            ],
            'selectors' => $SC::selectors([
                $container => [
                    '{{VALUE}}'
                ]
            ])
        ]);

        $SC->control('container_text_align', 'Text Align', CM::CHOOSE, [
            'options' => $SC::choice_set_text_align(['left', 'center', 'right']),
            'selectors_dictionary' => [
                'left' => 'justify-content: flex-start; text-align:left; margin-right:auto;',
                'center' => 'justify-content: center; text-align:center; margin-left:auto; margin-right:auto;',
                'right' => 'justify-content: flex-end; text-align:right; margin-left:auto;'
            ],
            'selectors' => $SC::selectors([
                "$container, .themeshark-team-member-social-wrap, .themeshark-team-member-image-wrap" => [
                    '{{VALUE}}'
                ]
            ])
        ]);

        $SC->responsive_control('container_width', 'Width', CM::SLIDER, [
            'separator' => 'before',
            'size_units' => ['px', '%'],
            'range' => $SC::range(['px', 1, 1000], ['%', 1, 100]),
            'selectors' => $SC::selectors([
                $container => [
                    'width: {{SIZE}}{{UNIT}}'
                ]
            ])
        ]);
        $SC->responsive_control('container_max_width', 'Max Width', CM::SLIDER, [
            'size_units' => ['px', '%'],
            'range' => $SC::range(['px', 1, 1000], ['%', 1, 100]),
            'selectors' => $SC::selectors([
                $container => [
                    'max-width: {{SIZE}}{{UNIT}}'
                ]
            ])
        ]);

        $SC->control('container_background_color', 'Background Color', CM::COLOR, [
            'separator' => 'before',
            'selectors' => $SC::selectors([
                $container => [
                    'background-color: {{VALUE}}'
                ]
            ])
        ]);


        $SC->responsive_control('container_padding', 'Padding', CM::DIMENSIONS, [
            'size_units' => ['px', '%'],
            'selectors' => $SC::selectors([
                $container => [
                    '--container-padding-top: {{TOP}}{{UNIT}}',
                    '--container-padding-right: {{RIGHT}}{{UNIT}}',
                    '--container-padding-bottom: {{BOTTOM}}{{UNIT}}',
                    '--container-padding-left: {{LEFT}}{{UNIT}}',
                ]
            ])
        ]);


        $SC->group_control('container_border', Group_Control_Border::get_type(), [
            'selector' => "{{WRAPPER}} $container",
            'separator' => 'before'
        ]);

        $SC->responsive_control('container_border_radius', 'Border Radius', CM::DIMENSIONS, [
            'size_units' => ['px', '%'],
            'selectors' => $SC::selectors([
                $container => [
                    'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}'
                ]
            ])
        ]);


        $SC->group_control('container_box_shadow', Group_Control_Box_Shadow::get_type(), [
            'selector' => "{{WRAPPER}} $container",
        ]);


        $this->end_controls_section();
    }

    public function add_section_photo_style()
    {
        //------------------------------//
        //--------- PHOTO STYLE --------//
        //------------------------------//
        $SC = $this->get_SC();
        $this->start_controls_section('section_photo_style', [
            'label' => $SC::_('Photo'),
            'tab' => CM::TAB_STYLE,
        ]);

        $image_wrap = '.themeshark-team-member-image-wrap';
        $image_wrap_inner = '.themeshark-team-member-image-wrap-inner';
        $image = "$image_wrap img";

        $SC->control('object_position', 'Image Fit Position', CM::CHOOSE, [
            'default' => 'center',
            'options' => $SC::choice_set_v_align(['top', 'center', 'bottom']),
            'selectors' => $SC::selectors([
                $image => [
                    'object-position: {{VALUE}}'
                ]
            ])
        ]);

        $SC->responsive_control('photo_width', 'Width', CM::SLIDER, [
            'size_units' => ['px', '%'],
            'range'      => $SC::range(['px', 1, 1000], ['%', 1, 100]),
            'selectors'  => $SC::selectors([
                $image_wrap => [
                    'width: {{SIZE}}{{UNIT}}'
                ]
            ])
        ]);

        $SC->responsive_control('photo_max_width', 'Max Width', CM::SLIDER, [
            'size_units' => ['px', '%'],
            'range'      => $SC::range(['px', 1, 1000], ['%', 1, 100]),
            'selectors'  => $SC::selectors([
                $image_wrap => [
                    'max-width: {{SIZE}}{{UNIT}}'
                ]
            ])
        ]);


        $SC->responsive_control('photo_height', 'Height', CM::SLIDER, [
            'condition'  => ['_skin!' => ''],
            'size_units' => ['px'],
            'range'      => $SC::range(['px', 1, 1000]),
            'selectors'  => $SC::selectors([
                $image => [
                    'height: {{SIZE}}{{UNIT}}'
                ]
            ])
        ]);


        $this->start_controls_tabs('photo_tabs');

        //___NORMAL___//
        $this->start_controls_tab('photo_tab_normal', [
            'label' => $SC::_('Normal')
        ]);

        $SC->group_control('photo_css_filters', Group_Control_Css_Filter::get_type(), [
            'selector' => "{{WRAPPER}} $image_wrap_inner"
        ]);


        $SC->control('overlay_color', 'Overlay Color', CM::COLOR, [
            'condition' => ['_skin!' => ''],
            'selectors' => $SC::selectors([
                '.themeshark-team-member-image-overlay' => [
                    'background-color: {{VALUE}};'
                ]
            ])
        ]);

        $SC->control('overlay_opacity', 'Overlay Opacity', CM::SLIDER, [
            'condition' => ['_skin!' => ''],
            'range' => $SC::range(['px', 0, 1, 0.01]),
            'selectors' => $SC::selectors([
                '.themeshark-team-member-image-overlay' => [
                    'opacity: {{SIZE}};'
                ]
            ])
        ]);

        $this->end_controls_tab();


        //___HOVER___//
        $this->start_controls_tab('photo_tab_hover', [
            'label' => $SC::_('Hover')
        ]);

        $SC->group_control('photo_css_filters_hover', Group_Control_Css_Filter::get_type(), [
            'selector' => "{{WRAPPER}} .themeshark-team-member:hover $image_wrap_inner"
        ]);

        $SC->control('overlay_color_hover', 'Overlay Color', CM::COLOR, [
            'condition' => ['_skin!' => ''],
            'default' => '#000',
            'selectors' => $SC::selectors([
                '.themeshark-team-member:hover .themeshark-team-member-image-overlay' => [
                    'background-color: {{VALUE}};'
                ]
            ])
        ]);

        $SC->control('overlay_opacity_hover', 'Overlay Opacity', CM::SLIDER, [
            'condition' => ['_skin!' => ''],
            'range' => $SC::range(['px', 0, 1, 0.01]),
            'default' => $SC::range_default('px', 0.5),
            'selectors' => $SC::selectors([
                '.themeshark-team-member:hover .themeshark-team-member-image-overlay' => [
                    'opacity: {{SIZE}};'
                ]
            ])
        ]);


        $this->end_controls_tab();
        $this->end_controls_tabs();


        $SC->responsive_control('photo_spacing', 'Spacing', CM::SLIDER, [
            'size_units' => ['px'],
            'separator' => 'before',
            'default' => $SC::range_default('px', 5),
            'selectors' => $SC::selectors([
                $image_wrap => [
                    'margin-bottom: {{SIZE}}{{UNIT}}'
                ]
            ])
        ]);

        $this->end_controls_section();
    }

    public function add_section_content_style()
    {


        //------------------------------//
        //------- CONTENT STYLE --------//
        //------------------------------//
        $SC = $this->get_SC();
        $this->start_controls_section('section_content_style', [
            'label' => $SC::_('Content'),
            'tab' => CM::TAB_STYLE,
        ]);


        //___NAME___//
        $SC->control('heading_name', 'Name', CM::HEADING);

        $name = '.themeshark-team-member-name';

        $SC->control('name_color', 'Color', CM::COLOR, [
            'selectors' => $SC::selectors([
                $name => [
                    'color: {{VALUE}}'
                ]
            ])
        ]);

        $SC->group_control('name_typography', Group_Control_Typography::get_type(), [
            'selector' => "{{WRAPPER}} $name"
        ]);

        $SC->responsive_control('name_spacing', 'Spacing', CM::SLIDER, [
            'range' => $SC::range(['px', 0, 100]),
            'default' => $SC::range_default('px', 5),
            'selectors' => $SC::selectors([
                $name => [
                    'margin-bottom: {{SIZE}}{{UNIT}}'
                ]
            ])
        ]);

        //___POSITION___//
        $SC->control('heading_position', 'Position', CM::HEADING, [
            'separator' => 'before'
        ]);

        $position = '.themeshark-team-member-position';

        $SC->control('position_color', 'Color', CM::COLOR, [
            'selectors' => $SC::selectors([
                $position => [
                    'color: {{VALUE}}'
                ]
            ])
        ]);

        $SC->group_control('position_typography', Group_Control_Typography::get_type(), [
            'selector' => "{{WRAPPER}} $position"
        ]);

        $SC->responsive_control('position_spacing', 'Spacing', CM::SLIDER, [
            'range' => $SC::range(['px', 0, 100]),
            'default' => $SC::range_default('px', 5),
            'selectors' => $SC::selectors([
                $position => [
                    'margin-bottom: {{SIZE}}{{UNIT}}'
                ]
            ])
        ]);


        $description = '.themeshark-team-member-description';

        $SC->control('heading_description', 'Description', CM::HEADING, [
            'separator' => 'before'
        ]);
        $SC->control('description_color', 'Color', CM::COLOR, [
            'selectors' => $SC::selectors([
                $description => [
                    'color: {{VALUE}}'
                ]
            ])
        ]);

        $SC->group_control('description_typography', Group_Control_Typography::get_type(), [
            'selector' => "{{WRAPPER}} $description"
        ]);

        $SC->responsive_control('description_spacing', 'Spacing', CM::SLIDER, [
            'range' => $SC::range(['px', 0, 100]),
            'default' => $SC::range_default('px', 15),
            'selectors' => $SC::selectors([
                $description => [
                    'margin-bottom: {{SIZE}}{{UNIT}}'
                ]
            ])
        ]);



        //___CONTENT___//
        $details = '.themeshark-team-member-details';
        $content = '.themeshark-team-member-content';


        $SC->responsive_control('content_padding', 'Padding', CM::DIMENSIONS, [
            'size_units' => ['px', 'em', '%'],
            'separator' => 'before',
            'selectors' => $SC::selectors([
                $content => [
                    'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}'
                ]
            ])
        ]);


        $this->end_controls_section();
    }

    public function add_section_social_style()
    {
        $SC = $this->get_SC();

        $this->start_controls_section('section_social_style', [
            'condition' => ['show_social_icons' => 'yes'],
            'label'     => $SC::_('Icons'),
            'tab'       => CM::TAB_STYLE,
        ]);

        $SC->control('icon_color', 'Color', CM::SELECT, [
            'default' => 'default',
            'options' => $SC::options_select(
                ['default', 'Official Color'],
                ['custom', 'Custom']
            )
        ]);

        $this->start_controls_tabs('icon_colors', [
            'condition' => ['icon_color' => 'custom'],
        ]);
        $this->start_controls_tab('icon_colors_normal', [
            'condition' => ['icon_color' => 'custom'],
            'label' => $SC::_('Normal')
        ]);
        $SC->control('icon_primary_color', 'Primary Color', CM::COLOR, [
            'condition' => ['icon_color' => 'custom'],
            'selectors' => $SC::selectors([
                '.elementor-social-icon' => [
                    'background-color: {{VALUE}};',
                ]
            ]),
        ]);

        $SC->control('icon_secondary_color', 'Secondary Color', CM::COLOR, [
            'condition' => ['icon_color' => 'custom'],
            'default' => '#fff',
            'selectors' => $SC::selectors([
                '.elementor-social-icon i' => [
                    'color: {{VALUE}}'
                ],
                '.elementor-social-icon svg' => [
                    'fill: {{VALUE}}'
                ]
            ])
        ]);

        $this->end_controls_tab();

        $this->start_controls_tab('icon_colors_hover', [
            'condition' => ['icon_color' => 'custom'],
            'label' => $SC::_('Hover')
        ]);

        $SC->control('icon_primary_color_hover', 'Primary Color', CM::COLOR, [
            'condition' => ['icon_color' => 'custom'],
            'selectors' => $SC::selectors([
                '.elementor-social-icon:hover' => [
                    'background-color: {{VALUE}};',
                ]
            ]),
        ]);

        $SC->control('icon_secondary_color_hover', 'Secondary Color', CM::COLOR, [
            'condition' => ['icon_color' => 'custom'],
            'selectors' => $SC::selectors([
                '.elementor-social-icon:hover i' => [
                    'color: {{VALUE}}'
                ],
                '.elementor-social-icon:hover svg' => [
                    'fill: {{VALUE}}'
                ]
            ])
        ]);

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $SC->responsive_control('icon_size', 'Size', CM::SLIDER, [
            'range' => $SC::range(['px', 6, 300]),
            'separator' => 'before',
            'selectors' => $SC::selectors([
                '.themeshark-team-member-social-wrap' => [
                    '--icon-size: {{SIZE}}{{UNIT}}'
                ]
            ])
        ]);

        $SC->responsive_control('icon_padding', 'Padding', CM::SLIDER, [
            'range' => $SC::range(['em', 0, 5]),
            'default' => ['unit' => 'em'],
            'tablet_default' => ['unit' => 'em'],
            'mobile_default' => ['unit' => 'em'],
            'selectors' => $SC::selectors([
                '.elementor-social-icon' => [
                    '--icon-padding: {{SIZE}}{{UNIT}}'
                ]
            ])
        ]);


        $SC->responsive_control('icon_spacing', 'Gap', CM::SLIDER, [
            'range' => $SC::range(['px', 0, 50]),
            'default' => $SC::range_default('px', 5),
            'selectors' => $SC::selectors([
                '.themeshark-team-member-social-wrap' => [
                    'gap: {{SIZE}}{{UNIT}}'
                ]
            ])
        ]);

        $SC->group_control('image_border', Group_Control_Border::get_type(), [
            'selector' => '{{WRAPPER}} .themeshark-social-icon',
            'separator' => 'before',
        ]);

        $SC->control('border_radius', 'Border Radius', CM::DIMENSIONS, [
            'size_units' => ['px', '%'],
            'selectors' => $SC::selectors([
                '.themeshark-social-icon' => [
                    'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
                ]
            ]),
        ]);

        $this->end_controls_section();
    }


    protected function register_controls()
    {
        $this->add_section_content();
        $this->add_section_social_repeater();
        $this->add_section_container_style();
        $this->add_section_photo_style();
        $this->add_section_content_style();
        $this->add_section_social_style();
    }

    public function render_image_wrap($include_overlay = false)
    {
        $SC = $this->get_SC();
?>
        <div <?php $this->print_render_attribute_string($this->attribute_image_wrap); ?>>
            <?php if ($include_overlay) : ?>
                <div class='themeshark-team-member-image-overlay'></div>
            <?php endif; ?>
            <div <?php $this->print_render_attribute_string($this->attribute_image_wrap_inner); ?>>
                <?php echo $SC->get_image_html('photo'); ?>
            </div>
        </div>
    <?php
    }

    public function render_name()
    {
        $settings = $this->get_settings();
        $name_tag = $settings['name_tag']; ?>
        <<?php echo Utils::validate_html_tag($name_tag); ?> class='themeshark-team-member-name'>
            <?php esc_html_e($settings['name']); ?>
        </<?php echo Utils::validate_html_tag($name_tag); ?>>
    <?php
    }

    public function render_position()
    { ?>
        <div class='themeshark-team-member-position'><?php esc_html_e($this->get_settings('position')); ?></div>
    <?php
    }

    public function render_description()
    { ?>
        <p class='themeshark-team-member-description'><?php echo Helpers::esc_wysiwyg($this->get_settings('description')); ?></p>
    <?php
    }

    public function render_social_icons()
    {
        $SC = $this->get_SC();
        $social_icons = $this->get_settings('social_repeater');
    ?>
        <div <?php $this->print_render_attribute_string($this->attribute_social_wrap); ?>>

            <?php for ($i = 0; $i < sizeof($social_icons); $i++) : $icon_group = $social_icons[$i]; ?>
                <div <?php $this->print_render_attribute_string($this->attribute_social_icon); ?> style='--index: <?php esc_attr_e($i); ?>;'>
                    <div <?php $this->print_render_attribute_string($this->attribute_social_icon_inner); ?>>
                        <?php $SC->render_social_icon($icon_group['icon'], $icon_group['link']); ?>
                    </div>
                </div>
            <?php endfor; ?>
        </div>

        <?php
        //Icons won't init automatically in edit mode, this loads them.
        if (\Elementor\Plugin::$instance->editor->is_edit_mode()) : ?>
            <script class='_ts_enqueue_font_script'>
                (function() {
                    let container = elementor.getContainer('<?php echo $this->get_id(); ?>')
                    let social_repeater = container.model.attributes.settings.attributes.social_repeater
                    let libraries = Array.from(new Set(social_repeater.models.map(model => model.attributes.icon.library)))
                    libraries.forEach(library => elementor.helpers.enqueueIconFonts(library))
                    jQuery('._ts_enqueue_font_script').remove()
                })()
            </script>
        <?php endif;
    }

    public $attribute_wrap = 'wrap';
    public $attribute_image_wrap = 'image_wrap';
    public $attribute_image_wrap_inner = 'image_wrap_inner';
    public $attribute_content = 'content';
    public $attribute_social_wrap = 'social_wrap';
    public $attribute_social_icon_inner = 'social_icon_inner';
    public $attribute_details = 'details';
    public $attribute_social_icon = 'social_icon';

    public function add_default_render_attributes()
    {
        $settings = $this->get_settings();
        $skin = strlen($settings['_skin']) > 0 ? $settings['_skin'] : 'default';

        $this->add_render_attribute($this->attribute_wrap, ['class' => [
            'themeshark-team-member',
            'themeshark-team-member--skin-' . $skin,
            'ts-hover-effect'
        ]]);

        $this->add_render_attribute($this->attribute_image_wrap, 'class', 'themeshark-team-member-image-wrap');
        $this->add_render_attribute($this->attribute_image_wrap_inner, 'class', 'themeshark-team-member-image-wrap-inner');
        $this->add_render_attribute($this->attribute_content, 'class', 'themeshark-team-member-content');
        $this->add_render_attribute($this->attribute_social_wrap, 'class', 'themeshark-team-member-social-wrap');
        $this->add_render_attribute($this->attribute_details, 'class', 'themeshark-team-member-details');
        $this->add_render_attribute($this->attribute_social_icon_inner, 'class', 'themeshark-social-icon-inner');
        $this->add_render_attribute($this->attribute_social_icon, 'class', 'themeshark-social-icon');
    }

    public $hover_control_key_image = 'photo_hover_animation';
    public $hover_control_key_content = 'content_hover_animation';
    public $hover_control_key_social = 'social_hover_animation';

    protected function render()
    {
        $settings = $this->get_settings();
        $this->add_default_render_attributes();
        $show_social_icons = $settings['show_social_icons'] === 'yes';
        ?>
        <div <?php $this->print_render_attribute_string($this->attribute_wrap); ?>>

            <?php $this->render_image_wrap(); ?>

            <div <?php $this->print_render_attribute_string($this->attribute_details) ?>>
                <div <?php $this->print_render_attribute_string($this->attribute_content); ?>>
                    <?php
                    $this->render_name();
                    $this->render_position();
                    $this->render_description();
                    if ($show_social_icons) $this->render_social_icons();
                    ?>
                </div>
            </div>
        </div>
<?php
    }
}
