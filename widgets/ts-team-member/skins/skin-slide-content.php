<?php

namespace Themeshark_Elementor\Widgets\TS_Team_Member\Skins;

use \Elementor\Controls_Manager as CM;
use Themeshark_Elementor\Inc\TS_Skin;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Skin_Slide extends TS_Skin
{
    public function get_id()
    {
        return 'slide';
    }

    public function get_title()
    {
        return __('Slide Content', THEMESHARK_TXTDOMAIN);
    }


    protected function _register_controls_actions()
    {
        parent::_register_controls_actions();
        $this->add_controls_injection('section_photo_style', 'before_section_end', [$this, 'inject_image_controls']);
        $this->add_controls_injection('section_content_style', 'before_section_end', [$this, 'inject_content_controls']);
        $this->add_controls_injection('section_social_style', 'before_section_end', [$this, 'inject_social_controls']);
    }

    /**
     * @return \Themeshark_Elementor\Widgets\TS_Team_Member
     */
    public function get_parent()
    {
        return $this->parent;
    }

    /**
     * @param \Themeshark_Elementor\Widgets\TS_Team_Member $parent
     */
    public function inject_image_controls($parent)
    {
        $this->parent = $parent;
        $SC = $this->shorthand_controls();
        $effect_key = $parent->hover_control_key_image;
        $duration_key = $effect_key . '_duration';

        $SC->control($effect_key, 'Hover Animation', CM::SELECT, [
            'groups' => $SC::get_hover_effect_groups(['move', 'zoom']),
            'separator' => 'before',
            'default' => 'move-right',
            'frontend_available' => true,
            'render_type' => 'template',
        ]);

        $SC->control($duration_key, 'Animation Duration (ms)', CM::SLIDER, [
            'range' => $SC::range(['px', 0, 3000]),
            'default' => $SC::range_default('px', 800),
            'selectors' => $SC::selectors([
                '.themeshark-team-member-image-wrap-inner,
                 .themeshark-team-member-image-overlay' => [
                    'transition-duration: {{SIZE}}ms',
                ]
            ])
        ]);

        $this->hide_parent_control('photo_spacing');
        $this->hide_parent_control('photo_width');
        $this->hide_parent_control('photo_max_width');
    }


    /**
     * @param \Themeshark_Elementor\Widgets\TS_Team_Member $parent
     */
    public function inject_content_controls($parent)
    {
        $this->parent = $parent;
        $SC = $this->shorthand_controls();
        $effect_key = $parent->hover_control_key_content;
        $duration_key = $effect_key . '_duration';

        $SC->control('content_vertical_align', 'Vertical Align', CM::CHOOSE, [
            'options' => $SC::choice_set_v_align(['top', 'bottom']),
            'default' => 'bottom',
            'selectors_dictionary' => [
                'top' => 'top: 0px; bottom:auto;',
                'bottom' => 'bottom:0px; top: auto;'
            ],
            'selectors' => $SC::selectors([
                '.themeshark-team-member-details' => [
                    '{{VALUE}}'
                ]
            ])
        ], $SC::set_position('before', 'heading_name'));

        $SC->control($effect_key, 'Hover Animation', CM::SELECT, [
            'groups' => $SC::get_hover_effect_groups(['enter', 'fade-in']),
            'separator' => 'before',
            'default' => 'enter-left',
            'frontend_available' => true,
            'render_type' => 'template',
        ]);

        $SC->control($duration_key, 'Animation Duration (ms)', CM::SLIDER, [
            'range' => $SC::range(['px', 0, 3000]),
            'default' => $SC::range_default('px', 800),
            'selectors' => $SC::selectors([
                '.themeshark-team-member-details' => [
                    'transition-duration: {{SIZE}}ms',
                ]
            ])
        ]);
    }

    /**
     * @param \Themeshark_Elementor\Widgets\TS_Team_Member $parent
     */
    public function inject_social_controls($parent)
    {
        $this->parent = $parent;
        $SC = $this->shorthand_controls();


        $parent->start_injection($SC::set_position('before', 'icon_color', 'control', false));

        $SC->control('social_icon_location', 'Icons Location', CM::SELECT, [
            'default' => 'outer-container',
            'options' => $SC::options_select(
                ['outer-container', 'Outside Content'],
                ['with-content', 'With Content']
            )
        ]);


        $SC->control('social_position_horizontal', 'Horizontal Alignment', CM::CHOOSE, [
            'condition' => [$this->get_control_id('social_icon_location') => 'outer-container'],
            'options' => $SC::choice_set_h_align(['left', 'center', 'right']),
            'default' => 'right',
            'selectors_dictionary' => [
                'left' => 'flex-start',
                'center' => 'center',
                'right' => 'flex-end'
            ],
            'selectors' => $SC::selectors([
                '.themeshark-team-member-social-wrap' => [
                    'justify-content: {{VALUE}}; align-items: {{VALUE}};'
                ]
            ])
        ]);
        $SC->control('social_position_vertical', 'Vertical Alignment', CM::CHOOSE, [
            'condition' => [$this->get_control_id('social_icon_location') => 'outer-container'],
            'options' => $SC::choice_set_v_align(['top', 'bottom']),
            'default' => 'top',
            'selectors_dictionary' => [
                'top' => 'top:0px; bottom:auto;',
                'bottom' => 'bottom:0px; top: auto;',
            ],
            'selectors' => $SC::selectors([
                '.themeshark-team-member-social-wrap' => [
                    '{{VALUE}}'
                ]
            ])
        ]);

        $SC->control('social_icon_flip_vertical', 'Flip Vertical', CM::SWITCHER, [
            'condition' => [$this->get_control_id('social_icon_location') => 'outer-container'],
            'return_value' => 'yes',
            'default' => 'yes',
            'selectors_dictionary' => [
                'yes' => 'flex-direction: column;'
            ],
            'selectors' => $SC::selectors([
                '.themeshark-team-member-social-wrap' => [
                    '{{VALUE}}'
                ]
            ])
        ]);

        $SC->control('social_icon_padding', 'Padding', CM::DIMENSIONS, [
            'condition' => [$this->get_control_id('social_icon_location') => 'outer-container'],
            'selectors' => $SC::selectors([
                '.themeshark-team-member-social-wrap' => [
                    'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
                ]
            ])
        ]);

        $SC->control('separator_icon_location', null, CM::DIVIDER);

        $parent->end_injection();


        $effect_key = $parent->hover_control_key_social;
        $duration_key = $effect_key . '_duration';
        $delay_key = $effect_key . '_delay';
        $interval_key = $effect_key . '_interval';

        $setting_condition = [$this->get_control_id($effect_key) . '!' => 'none'];

        $SC->control($effect_key, 'Hover Animation', CM::SELECT, [
            'groups' => $SC::get_hover_effect_groups(['enter', 'fade-in']),
            'separator' => 'before',
            'default' => 'enter-right',
            'frontend_available' => true,
            'render_type' => 'template',
        ]);

        $SC->control($duration_key, 'Animation Duration (ms)', CM::SLIDER, [
            'condition' => $setting_condition,
            'range' => $SC::range(['px', 0, 3000]),
            'default' => $SC::range_default('px', 400),
            'selectors' => $SC::selectors([
                '.themeshark-social-icon, 
                 .themeshark-social-icon-inner' => [
                    '--transition-duration: {{SIZE}}ms',
                ]
            ])
        ]);

        $SC->control($delay_key, 'Animation Delay (ms)', CM::SLIDER, [
            'condition' => $setting_condition,
            'range' => $SC::range(['px', 0, 1000]),
            'default' => $SC::range_default('px', 100),
            'selectors' => $SC::selectors([
                '.themeshark-team-member:hover .themeshark-social-icon-inner, 
                 .themeshark-team-member:hover .themeshark-social-icon' => [
                    '--starting-delay: {{SIZE}}ms',
                ]
            ])
        ]);

        $SC->control($interval_key, 'Stagger Delay (ms)', CM::SLIDER, [
            'condition' => $setting_condition,
            'range' => $SC::range(['px', 0, 500]),
            'default' => $SC::range_default('px', 150),
            'selectors' => $SC::selectors([
                '.themeshark-team-member:hover .themeshark-social-icon-inner,
                 .themeshark-team-member:hover .themeshark-social-icon' => [
                    '--delay-interval: {{SIZE}}ms',
                ]
            ])
        ]);
    }


    public function ensure_hover_effect($attribute, $hover_control_id)
    {
        $settings = $this->parent->get_settings();
        $hover_control_key = $this->get_control_id($hover_control_id);
        $effect = isset($settings[$hover_control_key]) ? $settings[$hover_control_key] : '';
        if (!empty($effect)) $this->parent->add_render_attribute($attribute, 'class', "ts-effect--$effect");
    }


    public function render()
    {
        $parent = $this->get_parent();

        $parent->add_default_render_attributes();
        $settings = $this->parent->get_settings();
        $show_social_icons = $settings['show_social_icons'] === 'yes';
        $icons_location = $this->get_skin_control_setting('social_icon_location');



        if ($icons_location === 'outer-container') {
            $parent->add_render_attribute($parent->attribute_social_wrap, 'class', 'social-icon-wrap-outer');
        }

        $this->ensure_hover_effect($parent->attribute_details, $parent->hover_control_key_content);
        $this->ensure_hover_effect($parent->attribute_image_wrap_inner, $parent->hover_control_key_image);

        //check if hover effect should be on social inner or outer
        $mask_animations = ['enter-left', 'enter-right', 'enter-top', 'enter-bottom'];
        $social_effect = $this->get_skin_control_setting($parent->hover_control_key_social);
        $use_social_effect_mask = in_array($social_effect, $mask_animations);

        if ($use_social_effect_mask) {
            $this->ensure_hover_effect($parent->attribute_social_icon_inner, $parent->hover_control_key_social);
        } else {
            $this->ensure_hover_effect($parent->attribute_social_icon, $parent->hover_control_key_social);
        }
?>
        <div <?php echo $parent->get_render_attribute_string($parent->attribute_wrap); ?>>

            <?php $parent->render_image_wrap(true); ?>
            <div <?php echo $parent->get_render_attribute_string($parent->attribute_details); ?>>
                <div <?php echo $parent->get_render_attribute_string($parent->attribute_content); ?>>
                    <?php
                    $parent->render_name();
                    $parent->render_position();
                    $parent->render_description();
                    if ($icons_location === 'with-content' && $show_social_icons) {
                        $parent->render_social_icons();
                    } ?>
                </div>
            </div>

            <?php
            if ($icons_location === 'outer-container' && $show_social_icons) {
                $parent->render_social_icons();
            } ?>
        </div>
<?php
    }
}
