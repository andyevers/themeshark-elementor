<?php

namespace Themeshark_Elementor\Controls;

use Elementor\Widget_Base;
use Elementor\Element_Base;
use Elementor\Element_Section;
use Elementor\Controls_Manager;
use Elementor\Core\Base\Module;

if (!defined('ABSPATH'))  exit;

class Sticky_Module extends Module
{
    public function __construct()
    {
        // parent::__construct();
        $this->add_actions();

        add_action('elementor/widgets/widgets_registered', [$this, 'register_nopro_script']); //admin & frontend scripts register
    }

    public function get_name()
    {
        return 'sticky';
    }


    public function register_nopro_script()
    {
        $dir_url = THEMESHARK_URL . 'controls/sticky';
        wp_enqueue_script('ts-sticky-nopro', "$dir_url/sticky-nopro.js", ['elementor-sticky'], false, true);
    }

    /**
     * Check if `$element` is an instance of a class in the `$types` array.
     *
     * @param $element
     * @param $types
     *
     * @return bool
     */
    private function is_instance_of($element, array $types)
    {
        foreach ($types as $type) {
            if ($element instanceof $type) {
                return true;
            }
        }

        return false;
    }

    public function register_controls(Element_Base $element)
    {
        $element->add_control(
            'sticky',
            [
                'label' => __('Sticky', THEMESHARK_TXTDOMAIN),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    '' => __('None', THEMESHARK_TXTDOMAIN),
                    'top' => __('Top', THEMESHARK_TXTDOMAIN),
                    'bottom' => __('Bottom', THEMESHARK_TXTDOMAIN),
                ],
                'render_type' => 'none',
                'frontend_available' => true,
                'assets' => $this->get_asset_conditions_data(),
            ]
        );

        $element->add_control(
            'sticky_on',
            [
                'label' => __('Sticky On', THEMESHARK_TXTDOMAIN),
                'type' => Controls_Manager::SELECT2,
                'multiple' => true,
                'label_block' => true,
                'default' => ['desktop', 'tablet', 'mobile'],
                'options' => [
                    'desktop' => __('Desktop', THEMESHARK_TXTDOMAIN),
                    'tablet' => __('Tablet', THEMESHARK_TXTDOMAIN),
                    'mobile' => __('Mobile', THEMESHARK_TXTDOMAIN),
                ],
                'condition' => [
                    'sticky!' => '',
                ],
                'render_type' => 'none',
                'frontend_available' => true,
            ]
        );

        $element->add_control(
            'sticky_offset',
            [
                'label' => __('Offset', THEMESHARK_TXTDOMAIN),
                'type' => Controls_Manager::NUMBER,
                'default' => 0,
                'min' => 0,
                'max' => 500,
                'required' => true,
                'condition' => [
                    'sticky!' => '',
                ],
                'render_type' => 'none',
                'frontend_available' => true,
            ]
        );

        $element->add_control(
            'sticky_effects_offset',
            [
                'label' => __('Effects Offset', THEMESHARK_TXTDOMAIN),
                'type' => Controls_Manager::NUMBER,
                'default' => 0,
                'min' => 0,
                'max' => 1000,
                'required' => true,
                'condition' => [
                    'sticky!' => '',
                ],
                'render_type' => 'none',
                'frontend_available' => true,
            ]
        );

        // Add `Stay In Column` only to the following types:
        $types = [
            Element_Section::class,
            Widget_Base::class,
        ];

        if ($this->is_instance_of($element, $types)) {
            $conditions = [
                'sticky!' => '',
            ];
            if ($element instanceof Element_Section && \Elementor\Plugin::$instance->editor->is_edit_mode()) {
                $conditions['isInner'] = true;
            }

            $element->add_control(
                'sticky_parent',
                [
                    'label' => __('Stay In Column', THEMESHARK_TXTDOMAIN),
                    'type' => Controls_Manager::SWITCHER,
                    'condition' => $conditions,
                    'render_type' => 'none',
                    'frontend_available' => true,
                ]
            );
        }

        $element->add_control(
            'sticky_divider',
            [
                'type' => Controls_Manager::DIVIDER,
            ]
        );
    }

    private function get_asset_conditions_data()
    {
        return [
            'scripts' => [
                [
                    'name' => 'e-sticky',
                    'conditions' => [
                        'terms' => [
                            [
                                'name' => 'sticky',
                                'operator' => '!==',
                                'value' => '',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    private function add_actions()
    {
        add_action('elementor/element/section/section_effects/after_section_start', [$this, 'register_controls']);
        add_action('elementor/element/common/section_effects/after_section_start', [$this, 'register_controls']);
    }
}
