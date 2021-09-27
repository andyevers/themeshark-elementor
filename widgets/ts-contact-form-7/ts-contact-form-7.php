<?php

namespace Themeshark_Elementor\Widgets;

use \Elementor\Group_Control_Border;
use \Elementor\Controls_Manager as CM;
use \Themeshark_Elementor\Inc\TS_Widget;
use \Elementor\Group_Control_Typography;
use \Themeshark_Elementor\Inc\Shorthand_Controls;
use \Themeshark_Elementor\Controls\Controls_Handler;
use \Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use \Elementor\Core\Kits\Documents\Tabs\Global_Typography;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class TS_Contact_Form_7 extends TS_Widget
{

    const NAME = 'ts-contact-form-7';
    const TITLE = 'Contact Form 7';


    public static function editor_scripts()
    {
        self::editor_script('ts-contact-form-7', self::get_dir_url(__DIR__, 'ts-contact-form-7-editor.js'));
    }


    public static function register_styles()
    {
        self::widget_style('ts-contact-form-7', self::get_dir_url(__DIR__, 'ts-contact-form-7.css'));
    }


    public function get_icon()
    {
        return 'tsicon-contact-form-7';
    }

    public function get_style_depends()
    {
        return ['ts-contact-form-7'];
    }


    public function get_keywords()
    {
        return self::keywords(['contact', 'form', 'email']);
    }

    /**
     * returns all field sets for each contact form. uses form post_id as key and label 
     * ex: [10636 => ["my-name" => "My Name", "my-email" => "My Email"], 10637 => [...]]
     * @return {Array} 
     */
    public function get_forms_field_groups()
    {
        $forms = $this->get_forms();
        $field_groups = [];
        foreach ($forms as $form_id => $label) {
            $form_fields = $this->get_form_fields($form_id);
            $field_groups[$form_id] = [];
            foreach ($form_fields as $field) {

                if (!$field) continue;

                $field_groups[$form_id][$field] = $field; // use key as the label too.
            }
        }
        return $field_groups;
    }


    /**
     * returns all fields for the provided form_id
     * ex: ["my-name" => "My Name", "my-email" => "My Email"]
     * @param {String|Number} $form_id post ID for the form
     */
    public function get_form_fields($form_id)
    {
        if (class_exists('\WPCF7_ContactForm')) {
            $field_names = [];
            $ContactForm = \WPCF7_ContactForm::get_instance($form_id);
            if (!$ContactForm) return [];

            $form_fields = $ContactForm->scan_form_tags();

            foreach ($form_fields as $field) {
                $field_names[] = $field->name;
            }

            return $field_names;
        }
        return [];
    }


    /**
     * Returns form_id => form title for each CF7 form
     * ex: [10636 => "Contact form 1", 10637 => "Some Contact Form"]
     */
    public function get_forms()
    {
        $cf7_form_posts = get_posts('post_type="wpcf7_contact_form"&numberposts=-1');
        $contact_forms = [];
        if ($cf7_form_posts) {
            foreach ($cf7_form_posts as $form) {
                $contact_forms[$form->ID] = $form->post_title;
            }
        } else {
            $contact_forms[__('No contact forms found', THEMESHARK_TXTDOMAIN)] = 0;
        }
        return $contact_forms;
    }


    private function get_width_options()
    {
        return [
            '' => __('Default', THEMESHARK_TXTDOMAIN),
            '100.0' => '100%',
            '80.0' => '80%',
            '75.0' => '75%',
            '70.0' => '70%',
            '66.66666' => '66%',
            '60.0' => '60%',
            '50.0' => '50%',
            '40.0' => '40%',
            '33.33333' => '33%',
            '30.0' => '30%',
            '25.0' => '25%',
            '20.0' => '20%'
        ];
    }

    /**
     * Controls for individual contact form fields
     */
    protected function add_repeater_controls($repeater)
    {
        $SCR = new Shorthand_Controls($repeater);

        $SCR->control('field', null, CM::HIDDEN, ['default' => '']);

        $SCR->control('field_label', 'Label', CM::TEXT, [
            'render_type' => 'ui',
            'themeshark_settings' => [
                Controls_Handler::LINK_HTML => [
                    'selector' => '{{CURRENT_ITEM}} .themeshark-field-label'
                ]
            ]
        ]);

        $SCR->responsive_control('width', 'Width', CM::SELECT, [
            'render_type' => 'ui',
            'selectors' => $SCR::selectors([
                '{{CURRENT_ITEM}}' => [
                    'width: {{VALUE}}%'
                ]
            ]),
            'default' => '100.0',
            'options' => $this->get_width_options(),
        ]);

        $repeater->end_controls_tab();
        $repeater->end_controls_tabs();
    }


    protected function register_controls()
    {
        $SC = new Shorthand_Controls($this);

        // $form_wrap = '.themeshark-contact-form-7';
        // $form = '.themeshark-form, .wpcf7-form';
        // $textual_field = '.themeshark-field-textual, .elementor-field-textual';
        // $field = '.themeshark-form .themeshark-form-field, .wpcf7-form-control';

        $form = '.themeshark-form';
        $textual_field = '.themeshark-field-textual';
        $field = '.themeshark-form .themeshark-form-field';
        $label = '.themeshark-form label';
        $submit_button = ".themeshark-form input[type='submit']";
        $submit_wrapper = '.themeshark-field-type-submit';

        //------------------------------//
        //-------- FORM FIELDS ---------//
        //------------------------------//
        $this->start_controls_section('section_content_layout', [
            'label' => $SC::_('Layout'),
            'tab' => CM::TAB_LAYOUT
        ]);

        $contact_forms = $this->get_forms();
        $SC->control('contact_form', 'Contact Form', CM::SELECT, [
            'render_type' => 'ui',
            'options' => $this->get_forms()
        ]);

        $cf7_forms_link = admin_url('?page=wpcf7');
        $SC->control('contact_form_link', null, CM::RAW_HTML, [
            'raw' => $SC::_('To add, move, & modify fields, edit the form ')
                . "<a href='$cf7_forms_link' target='_blank'>" . $SC::_('here') . '</a>'
                . $SC::_(' then refresh the page.')
        ]);

        $SC->control('use_default_html', 'Use Default HTML', CM::SWITCHER, [
            'default' => '',
            'return_value' => 'yes',
            'default' => 'true',
            'separator' => 'before',
        ]);

        $repeater = new \Elementor\Repeater();
        $this->add_repeater_controls($repeater);

        $SC->control('divider_field_widths', null, CM::DIVIDER);

        $SC->control('contact_form_fields', 'Fields', CM::REPEATER, [
            'condition' => ['use_default_html!' => 'yes'],
            'fields' => $repeater->get_controls(),
            'classes' => 'themeshark-cf7-repeater',
            'render_type' => 'ui',
            'title_field' => '{{{ field_label || field }}}',
            'themeshark_settings' => [
                'contact_form_7_add_form_field_slides' => true,
                Controls_Handler::NEW_SLIDES_ADD_HANDLERS => true
            ],
            'contact_form_field_sets' => $this->get_forms_field_groups(),
            'sort' => false,
            'prevent_empty' => false,
            'item_actions' => [
                'add' => false,
                'duplicate' => false,
                'remove' => false,
                'sort' => false,
            ],
        ]);



        //HOLDS CONTROL SETTING FOR EACH FIELD ID
        $SC->control('field_value_saver', null, CM::HIDDEN, [
            'default' => null,
            'condition' => ['use_default_html!' => 'yes'],
        ]);

        $SC->control('input_sizes', 'Input Size', CM::SELECT, [
            'options' => $SC::options_select(
                ['xs', 'Extra Small'],
                ['sm', 'Large'],
                ['sm', 'Small'],
                ['md', 'Medium'],
                ['lg', 'Large'],
                ['xl', 'Extra Large']
            ),
            'prefix_class' => 'themeshark-input-size-',
            'default' => 'sm',
        ]);


        $SC->control('show_labels', 'Label', CM::SWITCHER, [
            'condition' => ['use_default_html!' => 'yes'],
            'label_on' => $SC::_('Show'),
            'label_off' => $SC::_('Hide'),
            'return_value' => 'true',
            'default' => 'true',
            'separator' => 'before',
        ]);




        $this->end_controls_section();

        $this->start_controls_section('section_button', [
            'condition' => ['use_default_html!' => 'yes'],
            'label' => $SC::_('Submit Button'),
            'tab' => CM::TAB_LAYOUT,
        ]);

        $SC->control('button_size', 'Size', CM::SELECT, [
            'options' => $SC::options_select(
                ['xs', 'Extra Small'],
                ['sm', 'Small'],
                ['md', 'Medium'],
                ['lg', 'Large'],
                ['xl', 'Extra Large']
            ),
            'default' => 'sm',
            'render_type' => 'ui',
            'themeshark_settings' => [
                Controls_Handler::NO_TRANSITION => true,
            ],
            'prefix_class' => 'themeshark-button-size-',
        ]);


        $SC->responsive_control('button_width', 'Width', CM::SELECT, [
            'render_type' => 'ui',
            'default' => '100.0',
            'frontend_available' => true,
            'options' => $this->get_width_options(),
            'selectors' => $SC::selectors([
                $submit_wrapper => [
                    'width: {{VALUE}}%'
                ]
            ])
        ]);



        $_prefix = 'eicon-text-align';
        $SC->responsive_control('button_alignment', 'Alignment', CM::CHOOSE, [
            'options' => $SC::options_choose(
                ['flex-start', 'Left', "$_prefix-left"],
                ['center', 'Center', "$_prefix-center"],
                ['flex-end', 'Right', "$_prefix-right"],
                ['stretch', 'Justified', "$_prefix-justify"]
            ),
            'default' => 'stretch',
            'selectors' => $SC::selectors([
                $submit_wrapper => [
                    'justify-content: {{VALUE}}'
                ]
            ])
        ]);


        $SC->responsive_control('button_justify', 'Alignment', CM::HIDDEN, [
            'condition' => ['button_alignment' => 'stretch'],
            'default' => '100%',
            'selectors' => $SC::selectors([
                $submit_button => [
                    'width:{{VALUE}}'
                ]
            ])
        ]);


        $this->end_controls_section();




        //------------------------------//
        //----------- STYLES -----------//
        //------------------------------//

        $this->start_controls_section('section_contact_field_styles', [
            'label' => $SC::_('Form'),
            'tab' => CM::TAB_STYLE,
        ]);

        $SC->control('column_gap', 'Columns Gap', CM::SLIDER, [
            'condition' => ['use_default_html!' => 'yes'],
            'default'   => $SC::range_default('px', 10),
            'range'     => $SC::range(['px', 0, 60]),
            'selectors' => $SC::selectors([
                $form => [
                    '--column-gap: {{SIZE}}{{UNIT}}'
                ]
            ])
        ]);

        $SC->control('row_gap', 'Rows Gap', CM::SLIDER, [
            'default' => $SC::range_default('px', 10),
            'range' => $SC::range(['px', 0, 60]),
            'selectors' => $SC::selectors([
                $form => [
                    '--row-gap: {{SIZE}}{{UNIT}}'
                ]
            ])
        ]);

        $SC->control('heading_label', 'Label', CM::HEADING, [
            'separator' => 'before'
        ]);

        $SC->control('label_spacing', 'Spacing', CM::SLIDER, [
            'range' => $SC::range(['px', 0, 60]),
            'condition' => ['use_default_html!' => 'yes'],
            'default' => $SC::range_default('px', 0),
            'selectors' => $SC::selectors([
                $label => [
                    'padding-bottom: {{SIZE}}{{UNIT}}'
                ]
            ])
        ]);


        $SC->control('label_color', 'Color', CM::COLOR, [
            'selectors' => $SC::selectors([
                $label => [
                    'color: {{VALUE}}'
                ],
                'global' => ['default' => Global_Colors::COLOR_TEXT],
            ])
        ]);

        $SC->group_control('label_typography', Group_Control_Typography::get_type(), [
            'global' => ['default' => Global_Typography::TYPOGRAPHY_TEXT],
            'selector' => '{{WRAPPER}} .elementor-field-label, {{WRAPPER}} .ts-cf7-use-default-html label',
        ]);


        $SC->responsive_control('textarea_height', 'Textarea Height', CM::SLIDER, [
            'range' => $SC::range(['px', 30, 500]),
            'default' => $SC::range_default('px', 125),
            'separator' => 'before',
            'selectors' => $SC::selectors([
                "textarea" => [
                    'height: {{SIZE}}{{UNIT}}; display: block;'
                ],
            ]),
        ]);




        $this->end_controls_section();


        //------------------------------//
        //----------- INPUTS -----------//
        //------------------------------//


        $this->start_controls_section('section_style_input', [
            'label' => $SC::_('Field'),
            'tab'   => CM::TAB_STYLE,
        ]);

        $SC->control('input_placeholder_color', 'Placeholder Color', CM::COLOR, [
            'selectors' => $SC::selectors([
                "$field:not(input[type='submit'])::placeholder,
                 textarea::placeholder" => [
                    'color: {{VALUE}}'
                ],
            ]),
        ]);

        $SC->control('input_text_color', 'Text Color', CM::COLOR, [
            'selectors' => $SC::selectors([
                "$field, 
                 textarea,
                 .wpcf7-list-item" => [
                    'color: {{VALUE}};'
                ],
            ]),
        ]);


        $SC->group_control('input_typography', Group_Control_Typography::get_type(), [
            'global' => ['default' => Global_Typography::TYPOGRAPHY_TEXT],
            'selector' => '{{WRAPPER}} .elementor-field-textual, {{WRAPPER}} .wpcf7-list-item, {{WRAPPER}} .ts-cf7-use-default-html input, {{WRAPPER}} .ts-cf7-use-default-html textarea, {{WRAPPER}} .ts-cf7-use-default-html select',
        ]);

        $default_html_textual = '.ts-cf7-use-default-html input, .ts-cf7-use-default-html textarea, .ts-cf7-use-default-html select';

        $SC->control('input_text_background', 'Background Color', CM::COLOR, [
            'separator' => 'before',
            'selectors' => $SC::selectors([
                "$textual_field,
                 $default_html_textual" => [
                    'background-color: {{VALUE}}'
                ]
            ]),
        ]);



        $SC->control('field_border_color', 'Border Color', CM::COLOR, [
            'separator' => 'before',
            'selectors' => $SC::selectors([
                "$textual_field,
                 $default_html_textual" => [
                    'border-color:{{VALUE}}'
                ]
            ])
        ]);


        $SC->control('field_border_width', 'Border Width', CM::DIMENSIONS, [
            'placeholder' => '1',
            'size_units' => ['px'],
            'selectors' => $SC::selectors([
                "$textual_field,
                 $default_html_textual" => [
                    'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
                ]
            ]),
        ]);

        $SC->control('field_border_radius', 'Border Radius', CM::DIMENSIONS, [
            'size_units' => ['px', '%'],
            'selectors' => $SC::selectors([
                "$textual_field,
                 $default_html_textual" => [
                    'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
                ]
            ]),
        ]);

        $this->end_controls_section();

        $this->start_controls_section('section_button_style', [
            'label' => $SC::_('Buttons'),
            'tab' => CM::TAB_STYLE,
        ]);

        $buttons = 'button, input[type="button"], input[type="submit"]';
        $buttons_hover = 'button:hover, input[type="button"]:hover, input[type="submit"]:hover';
        $buttons_group_control = '{{WRAPPER}} button, {{WRAPPER}} input[type="button"], {{WRAPPER}} input[type="submit"]';

        $SC->group_control('button_typography', Group_Control_Typography::get_type(), [
            'global' => ['default' => Global_Typography::TYPOGRAPHY_ACCENT],
            'selector' => $buttons_group_control,
        ]);

        $SC->group_control('button_border', Group_Control_Border::get_type(), [
            'selector' => $buttons_group_control,
            'exclude' => ['color'],
        ]);


        $this->start_controls_tabs('tabs_button_style');

        $this->start_controls_tab('tab_button_normal', [
            'label' => $SC::_('Normal'),
        ]);


        $SC->control('button_background_color', 'Background Color', CM::COLOR, [
            'global' => ['default' => Global_Colors::COLOR_ACCENT],
            'selectors' => $SC::selectors([
                $buttons => [
                    'background-color: {{VALUE}};'
                ]
            ]),
        ]);

        $SC->control('button_text_color', 'Text Color', CM::COLOR, [
            'default' => '#ffffff',
            'selectors' => $SC::selectors([
                $buttons => [
                    'color: {{VALUE}};',
                ]
            ]),
        ]);


        $SC->control('button_border_color', 'Border Color', CM::COLOR, [
            'condition' => ['button_border_border!' => ''],
            'selectors' => $SC::selectors([
                $buttons => [
                    'border-color: {{VALUE}};'
                ]
            ]),
        ]);

        $this->end_controls_tab();

        $this->start_controls_tab('tab_button_hover', [
            'label' => $SC::_('Hover'),
        ]);

        $SC->control('button_background_color_hover', 'Background Color', CM::COLOR, [
            'selectors' => $SC::selectors([
                $buttons_hover => [
                    'background-color: {{VALUE}};'
                ]
            ]),
        ]);

        $SC->control('button_text_color_hover', 'Text Color', CM::COLOR, [
            'selectors' => $SC::selectors([
                $buttons_hover => [
                    'color: {{VALUE}};',
                ]
            ]),
        ]);


        $SC->control('button_border_color_hover', 'Border Color', CM::COLOR, [
            'condition' => ['button_border_border!' => ''],
            'selectors' => $SC::selectors([
                $buttons_hover => [
                    'border-color: {{VALUE}};'
                ]
            ]),
        ]);

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $SC->control('button_border_radius', 'Border Radius', CM::DIMENSIONS, [
            'size_units' => ['px', '%'],
            'separator' => 'before',
            'selectors' => $SC::selectors([
                $buttons => [
                    'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
                ]
            ]),
        ]);

        $SC->control('button_text_padding', 'Text Padding', CM::DIMENSIONS, [
            'size_units' => ['px', 'em', '%'],
            'selectors' => $SC::selectors([
                $buttons => [
                    'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
                ]
            ]),
        ]);



        $this->end_controls_section();
    }

    private $accepted_field_types = [
        'text',
        'email',
        'url',
        'tel',
        'number',
        'date',
        'textarea',
        'select',
        'radio',
        'acceptance',
        'checkbox',
        'file',
        'submit'
    ];

    private $unaccepted_field_types = [
        'quiz'
    ];

    private $non_textual_fields = [
        'checkbox',
        'radio',
        'button',
        'file',
        'hidden',
        'acceptance',
        'submit'
    ];


    /**
     * Creates \DOMDocument instance from the HTML produced by CF7 shortcode for a given $form_id
     */
    private function get_form_document($form_id, $is_default_html = false)
    {
        //get form shortcode
        $this->add_render_attribute('shortcode', ['id' => $form_id]);
        $shortcode = [];
        $shortcode[] = sprintf('[contact-form-7 %s]', $this->get_render_attribute_string('shortcode'));
        $shortcode = implode("", $shortcode);

        // '[contact-form-7 ';


        //create document from shortcode
        $html = do_shortcode($shortcode);
        if (strpos($html, '[contact-form-7 ') === 0) return null; //no form found

        $document = new \DOMDocument('1.0', 'utf-8');
        $document->preserveWhiteSpace = false;
        $document->formatOutput = true;
        @$document->loadHTML($html);

        if ($is_default_html) return $document;

        $fields = $this->extract_form_fields($document);
        $form = $document->getElementsByTagName('form')->item(0);
        $this->add_element_class($form, 'elementor-form');
        $this->add_element_class($form, 'themeshark-form');

        //clean html to have just form and fields
        $form_html = '<form ' . $this->get_attribute_string($form) . '>';
        foreach ($fields as $field) $form_html .= $field->ownerDocument->saveHTML($field);
        $form_html .= '</form>';

        @$document->loadHTML($form_html);
        return $document;
    }

    private function extract_form_fields(\DOMDocument $document)
    {
        $fields = (new \DOMXPath($document))->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' wpcf7-form-control ')]");
        return $fields;
    }


    private function get_field_type(\DOMElement $field)
    {
        foreach ($this->unaccepted_field_types as $type) {
            if ($this->element_has_class($field, "wpcf7-$type")) return null;
        }

        $type_att = $field->getAttribute('type');
        if ($type_att) return $type_att;

        foreach ($this->accepted_field_types as $type) {
            if ($this->element_has_class($field, "wpcf7-$type")) return $type;
        }
        return null;
    }

    private function get_field_name(\DOMElement $field)
    {
        if ($field->hasAttribute('name')) return $field->getAttribute('name');
        $elements = (new \DOMXPath($field->ownerDocument))->query('.//*[@name]', $field);
        foreach ($elements as $child) {
            if ($child->hasAttribute('name')) {
                return $child->getAttribute('name');
            }
        }
    }

    private function get_attribute_string(\DOMElement $element)
    {
        $attribute_string = '';
        if ($element->hasAttributes()) {
            foreach ($element->attributes as $attr) {
                $name = $attr->nodeName;
                $value = esc_attr($attr->nodeValue);
                $attribute_string .= "$name='$value' ";
            }
        }
        return $attribute_string;
    }

    private function element_has_class(\DOMElement $element, $class)
    {
        if (!$element->hasAttribute('class')) return false;
        $class_att = ' ' . $element->getAttribute('class') . ' ';
        return strpos($class_att, " $class ") !== false;
    }

    private function add_element_class(\DOMElement $element, $class)
    {
        if ($this->element_has_class($element, $class)) return;
        $class_att = $element->getAttribute('class');

        if (!empty($class_att)) $class_att .= ' ';
        $class_att .= $class;

        $element->setAttribute('class', $class_att);
        $class_att = '';
    }


    private function get_field_settings($field_name, $settings)
    {
        $fields = $settings['contact_form_fields'];
        foreach ($fields as $field) {
            if ($field['field'] === $field_name) {
                return $field;
            }
        }
        return [];
    }


    private function display_error($message)
    {
        $allowed_html = [
            'div' => [
                'class' => []
            ]
        ];
        echo wp_kses("<div class='themeshark-cf7-form-error-message'>$message</div>", $allowed_html);
    }

    private function is_valid_field_set(\DOMDocument $document, $display_error = true)
    {
        $is_valid = true;
        $fields = $this->extract_form_fields($document);

        $error_message  = 'Cannot display form. ';

        //CHECK NO FIELDS
        if (sizeof($fields) < 1) {
            $is_valid = false;
            $error_message .= 'No fields were found. ';
        }

        //CHECK DUPLICATE FIELDS
        $field_names = [];
        $duplicate_fields_string = '';
        foreach ($fields as $field) {
            $field_name = $this->get_field_name($field);
            if (in_array($field_name, $field_names)) {
                $is_valid = false;
                $duplicate_fields_string .= " $field_name";
            }
            $field_names[] = $field_name;
        }
        if (strlen($duplicate_fields_string) > 0) {
            $error_message .= "duplicate field names: $duplicate_fields_string";
        }

        //display error
        if ($is_valid === false && $display_error) {
            $this->display_error($error_message);
        }

        return $is_valid;
    }

    private function render_field(\DOMElement $field, $settings)
    {
        $settings = $this->get_settings();
        $name = $this->get_field_name($field);
        $type = $this->get_field_type($field);
        $field_settings = $this->get_field_settings($name, $settings);
        $field_id = isset($field_settings['_id']) ? $field_settings['_id'] : null;

        $field_wrap_att = "field_wrapper_$name";
        $field_label_att = "field_label_$name";

        $this->add_render_attribute($field_wrap_att, [
            'class' => [
                'elementor-field-group',
                'elementor-column',
                'themeshark-field-group',
            ]
        ]);

        $this->add_render_attribute($field_label_att, [
            'class' => [
                'elementor-field-label',
                'themeshark-field-label'
            ]
        ]);

        if ($type) {
            $this->add_render_attribute($field_wrap_att, 'class', "elementor-field-type-$type");
            $this->add_render_attribute($field_wrap_att, 'class', "themeshark-field-type-$type");
        }
        if ($name) $this->add_render_attribute($field_wrap_att, 'class', "elementor-field-group-$name");
        if ($field_id) $this->add_render_attribute($field_wrap_att, 'class', "elementor-repeater-item-$field_id");

        //cf7 field classes
        $this->add_element_class($field, 'elementor-field themeshark-form-field');

        $is_textual_field = !in_array($this->get_field_type($field), $this->non_textual_fields);
        if ($is_textual_field) {
            $this->add_element_class($field, 'elementor-field-textual');
            $this->add_element_class($field, 'themeshark-field-textual');
        }
        $label = $field_settings && $field_settings['field_label'] ? $field_settings['field_label'] : $name;

?>
        <div <?php $this->print_render_attribute_string($field_wrap_att); ?>>

            <?php if ($settings['show_labels']) : ?>
                <label <?php $this->print_render_attribute_string($field_label_att); ?>><?php esc_html_e($label); ?></label>
            <?php endif; ?>

            <?php echo $field->ownerDocument->saveHTML($field); ?>
        </div>




    <?php
    }

    public function render()
    {
        $allowed_err_html = [
            'div' => [
                'class' => []
            ]
        ];
        if (!defined('WPCF7_VERSION')) {

            echo wp_kses('<div class="themeshark-cf7-error-notice">The "Contact Form 7" plugin must be installed to display ThemeShark Contact Form 7 widget.</div>', $allowed_err_html);
            return;
        }

        $settings = $this->get_settings();
        $form_id = $this->get_settings('contact_form');

        if (!$form_id) {

            echo wp_kses('<div class="themeshark-cf7-error-notice">ThemeShark Contact Form 7 widget requires a valid contact form ID.</div>', $allowed_err_html);
            return;
        }

        $is_default_html = $settings['use_default_html'] === 'yes';
        $form_document = $this->get_form_document($form_id, $is_default_html);

        if (!$form_document) {
            echo wp_kses('<div class="themeshark-cf7-error-notice">Could not find contact form with ID ' . $form_id . '</div>', $allowed_err_html);
            return;
        }

        $form = $form_document->getElementsByTagName('form')->item(0);
        $fields = $this->extract_form_fields($form_document);



        $this->add_render_attribute('cf7_form_wrap', 'class', ['themeshark-contact-form-7']);
        if ($is_default_html) $this->add_render_attribute('cf7_form_wrap', 'class', 'ts-cf7-use-default-html');

        if ($this->is_valid_field_set($form_document) === false) return;
    ?>

        <div <?php $this->print_render_attribute_string('cf7_form_wrap'); ?>>
            <?php if ($is_default_html) : ?>
                <?php echo $form_document->saveHTML(); ?>

            <?php else : ?>
                <form <?php echo $this->get_attribute_string($form); ?>>
                    <div class='elementor-form-fields-wrapper themeshark-form-fields-wrapper'>
                        <?php foreach ($fields as $field) $this->render_field($field, $settings); ?>
                    </div>
                </form>
            <?php endif; ?>
        </div>
<?php
    }
}
