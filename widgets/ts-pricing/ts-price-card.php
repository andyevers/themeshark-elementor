<?php

namespace Themeshark_Elementor\Widgets;

use Elementor\Controls_Manager as CM;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Icons_Manager;
use Elementor\Repeater;
use Themeshark_Elementor\Inc\TS_Widget;
use Elementor\Utils;
use Themeshark_Elementor\Inc\Helpers;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class TS_Price_Card extends TS_Widget
{

	const NAME = 'ts-price-card';
	const TITLE = 'Price Card';

	public function get_icon()
	{
		return 'tsicon-price-card';
	}

	public function get_style_depends()
	{
		return ['ts-pricing', 'ts-price-card'];
	}

	public function get_keywords()
	{
		return self::keywords(['pricing', 'card', 'product']);
	}

	public static function register_styles()
	{
		self::widget_style('ts-price-card', self::get_dir_url(__DIR__, 'ts-price-card.css'));
		if (!\Themeshark_Elementor\Plugin::has_elementor_pro()) {
			self::widget_style('ts-pricing', self::get_dir_url(__DIR__, 'ts-pricing.css'));
		}
	}

	protected function register_controls()
	{
		$SC = $this->shorthand_controls();

		$this->start_controls_section(
			'section_header',
			[
				'label' => __('Header', THEMESHARK_TXTDOMAIN),
			]
		);

		$SC->control('header_style', 'Style', CM::SELECT, [
			'default'      => 'standard',
			'prefix_class' => 'themeshark-price-card-header-',
			'options'      => $SC::options_select(
				['standard', 'Standard'],
				['curved', 	 'Curved']
			)
		]);

		$this->add_control(
			'heading',
			[
				'label' => __('Title', THEMESHARK_TXTDOMAIN),
				'type' => CM::TEXT,
				'default' => __('Enter your title', THEMESHARK_TXTDOMAIN),
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$this->add_control(
			'sub_heading',
			[
				'label' => __('Description', THEMESHARK_TXTDOMAIN),
				'type' => CM::TEXT,
				'default' => __('Enter your description', THEMESHARK_TXTDOMAIN),
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$this->add_control(
			'heading_tag',
			[
				'label' => __('Title HTML Tag', THEMESHARK_TXTDOMAIN),
				'type' => CM::SELECT,
				'options' => [
					'h2' => 'H2',
					'h3' => 'H3',
					'h4' => 'H4',
					'h5' => 'H5',
					'h6' => 'H6',
				],
				'default' => 'h3',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_pricing',
			[
				'label' => __('Pricing', THEMESHARK_TXTDOMAIN),
			]
		);

		$this->add_control(
			'currency_symbol',
			[
				'label' => __('Currency Symbol', THEMESHARK_TXTDOMAIN),
				'type' => CM::SELECT,
				'options' => [
					'' => __('None', THEMESHARK_TXTDOMAIN),
					'dollar' => '&#36; ' . _x('Dollar', 'Currency', THEMESHARK_TXTDOMAIN),
					'euro' => '&#128; ' . _x('Euro', 'Currency', THEMESHARK_TXTDOMAIN),
					'baht' => '&#3647; ' . _x('Baht', 'Currency', THEMESHARK_TXTDOMAIN),
					'franc' => '&#8355; ' . _x('Franc', 'Currency', THEMESHARK_TXTDOMAIN),
					'guilder' => '&fnof; ' . _x('Guilder', 'Currency', THEMESHARK_TXTDOMAIN),
					'krona' => 'kr ' . _x('Krona', 'Currency', THEMESHARK_TXTDOMAIN),
					'lira' => '&#8356; ' . _x('Lira', 'Currency', THEMESHARK_TXTDOMAIN),
					'peseta' => '&#8359 ' . _x('Peseta', 'Currency', THEMESHARK_TXTDOMAIN),
					'peso' => '&#8369; ' . _x('Peso', 'Currency', THEMESHARK_TXTDOMAIN),
					'pound' => '&#163; ' . _x('Pound Sterling', 'Currency', THEMESHARK_TXTDOMAIN),
					'real' => 'R$ ' . _x('Real', 'Currency', THEMESHARK_TXTDOMAIN),
					'ruble' => '&#8381; ' . _x('Ruble', 'Currency', THEMESHARK_TXTDOMAIN),
					'rupee' => '&#8360; ' . _x('Rupee', 'Currency', THEMESHARK_TXTDOMAIN),
					'indian_rupee' => '&#8377; ' . _x('Rupee (Indian)', 'Currency', THEMESHARK_TXTDOMAIN),
					'shekel' => '&#8362; ' . _x('Shekel', 'Currency', THEMESHARK_TXTDOMAIN),
					'yen' => '&#165; ' . _x('Yen/Yuan', 'Currency', THEMESHARK_TXTDOMAIN),
					'won' => '&#8361; ' . _x('Won', 'Currency', THEMESHARK_TXTDOMAIN),
					'custom' => __('Custom', THEMESHARK_TXTDOMAIN),
				],
				'default' => 'dollar',
			]
		);

		$this->add_control(
			'currency_symbol_custom',
			[
				'label' => __('Custom Symbol', THEMESHARK_TXTDOMAIN),
				'type' => CM::TEXT,
				'condition' => [
					'currency_symbol' => 'custom',
				],
			]
		);

		$this->add_control(
			'price',
			[
				'label' => __('Price', THEMESHARK_TXTDOMAIN),
				'type' => CM::TEXT,
				'default' => '39.99',
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$this->add_control(
			'currency_format',
			[
				'label' => __('Currency Format', THEMESHARK_TXTDOMAIN),
				'type' => CM::SELECT,
				'options' => [
					'' => '1,234.56 (Default)',
					',' => '1.234,56',
				],
			]
		);

		$this->add_control(
			'sale',
			[
				'label' => __('Sale', THEMESHARK_TXTDOMAIN),
				'type' => CM::SWITCHER,
				'label_on' => __('On', THEMESHARK_TXTDOMAIN),
				'label_off' => __('Off', THEMESHARK_TXTDOMAIN),
				'default' => '',
			]
		);

		$this->add_control(
			'original_price',
			[
				'label' => __('Original Price', THEMESHARK_TXTDOMAIN),
				'type' => CM::NUMBER,
				'default' => '59',
				'condition' => [
					'sale' => 'yes',
				],
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$this->add_control(
			'period',
			[
				'label' => __('Period', THEMESHARK_TXTDOMAIN),
				'type' => CM::TEXT,
				'default' => __('Monthly', THEMESHARK_TXTDOMAIN),
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_features',
			[
				'label' => __('Features', THEMESHARK_TXTDOMAIN),
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'item_text',
			[
				'label' => __('Text', THEMESHARK_TXTDOMAIN),
				'type' => CM::TEXT,
				'default' => __('List Item', THEMESHARK_TXTDOMAIN),
			]
		);

		$default_icon = [
			'value' => 'far fa-check-circle',
			'library' => 'fa-regular',
		];

		$repeater->add_control(
			'selected_item_icon',
			[
				'label' => __('Icon', THEMESHARK_TXTDOMAIN),
				'type' => CM::ICONS,
				'fa4compatibility' => 'item_icon',
				'default' => $default_icon,
			]
		);

		$repeater->add_control(
			'item_icon_color',
			[
				'label' => __('Icon Color', THEMESHARK_TXTDOMAIN),
				'type' => CM::COLOR,
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}} i' => 'color: {{VALUE}}',
					'{{WRAPPER}} {{CURRENT_ITEM}} svg' => 'fill: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'features_list',
			[
				'type' => CM::REPEATER,
				'fields' => $repeater->get_controls(),
				'default' => [
					[
						'item_text' => __('List Item #1', THEMESHARK_TXTDOMAIN),
						'selected_item_icon' => $default_icon,
					],
					[
						'item_text' => __('List Item #2', THEMESHARK_TXTDOMAIN),
						'selected_item_icon' => $default_icon,
					],
					[
						'item_text' => __('List Item #3', THEMESHARK_TXTDOMAIN),
						'selected_item_icon' => $default_icon,
					],
				],
				'title_field' => '{{{ item_text }}}',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_footer',
			[
				'label' => __('Footer', THEMESHARK_TXTDOMAIN),
			]
		);

		$this->add_control(
			'button_text',
			[
				'label' => __('Button Text', THEMESHARK_TXTDOMAIN),
				'type' => CM::TEXT,
				'default' => __('Click Here', THEMESHARK_TXTDOMAIN),
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$this->add_control(
			'link',
			[
				'label' => __('Link', THEMESHARK_TXTDOMAIN),
				'type' => CM::URL,
				'placeholder' => __('https://your-link.com', THEMESHARK_TXTDOMAIN),
				'default' => [
					'url' => '#',
				],
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$this->add_control(
			'footer_additional_info',
			[
				'label' => __('Additional Info', THEMESHARK_TXTDOMAIN),
				'type' => CM::TEXTAREA,
				'default' => __('This is text element', THEMESHARK_TXTDOMAIN),
				'rows' => 3,
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_ribbon',
			[
				'label' => __('Ribbon', THEMESHARK_TXTDOMAIN),
			]
		);

		$this->add_control(
			'show_ribbon',
			[
				'label' => __('Show', THEMESHARK_TXTDOMAIN),
				'type' => CM::SWITCHER,
				'default' => 'yes',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'ribbon_title',
			[
				'label' => __('Title', THEMESHARK_TXTDOMAIN),
				'type' => CM::TEXT,
				'default' => __('Popular', THEMESHARK_TXTDOMAIN),
				'condition' => [
					'show_ribbon' => 'yes',
				],
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$this->add_control(
			'ribbon_horizontal_position',
			[
				'label' => __('Position', THEMESHARK_TXTDOMAIN),
				'type' => CM::CHOOSE,
				'options' => [
					'left' => [
						'title' => __('Left', THEMESHARK_TXTDOMAIN),
						'icon' => 'eicon-h-align-left',
					],
					'right' => [
						'title' => __('Right', THEMESHARK_TXTDOMAIN),
						'icon' => 'eicon-h-align-right',
					],
				],
				'condition' => [
					'show_ribbon' => 'yes',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_header_style',
			[
				'label' => __('Header', THEMESHARK_TXTDOMAIN),
				'tab' => CM::TAB_STYLE,
				'show_label' => false,
			]
		);


		// SELECTORS
		//-----------------------------------------------
		$header         = '.elementor-price-table__header';
		$header_overlay = '.themeshark-price-card-header-overlay';


		$SC->group_control('header_background', Group_Control_Background::get_type(), [
			'selector' => "{{WRAPPER}} $header"
		]);

		$SC->control('header_overlay', 'Background Overlay', CM::COLOR, [
			'selectors' => $SC::selectors([
				$header_overlay => [
					'background-color: {{VALUE}}'
				]
			]),
		]);

		$SC->control('header_overlay_opacity', 'Overlay Opacity', CM::SLIDER, [
			'range'     => $SC::range(['px', 0, 1, 0.01]),
			'default' 	=> $SC::range_default('px', .5),
			'selectors' => $SC::selectors([
				$header_overlay => [
					'opacity:{{SIZE}}'
				]
			])
		]);


		$this->add_responsive_control(
			'header_padding',
			[
				'label' => __('Padding', THEMESHARK_TXTDOMAIN),
				'type' => CM::DIMENSIONS,
				'size_units' => ['px', '%', 'em'],
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__header' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'heading_heading_style',
			[
				'label' => __('Title', THEMESHARK_TXTDOMAIN),
				'type' => CM::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'heading_color',
			[
				'label' => __('Color', THEMESHARK_TXTDOMAIN),
				'type' => CM::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__heading' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'heading_typography',
				'selector' => '{{WRAPPER}} .elementor-price-table__heading',
				'global' => [
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				],
			]
		);

		$this->add_control(
			'heading_sub_heading_style',
			[
				'label' => __('Sub Title', THEMESHARK_TXTDOMAIN),
				'type' => CM::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'sub_heading_color',
			[
				'label' => __('Color', THEMESHARK_TXTDOMAIN),
				'type' => CM::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__subheading' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'sub_heading_typography',
				'selector' => '{{WRAPPER}} .elementor-price-table__subheading',
				'global' => [
					'default' => Global_Typography::TYPOGRAPHY_SECONDARY,
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_pricing_element_style',
			[
				'label' => __('Pricing', THEMESHARK_TXTDOMAIN),
				'tab' => CM::TAB_STYLE,
				'show_label' => false,
			]
		);

		$this->add_control(
			'pricing_element_bg_color',
			[
				'label' => __('Background Color', THEMESHARK_TXTDOMAIN),
				'type' => CM::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__price' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'pricing_element_padding',
			[
				'label' => __('Padding', THEMESHARK_TXTDOMAIN),
				'type' => CM::DIMENSIONS,
				'size_units' => ['px', '%', 'em'],
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__price' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'price_color',
			[
				'label' => __('Color', THEMESHARK_TXTDOMAIN),
				'type' => CM::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__currency, {{WRAPPER}} .elementor-price-table__integer-part, {{WRAPPER}} .elementor-price-table__fractional-part' => 'color: {{VALUE}}',
				],
				'separator' => 'before',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'price_typography',
				'selector' => '{{WRAPPER}} .elementor-price-table__price',
				'global' => [
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				],
			]
		);

		$this->add_control(
			'heading_currency_style',
			[
				'label' => __('Currency Symbol', THEMESHARK_TXTDOMAIN),
				'type' => CM::HEADING,
				'separator' => 'before',
				'condition' => [
					'currency_symbol!' => '',
				],
			]
		);

		$this->add_control(
			'currency_size',
			[
				'label' => __('Size', THEMESHARK_TXTDOMAIN),
				'type' => CM::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__price > .elementor-price-table__currency' => 'font-size: calc({{SIZE}}em/100)',
				],
				'condition' => [
					'currency_symbol!' => '',
				],
			]
		);

		$this->add_control(
			'currency_position',
			[
				'label' => __('Position', THEMESHARK_TXTDOMAIN),
				'type' => CM::CHOOSE,
				'default' => 'before',
				'options' => [
					'before' => [
						'title' => __('Before', THEMESHARK_TXTDOMAIN),
						'icon' => 'eicon-h-align-left',
					],
					'after' => [
						'title' => __('After', THEMESHARK_TXTDOMAIN),
						'icon' => 'eicon-h-align-right',
					],
				],
			]
		);

		$this->add_control(
			'currency_vertical_position',
			[
				'label' => __('Vertical Position', THEMESHARK_TXTDOMAIN),
				'type' => CM::CHOOSE,
				'options' => [
					'top' => [
						'title' => __('Top', THEMESHARK_TXTDOMAIN),
						'icon' => 'eicon-v-align-top',
					],
					'middle' => [
						'title' => __('Middle', THEMESHARK_TXTDOMAIN),
						'icon' => 'eicon-v-align-middle',
					],
					'bottom' => [
						'title' => __('Bottom', THEMESHARK_TXTDOMAIN),
						'icon' => 'eicon-v-align-bottom',
					],
				],
				'default' => 'top',
				'selectors_dictionary' => [
					'top' => 'flex-start',
					'middle' => 'center',
					'bottom' => 'flex-end',
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__currency' => 'align-self: {{VALUE}}',
				],
				'condition' => [
					'currency_symbol!' => '',
				],
			]
		);

		$this->add_control(
			'fractional_part_style',
			[
				'label' => __('Fractional Part', THEMESHARK_TXTDOMAIN),
				'type' => CM::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'fractional-part_size',
			[
				'label' => __('Size', THEMESHARK_TXTDOMAIN),
				'type' => CM::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__fractional-part' => 'font-size: calc({{SIZE}}em/100)',
				],
			]
		);

		$this->add_control(
			'fractional_part_vertical_position',
			[
				'label' => __('Vertical Position', THEMESHARK_TXTDOMAIN),
				'type' => CM::CHOOSE,
				'options' => [
					'top' => [
						'title' => __('Top', THEMESHARK_TXTDOMAIN),
						'icon' => 'eicon-v-align-top',
					],
					'middle' => [
						'title' => __('Middle', THEMESHARK_TXTDOMAIN),
						'icon' => 'eicon-v-align-middle',
					],
					'bottom' => [
						'title' => __('Bottom', THEMESHARK_TXTDOMAIN),
						'icon' => 'eicon-v-align-bottom',
					],
				],
				'default' => 'top',
				'selectors_dictionary' => [
					'top' => 'flex-start',
					'middle' => 'center',
					'bottom' => 'flex-end',
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__after-price' => 'justify-content: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'heading_original_price_style',
			[
				'label' => __('Original Price', THEMESHARK_TXTDOMAIN),
				'type' => CM::HEADING,
				'separator' => 'before',
				'condition' => [
					'sale' => 'yes',
					'original_price!' => '',
				],
			]
		);

		$this->add_control(
			'original_price_color',
			[
				'label' => __('Color', THEMESHARK_TXTDOMAIN),
				'type' => CM::COLOR,
				'global' => [
					'default' => Global_Colors::COLOR_SECONDARY,
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__original-price' => 'color: {{VALUE}}',
				],
				'condition' => [
					'sale' => 'yes',
					'original_price!' => '',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'original_price_typography',
				'selector' => '{{WRAPPER}} .elementor-price-table__original-price',
				'global' => [
					'default' => Global_Typography::TYPOGRAPHY_PRIMARY,
				],
				'condition' => [
					'sale' => 'yes',
					'original_price!' => '',
				],
			]
		);

		$this->add_control(
			'original_price_vertical_position',
			[
				'label' => __('Vertical Position', THEMESHARK_TXTDOMAIN),
				'type' => CM::CHOOSE,
				'options' => [
					'top' => [
						'title' => __('Top', THEMESHARK_TXTDOMAIN),
						'icon' => 'eicon-v-align-top',
					],
					'middle' => [
						'title' => __('Middle', THEMESHARK_TXTDOMAIN),
						'icon' => 'eicon-v-align-middle',
					],
					'bottom' => [
						'title' => __('Bottom', THEMESHARK_TXTDOMAIN),
						'icon' => 'eicon-v-align-bottom',
					],
				],
				'selectors_dictionary' => [
					'top' => 'flex-start',
					'middle' => 'center',
					'bottom' => 'flex-end',
				],
				'default' => 'bottom',
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__original-price' => 'align-self: {{VALUE}}',
				],
				'condition' => [
					'sale' => 'yes',
					'original_price!' => '',
				],
			]
		);

		$this->add_control(
			'heading_period_style',
			[
				'label' => __('Period', THEMESHARK_TXTDOMAIN),
				'type' => CM::HEADING,
				'separator' => 'before',
				'condition' => [
					'period!' => '',
				],
			]
		);

		$this->add_control(
			'period_color',
			[
				'label' => __('Color', THEMESHARK_TXTDOMAIN),
				'type' => CM::COLOR,
				'global' => [
					'default' => Global_Colors::COLOR_SECONDARY,
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__period' => 'color: {{VALUE}}',
				],
				'condition' => [
					'period!' => '',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'period_typography',
				'selector' => '{{WRAPPER}} .elementor-price-table__period',
				'global' => [
					'default' => Global_Typography::TYPOGRAPHY_SECONDARY,
				],
				'condition' => [
					'period!' => '',
				],
			]
		);

		$this->add_control(
			'period_position',
			[
				'label' => __('Position', THEMESHARK_TXTDOMAIN),
				'type' => CM::SELECT,
				'label_block' => false,
				'options' => [
					'below' => __('Below', THEMESHARK_TXTDOMAIN),
					'beside' => __('Beside', THEMESHARK_TXTDOMAIN),
				],
				'default' => 'below',
				'condition' => [
					'period!' => '',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_features_list_style',
			[
				'label' => __('Features', THEMESHARK_TXTDOMAIN),
				'tab' => CM::TAB_STYLE,
				'show_label' => false,
			]
		);

		$this->add_control(
			'features_list_bg_color',
			[
				'label' => __('Background Color', THEMESHARK_TXTDOMAIN),
				'type' => CM::COLOR,
				'separator' => 'before',
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__features-list' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'features_list_padding',
			[
				'label' => __('Padding', THEMESHARK_TXTDOMAIN),
				'type' => CM::DIMENSIONS,
				'size_units' => ['px', '%', 'em'],
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__features-list' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'features_list_color',
			[
				'label' => __('Color', THEMESHARK_TXTDOMAIN),
				'type' => CM::COLOR,
				'global' => [
					'default' => Global_Colors::COLOR_TEXT,
				],
				'separator' => 'before',
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__features-list' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'features_list_typography',
				'selector' => '{{WRAPPER}} .elementor-price-table__features-list li',
				'global' => [
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				],
			]
		);

		$this->add_control(
			'features_list_alignment',
			[
				'label' => __('Alignment', THEMESHARK_TXTDOMAIN),
				'type' => CM::CHOOSE,
				'options' => [
					'left' => [
						'title' => __('Left', THEMESHARK_TXTDOMAIN),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => __('Center', THEMESHARK_TXTDOMAIN),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => __('Right', THEMESHARK_TXTDOMAIN),
						'icon' => 'eicon-text-align-right',
					],
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__features-list' => 'text-align: {{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'item_width',
			[
				'label' => __('Width', THEMESHARK_TXTDOMAIN),
				'type' => CM::SLIDER,
				'range' => [
					'%' => [
						'min' => 25,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__feature-inner' => 'margin-left: calc((100% - {{SIZE}}%)/2); margin-right: calc((100% - {{SIZE}}%)/2)',
				],
			]
		);

		$this->add_control(
			'list_divider',
			[
				'label' => __('Divider', THEMESHARK_TXTDOMAIN),
				'type' => CM::SWITCHER,
				'default' => 'yes',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'divider_style',
			[
				'label' => __('Style', THEMESHARK_TXTDOMAIN),
				'type' => CM::SELECT,
				'options' => [
					'solid' => __('Solid', THEMESHARK_TXTDOMAIN),
					'double' => __('Double', THEMESHARK_TXTDOMAIN),
					'dotted' => __('Dotted', THEMESHARK_TXTDOMAIN),
					'dashed' => __('Dashed', THEMESHARK_TXTDOMAIN),
				],
				'default' => 'solid',
				'condition' => [
					'list_divider' => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__features-list li:before' => 'border-top-style: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'divider_color',
			[
				'label' => __('Color', THEMESHARK_TXTDOMAIN),
				'type' => CM::COLOR,
				'default' => '#ddd',
				'global' => [
					'default' => Global_Colors::COLOR_TEXT,
				],
				'condition' => [
					'list_divider' => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__features-list li:before' => 'border-top-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'divider_weight',
			[
				'label' => __('Weight', THEMESHARK_TXTDOMAIN),
				'type' => CM::SLIDER,
				'default' => [
					'size' => 2,
					'unit' => 'px',
				],
				'range' => [
					'px' => [
						'min' => 1,
						'max' => 10,
					],
				],
				'condition' => [
					'list_divider' => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__features-list li:before' => 'border-top-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'divider_width',
			[
				'label' => __('Width', THEMESHARK_TXTDOMAIN),
				'type' => CM::SLIDER,
				'condition' => [
					'list_divider' => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__features-list li:before' => 'margin-left: calc((100% - {{SIZE}}%)/2); margin-right: calc((100% - {{SIZE}}%)/2)',
				],
			]
		);

		$this->add_control(
			'divider_gap',
			[
				'label' => __('Gap', THEMESHARK_TXTDOMAIN),
				'type' => CM::SLIDER,
				'default' => [
					'size' => 15,
					'unit' => 'px',
				],
				'range' => [
					'px' => [
						'min' => 1,
						'max' => 50,
					],
				],
				'condition' => [
					'list_divider' => 'yes',
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__features-list li:before' => 'margin-top: {{SIZE}}{{UNIT}}; margin-bottom: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_footer_style',
			[
				'label' => __('Footer', THEMESHARK_TXTDOMAIN),
				'tab' => CM::TAB_STYLE,
				'show_label' => false,
			]
		);

		$this->add_control(
			'footer_bg_color',
			[
				'label' => __('Background Color', THEMESHARK_TXTDOMAIN),
				'type' => CM::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__footer' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'footer_padding',
			[
				'label' => __('Padding', THEMESHARK_TXTDOMAIN),
				'type' => CM::DIMENSIONS,
				'size_units' => ['px', '%', 'em'],
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__footer' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'heading_footer_button',
			[
				'label' => __('Button', THEMESHARK_TXTDOMAIN),
				'type' => CM::HEADING,
				'separator' => 'before',
				'condition' => [
					'button_text!' => '',
				],
			]
		);

		$this->add_control(
			'button_size',
			[
				'label' => __('Size', THEMESHARK_TXTDOMAIN),
				'type' => CM::SELECT,
				'default' => 'md',
				'options' => [
					'xs' => __('Extra Small', THEMESHARK_TXTDOMAIN),
					'sm' => __('Small', THEMESHARK_TXTDOMAIN),
					'md' => __('Medium', THEMESHARK_TXTDOMAIN),
					'lg' => __('Large', THEMESHARK_TXTDOMAIN),
					'xl' => __('Extra Large', THEMESHARK_TXTDOMAIN),
				],
				'condition' => [
					'button_text!' => '',
				],
			]
		);

		$this->start_controls_tabs('tabs_button_style');

		$this->start_controls_tab(
			'tab_button_normal',
			[
				'label' => __('Normal', THEMESHARK_TXTDOMAIN),
				'condition' => [
					'button_text!' => '',
				],
			]
		);

		$this->add_control(
			'button_text_color',
			[
				'label' => __('Text Color', THEMESHARK_TXTDOMAIN),
				'type' => CM::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__button' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'button_typography',
				'global' => [
					'default' => Global_Typography::TYPOGRAPHY_ACCENT,
				],
				'selector' => '{{WRAPPER}} .elementor-price-table__button',
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'button_background',
				'types' => ['classic', 'gradient'],
				'exclude' => ['image'],
				'selector' => '{{WRAPPER}} .elementor-price-table__button',
				'fields_options' => [
					'background' => [
						'default' => 'classic',
					],
					'color' => [
						'global' => [
							'default' => Global_Colors::COLOR_ACCENT,
						],
					],
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'button_border',
				'selector' => '{{WRAPPER}} .elementor-price-table__button',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'button_border_radius',
			[
				'label' => __('Border Radius', THEMESHARK_TXTDOMAIN),
				'type' => CM::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'button_text_padding',
			[
				'label' => __('Text Padding', THEMESHARK_TXTDOMAIN),
				'type' => CM::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_button_hover',
			[
				'label' => __('Hover', THEMESHARK_TXTDOMAIN),
				'condition' => [
					'button_text!' => '',
				],
			]
		);

		$this->add_control(
			'button_hover_color',
			[
				'label' => __('Text Color', THEMESHARK_TXTDOMAIN),
				'type' => CM::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__button:hover' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'button_background_hover',
				'types' => ['classic', 'gradient'],
				'exclude' => ['image'],
				'selector' => '{{WRAPPER}} .elementor-price-table__button:hover',
				'fields_options' => [
					'background' => [
						'default' => 'classic',
					],
				],
			]
		);

		$this->add_control(
			'button_hover_border_color',
			[
				'label' => __('Border Color', THEMESHARK_TXTDOMAIN),
				'type' => CM::COLOR,
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__button:hover' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_hover_animation',
			[
				'label' => __('Animation', THEMESHARK_TXTDOMAIN),
				'type' => CM::HOVER_ANIMATION,
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_control(
			'heading_additional_info',
			[
				'label' => __('Additional Info', THEMESHARK_TXTDOMAIN),
				'type' => CM::HEADING,
				'separator' => 'before',
				'condition' => [
					'footer_additional_info!' => '',
				],
			]
		);

		$this->add_control(
			'additional_info_color',
			[
				'label' => __('Color', THEMESHARK_TXTDOMAIN),
				'type' => CM::COLOR,
				'global' => [
					'default' => Global_Colors::COLOR_TEXT,
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__additional_info' => 'color: {{VALUE}}',
				],
				'condition' => [
					'footer_additional_info!' => '',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'additional_info_typography',
				'selector' => '{{WRAPPER}} .elementor-price-table__additional_info',
				'global' => [
					'default' => Global_Typography::TYPOGRAPHY_TEXT,
				],
				'condition' => [
					'footer_additional_info!' => '',
				],
			]
		);

		$this->add_control(
			'additional_info_margin',
			[
				'label' => __('Margin', THEMESHARK_TXTDOMAIN),
				'type' => CM::DIMENSIONS,
				'size_units' => ['px', '%', 'em'],
				'default' => [
					'top' => 15,
					'right' => 30,
					'bottom' => 0,
					'left' => 30,
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__additional_info' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
				],
				'condition' => [
					'footer_additional_info!' => '',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_ribbon_style',
			[
				'label' => __('Ribbon', THEMESHARK_TXTDOMAIN),
				'tab' => CM::TAB_STYLE,
				'show_label' => false,
				'condition' => [
					'show_ribbon' => 'yes',
				],
			]
		);

		$this->add_control(
			'ribbon_bg_color',
			[
				'label' => __('Background Color', THEMESHARK_TXTDOMAIN),
				'type' => CM::COLOR,
				'global' => [
					'default' => Global_Colors::COLOR_ACCENT,
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__ribbon-inner' => 'background-color: {{VALUE}}',
				],
			]
		);

		$ribbon_distance_transform = is_rtl() ? 'translateY(-50%) translateX({{SIZE}}{{UNIT}}) rotate(-45deg)' : 'translateY(-50%) translateX(-50%) translateX({{SIZE}}{{UNIT}}) rotate(-45deg)';

		$this->add_responsive_control(
			'ribbon_distance',
			[
				'label' => __('Distance', THEMESHARK_TXTDOMAIN),
				'type' => CM::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__ribbon-inner' => 'margin-top: {{SIZE}}{{UNIT}}; transform: ' . $ribbon_distance_transform,
				],
			]
		);

		$this->add_control(
			'ribbon_text_color',
			[
				'label' => __('Text Color', THEMESHARK_TXTDOMAIN),
				'type' => CM::COLOR,
				'default' => '#ffffff',
				'separator' => 'before',
				'selectors' => [
					'{{WRAPPER}} .elementor-price-table__ribbon-inner' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'ribbon_typography',
				'selector' => '{{WRAPPER}} .elementor-price-table__ribbon-inner',
				'global' => [
					'default' => Global_Typography::TYPOGRAPHY_ACCENT,
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'box_shadow',
				'selector' => '{{WRAPPER}} .elementor-price-table__ribbon-inner',
			]
		);

		$this->end_controls_section();
	}

	private function render_currency_symbol($symbol, $location)
	{
		$currency_position = $this->get_settings('currency_position');
		$location_setting = !empty($currency_position) ? $currency_position : 'before';
		if (!empty($symbol) && $location === $location_setting) {

			echo '<span class="elementor-price-table__currency">' . $symbol . '</span>';
		}
	}

	private function get_currency_symbol($symbol_name)
	{
		$symbols = [
			'dollar' => '&#36;',
			'euro' => '&#128;',
			'franc' => '&#8355;',
			'pound' => '&#163;',
			'ruble' => '&#8381;',
			'shekel' => '&#8362;',
			'baht' => '&#3647;',
			'yen' => '&#165;',
			'won' => '&#8361;',
			'guilder' => '&fnof;',
			'peso' => '&#8369;',
			'peseta' => '&#8359',
			'lira' => '&#8356;',
			'rupee' => '&#8360;',
			'indian_rupee' => '&#8377;',
			'real' => 'R$',
			'krona' => 'kr',
		];

		return isset($symbols[$symbol_name]) ? $symbols[$symbol_name] : '';
	}

	protected function render()
	{
		$settings = $this->get_settings_for_display();
		$symbol = '';

		if (!empty($settings['currency_symbol'])) {
			if ('custom' !== $settings['currency_symbol']) {
				$symbol = $this->get_currency_symbol($settings['currency_symbol']);
			} else {
				$symbol = $settings['currency_symbol_custom'];
			}
		}
		$currency_format = empty($settings['currency_format']) ? '.' : $settings['currency_format'];
		$price = explode($currency_format, $settings['price']);
		$intpart = $price[0];
		$fraction = '';
		if (2 === count($price)) {
			$fraction = $price[1];
		}

		$this->add_render_attribute('button_text', 'class', [
			'elementor-price-table__button',
			'elementor-button',
			'elementor-size-' . $settings['button_size'],
		]);

		if (!empty($settings['link']['url'])) {
			$this->add_link_attributes('button_text', $settings['link']);
		}

		if (!empty($settings['button_hover_animation'])) {
			$this->add_render_attribute('button_text', 'class', 'elementor-animation-' . $settings['button_hover_animation']);
		}

		$this->add_render_attribute('heading', 'class', 'elementor-price-table__heading');
		$this->add_render_attribute('sub_heading', 'class', 'elementor-price-table__subheading');
		$this->add_render_attribute('period', 'class', ['elementor-price-table__period', 'elementor-typo-excluded']);
		$this->add_render_attribute('footer_additional_info', 'class', 'elementor-price-table__additional_info');
		$this->add_render_attribute('ribbon_title', 'class', 'elementor-price-table__ribbon-inner');

		$this->add_inline_editing_attributes('heading', 'none');
		$this->add_inline_editing_attributes('sub_heading', 'none');
		$this->add_inline_editing_attributes('period', 'none');
		$this->add_inline_editing_attributes('footer_additional_info');
		$this->add_inline_editing_attributes('button_text');
		$this->add_inline_editing_attributes('ribbon_title');

		$period_position = $settings['period_position'];
		$period_element = '<span ' . $this->get_render_attribute_string('period') . '>' . $settings['period'] . '</span>';
		$heading_tag = $settings['heading_tag'];

		$migration_allowed = Icons_Manager::is_migration_allowed();
?>

		<div class="themeshark-price-card elementor-price-table">
			<?php if ($settings['heading'] || $settings['sub_heading']) : ?>
				<div class="elementor-price-table__header">
					<?php if (!empty($settings['heading'])) : ?>
						<div class='themeshark-price-card-header-overlay'></div>
						<<?php echo Utils::validate_html_tag($heading_tag) . ' ' . $this->get_render_attribute_string('heading'); ?>>
							<?php esc_html_e($settings['heading']) ?>
						</<?php echo Utils::validate_html_tag($heading_tag); ?>>
					<?php endif; ?>

					<?php if (!empty($settings['sub_heading'])) : ?>
						<span <?php $this->print_render_attribute_string('sub_heading'); ?>><?php echo Helpers::esc_wysiwyg($settings['sub_heading']); ?></span>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<div class="elementor-price-table__price">
				<?php if ('yes' === $settings['sale'] && !empty($settings['original_price'])) : ?>
					<div class="elementor-price-table__original-price elementor-typo-excluded">
						<?php
						$this->render_currency_symbol($symbol, 'before');
						esc_html_e($settings['original_price']);
						$this->render_currency_symbol($symbol, 'after');
						?>
					</div>
				<?php endif; ?>
				<?php $this->render_currency_symbol($symbol, 'before'); ?>
				<?php if (!empty($intpart) || 0 <= $intpart) : ?>
					<span class="elementor-price-table__integer-part"><?php esc_html_e($intpart); ?></span>
				<?php endif; ?>

				<?php if ('' !== $fraction || (!empty($settings['period']) && 'beside' === $period_position)) : ?>
					<div class="elementor-price-table__after-price">
						<span class="elementor-price-table__fractional-part"><?php esc_html_e($fraction); ?></span>

						<?php if (!empty($settings['period']) && 'beside' === $period_position) : ?>
							<?php echo $period_element; ?>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php $this->render_currency_symbol($symbol, 'after'); ?>

				<?php if (!empty($settings['period']) && 'below' === $period_position) : ?>
					<?php echo $period_element; ?>
				<?php endif; ?>
			</div>

			<?php if (!empty($settings['features_list'])) : ?>
				<ul class="elementor-price-table__features-list">
					<?php
					foreach ($settings['features_list'] as $index => $item) :
						$repeater_setting_key = $this->get_repeater_setting_key('item_text', 'features_list', $index);
						$this->add_inline_editing_attributes($repeater_setting_key);

						$migrated = isset($item['__fa4_migrated']['selected_item_icon']);
						// add old default
						if (!isset($item['item_icon']) && !$migration_allowed) {
							$item['item_icon'] = 'fa fa-check-circle';
						}
						$is_new = !isset($item['item_icon']) && $migration_allowed;
					?>
						<li class="elementor-repeater-item-<?php esc_attr_e($item['_id']); ?>">
							<div class="elementor-price-table__feature-inner">
								<?php if (!empty($item['item_icon']) || !empty($item['selected_item_icon'])) :
									if ($is_new || $migrated) :
										Icons_Manager::render_icon($item['selected_item_icon'], ['aria-hidden' => 'true']);
									else : ?>
										<i class="<?php esc_attr_e($item['item_icon']); ?>" aria-hidden="true"></i>
								<?php
									endif;
								endif; ?>
								<?php if (!empty($item['item_text'])) : ?>
									<span <?php $this->print_render_attribute_string($repeater_setting_key); ?>>
										<?php esc_html_e($item['item_text']); ?>
									</span>
								<?php
								else :
									echo '&nbsp;';
								endif;
								?>
							</div>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<?php if (!empty($settings['button_text']) || !empty($settings['footer_additional_info'])) : ?>
				<div class="elementor-price-table__footer">
					<?php if (!empty($settings['button_text'])) : ?>
						<a <?php $this->print_render_attribute_string('button_text'); ?>><span class="themeshark-price-card-button-text-inner"><?php esc_html_e($settings['button_text']); ?></span></a>
					<?php endif; ?>

					<?php if (!empty($settings['footer_additional_info'])) : ?>
						<div <?php $this->print_render_attribute_string('footer_additional_info'); ?>><?php echo Helpers::esc_wysiwyg($settings['footer_additional_info']); ?></div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>

		<?php
		if ('yes' === $settings['show_ribbon'] && !empty($settings['ribbon_title'])) :
			$this->add_render_attribute('ribbon-wrapper', 'class', 'elementor-price-table__ribbon');

			if (!empty($settings['ribbon_horizontal_position'])) :
				$this->add_render_attribute('ribbon-wrapper', 'class', 'elementor-ribbon-' . $settings['ribbon_horizontal_position']);
			endif;

		?>
			<div <?php $this->print_render_attribute_string('ribbon-wrapper'); ?>>
				<div <?php $this->print_render_attribute_string('ribbon_title'); ?>><?php esc_html_e($settings['ribbon_title']); ?></div>
			</div>
		<?php
		endif;
	}

	/**
	 * Render Price Table widget output in the editor.
	 *
	 * Written as a Backbone JavaScript template and used to generate the live preview.
	 *
	 * @since 2.9.0
	 * @access protected
	 */
	protected function content_template()
	{
		?>
		<# var symbols={ dollar: '&#36;' , euro: '&#128;' , franc: '&#8355;' , pound: '&#163;' , ruble: '&#8381;' , shekel: '&#8362;' , baht: '&#3647;' , yen: '&#165;' , won: '&#8361;' , guilder: '&fnof;' , peso: '&#8369;' , peseta: '&#8359;' , lira: '&#8356;' , rupee: '&#8360;' , indian_rupee: '&#8377;' , real: 'R$' , krona: 'kr' }; var symbol='' , iconsHTML={}; if ( settings.currency_symbol ) { if ( 'custom' !==settings.currency_symbol ) { symbol=symbols[ settings.currency_symbol ] || '' ; } else { symbol=settings.currency_symbol_custom; } } var buttonClasses='elementor-price-table__button elementor-button elementor-size-' + settings.button_size; if ( settings.button_hover_animation ) { buttonClasses +=' elementor-animation-' + settings.button_hover_animation; } view.addRenderAttribute( 'heading' , 'class' , 'elementor-price-table__heading' ); view.addRenderAttribute( 'sub_heading' , 'class' , 'elementor-price-table__subheading' ); view.addRenderAttribute( 'period' , 'class' , ['elementor-price-table__period', 'elementor-typo-excluded' ] ); view.addRenderAttribute( 'footer_additional_info' , 'class' , 'elementor-price-table__additional_info' ); view.addRenderAttribute( 'button_text' , 'class' , buttonClasses ); view.addRenderAttribute( 'ribbon_title' , 'class' , 'elementor-price-table__ribbon-inner' ); view.addInlineEditingAttributes( 'heading' , 'none' ); view.addInlineEditingAttributes( 'sub_heading' , 'none' ); view.addInlineEditingAttributes( 'period' , 'none' ); view.addInlineEditingAttributes( 'footer_additional_info' ); view.addInlineEditingAttributes( 'button_text' ); view.addInlineEditingAttributes( 'ribbon_title' ); var currencyFormat=settings.currency_format || '.' , price=settings.price.split( currencyFormat ), intpart=price[0], fraction=price[1], periodElement='<span ' + view.getRenderAttributeString( "period" ) + '>' + settings.period + '</span>' ; #>
			<div class="themeshark-price-card elementor-price-table">
				<# if ( settings.heading || settings.sub_heading ) { #>
					<div class="elementor-price-table__header">
						<div class='themeshark-price-card-header-overlay'></div>
						<# if ( settings.heading ) { #>
							<# var headingTag=settings.heading_tag #>
								<{{ headingTag }} {{{ view.getRenderAttributeString( 'heading' ) }}}>{{{ settings.heading }}}</{{ headingTag }}>
								<# } #>
									<# if ( settings.sub_heading ) { #>
										<span {{{ view.getRenderAttributeString( 'sub_heading' ) }}}>{{{ settings.sub_heading }}}</span>
										<# } #>
					</div>
					<# } #>

						<div class="elementor-price-table__price">
							<# if ( settings.sale && settings.original_price ) { #>
								<div class="elementor-price-table__original-price elementor-typo-excluded">
									<# if ( ! _.isEmpty( symbol ) && ( 'before'==settings.currency_position || _.isEmpty( settings.currency_position ) ) ) { #>
										<span class="elementor-price-table__currency">{{{ symbol }}}</span>{{{ settings.original_price }}}
										<# } #>
											<# /* The duplicate usage of the original price setting in the "if blocks" is to avoid whitespace between the number and the symbol. */ if ( _.isEmpty( symbol ) ) { #>
												{{{ settings.original_price }}}
												<# } #>
													<# if ( ! _.isEmpty( symbol ) && 'after'==settings.currency_position ) { #>
														{{{ settings.original_price }}}<span class="elementor-price-table__currency">{{{ symbol }}}</span>
														<# } #>
								</div>
								<# } #>

									<# if ( ! _.isEmpty( symbol ) && ( 'before'==settings.currency_position || _.isEmpty( settings.currency_position ) ) ) { #>
										<span class="elementor-price-table__currency elementor-currency--before">{{{ symbol }}}</span>
										<# } #>
											<# if ( intpart ) { #>
												<span class="elementor-price-table__integer-part">{{{ intpart }}}</span>
												<# } #>
													<div class="elementor-price-table__after-price">
														<# if ( fraction ) { #>
															<span class="elementor-price-table__fractional-part">{{{ fraction }}}</span>
															<# } #>
																<# if ( settings.period && 'beside'===settings.period_position ) { #>
																	{{{ periodElement }}}
																	<# } #>
													</div>

													<# if ( ! _.isEmpty( symbol ) && 'after'==settings.currency_position ) { #>
														<span class="elementor-price-table__currency elementor-currency--after">{{{ symbol }}}</span>
														<# } #>

															<# if ( settings.period && 'below'===settings.period_position ) { #>
																{{{ periodElement }}}
																<# } #>
						</div>

						<# if ( settings.features_list ) { #>
							<ul class="elementor-price-table__features-list">
								<# _.each( settings.features_list, function( item, index ) { var featureKey=view.getRepeaterSettingKey( 'item_text' , 'features_list' , index ), migrated=elementor.helpers.isIconMigrated( item, 'selected_item_icon' ); view.addInlineEditingAttributes( featureKey ); #>

									<li class="elementor-repeater-item-{{ item._id }}">
										<div class="elementor-price-table__feature-inner">
											<# if ( item.item_icon || item.selected_item_icon ) { iconsHTML[ index ]=elementor.helpers.renderIcon( view, item.selected_item_icon, { 'aria-hidden' : 'true' }, 'i' , 'object' ); if ( ( ! item.item_icon || migrated ) && iconsHTML[ index ] && iconsHTML[ index ].rendered ) { #>
												{{{ iconsHTML[ index ].value }}}
												<# } else { #>
													<i class="{{ item.item_icon }}" aria-hidden="true"></i>
													<# } } #>
														<# if ( ! _.isEmpty( item.item_text.trim() ) ) { #>
															<span {{{ view.getRenderAttributeString( featureKey ) }}}>{{{ item.item_text }}}</span>
															<# } else { #>
																&nbsp;
																<# } #>
										</div>
									</li>
									<# } ); #>
							</ul>
							<# } #>

								<# if ( settings.button_text || settings.footer_additional_info ) { #>
									<div class="elementor-price-table__footer">
										<# if ( settings.button_text ) { #>
											<a href="#" {{{ view.getRenderAttributeString( 'button_text' ) }}}><span class="themeshark-price-card-button-text-inner">{{{ settings.button_text }}}</span></a>
											<# } #>
												<# if ( settings.footer_additional_info ) { #>
													<p {{{ view.getRenderAttributeString( 'footer_additional_info' ) }}}>{{{ settings.footer_additional_info }}}</p>
													<# } #>
									</div>
									<# } #>
			</div>

			<# if ( 'yes'===settings.show_ribbon && settings.ribbon_title ) { var ribbonClasses='elementor-price-table__ribbon' ; if ( settings.ribbon_horizontal_position ) { ribbonClasses +=' elementor-ribbon-' + settings.ribbon_horizontal_position; } #>
				<div class="{{ ribbonClasses }}">
					<div {{{ view.getRenderAttributeString( 'ribbon_title' ) }}}>{{{ settings.ribbon_title }}}</div>
				</div>
				<# } #>
			<?php
		}

		public function get_group_name()
		{
			return 'pricing';
		}
	}
