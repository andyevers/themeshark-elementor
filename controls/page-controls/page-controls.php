<?php

namespace Themeshark_Elementor\Controls;

use \Elementor\Controls_Manager as CM;
use \Themeshark_Elementor\Inc\Shorthand_Controls;

if (!defined('ABSPATH')) exit;

/**
 * ThemeShark Sticky Controls
 *
 * Adds sticky controls to already existing widgets/elements
 *
 * @since 1.0.0
 */
final class Page_Controls
{
    private static $_instance = null;

    public static function register()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct()
    {
        // Page Controls
        add_action('elementor/element/wp-page/section_page_style/before_section_end', [$this, 'inject_page_controls']);
    }

    public function inject_page_controls($element)
    {
        $SC = new Shorthand_Controls($element);

        $SC->control('hide_page_overflow_x', 'Hide Overflow X', CM::SWITCHER, [
            'render_type' => 'ui',
            'default' => '',
            'return_value' => 'yes',
            'classes' => 'themeshark-control-icon',
            'separator' => 'before',
            'selectors' => [
                'html' => 'overflow-x:hidden;',
                'body' => 'overflow-x:hidden; overflow-y:scroll;'
            ]
        ]);
    }
}
