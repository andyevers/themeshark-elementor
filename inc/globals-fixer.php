<?php

namespace Themeshark_Elementor\Inc;

use Elementor\Core\Kits\Documents\Tabs\Global_Colors as GC;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography as GT;

if (!defined('ABSPATH')) exit;

/**
 * Ever since mid 2020, elementor broke their global colors & typography. They still have not fixed it, so this is a temporary fix.
 */
final class Globals_Fixer
{
    /**
     * Key used to mark whether a control with a global variable should be fixed
     */
    const FIX = 'themeshark_global_fix';

    /**
     * If this is true, all globals will be fixed regardless of whether 'themeshark_global_fix' key is present
     */
    private static $fix_all = false;

    public $global_styles = [
        GC::COLOR_PRIMARY           => 'var(--e-global-color-primary)',
        GC::COLOR_SECONDARY         => 'var(--e-global-color-secondary)',
        GC::COLOR_TEXT              => 'var(--e-global-color-text)',
        GC::COLOR_ACCENT            => 'var(--e-global-color-accent)',
        GT::TYPOGRAPHY_PRIMARY      => 'var(--e-global-typography-primary-font-family)',
        GT::TYPOGRAPHY_SECONDARY    => 'var(--e-global-typography-secondary-font-family)',
        GT::TYPOGRAPHY_TEXT         => 'var(--e-global-typography-text-font-family)',
        GT::TYPOGRAPHY_ACCENT       => 'var(--e-global-typography-accent-font-family)',
    ];


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
        add_action('elementor/element/before_section_end', [$this, 'fix_globals'], 10, 2);
    }


    /**
     * Inserts a hidden control after the fixed control that holds the CSS variable for the global color/font.
     */
    public function fix_globals($element, $section_id)
    {
        $controls = $element->get_section_controls($section_id);

        foreach ($controls as $id => $settings) {
            if (!isset($settings[self::FIX]) && self::$fix_all === false) continue;

            if (!isset($settings['global'])) continue;
            $global = $settings['global'];

            if (!isset($global['default'])) continue;
            $default = $global['default'];

            if (!isset($settings['selectors'])) continue;
            $selectors = $settings['selectors'];

            if (!isset($this->global_styles[$default])) continue;
            $css_var = $this->global_styles[$default];

            $condition = isset($settings['condition']) ? $settings['condition'] : [];
            $condition[$id] = ''; //only active if global setting has no value

            // add hidden control holding the default value
            $element->add_control("_global_fix_$id", [
                'type' => \Elementor\Controls_Manager::HIDDEN,
                'default' => $css_var,
                'condition' => $condition,
                'selectors' => $selectors
            ], [
                'position' => [
                    'type' => 'control',
                    'at' => 'after',
                    'of' => $id
                ]
            ]);
        }
    }
}
