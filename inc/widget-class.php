<?php

namespace Themeshark_Elementor\Inc;

use \Elementor\Widget_Base;
use \Themeshark_Elementor\Inc\Settings;
use \Themeshark_Elementor\Inc\Shorthand_Controls;
use \Themeshark_Elementor\Inc\Helpers;
use Themeshark_Elementor\Plugin as Themeshark;


if (!defined('ABSPATH')) exit; // Exit if accessed directly

abstract class TS_Widget extends Widget_Base
{
    public static function register()
    {
        $widget_class = get_called_class();

        $register_styles         = "$widget_class::register_styles";
        $register_scripts        = "$widget_class::register_scripts";
        $editor_scripts          = "$widget_class::editor_scripts";
        $localize_editor_scripts = "$widget_class::localize_editor_scripts";
        $localize_scripts        = "$widget_class::localize_scripts";

        add_action('elementor/frontend/after_enqueue_styles', $register_styles);
        add_action('elementor/frontend/after_register_scripts', $register_scripts);
        add_action('elementor/frontend/after_register_scripts', $localize_scripts);

        add_action('elementor/editor/before_enqueue_scripts', $editor_scripts);
        add_action('elementor/editor/before_enqueue_scripts', $localize_editor_scripts);
        add_action('elementor/editor/before_enqueue_scripts', $localize_scripts);

        \Themeshark_Elementor\Plugin::$instance->registered_widgets[] = $widget_class;
    }

    public static function editor_scripts()
    {
    }



    /**
     * adds action fired on "elementor/widget/{widgetname}/$section_id/before_section_end"
     * 
     * @param $section_id id set in $this->add_controls_section
     * @param $callback function called before section end
     */
    public function add_section_action($section_id, $callback)
    {
        $name = $this->get_name();
        $action = "elementor/element/$name/$section_id/before_section_end";
        add_action($action, $callback);
    }

    private $_SC = null;

    /**
     * @return \Themeshark_Elementor\Inc\Shorthand_Controls
     */
    public function shorthand_controls()
    {
        if (is_null($this->_SC)) $this->_SC = new Shorthand_Controls($this);
        return $this->_SC;
    }

    public function __construct($data = [], $args = null)
    {
        $this->on_before_construct();
        parent::__construct($data, $args);

        $this->check_preload_css();
        $this->on_after_construct();
    }

    private function check_preload_css()
    {
        $class_name            = get_called_class();
        $is_preload_css_widget = Settings::get_preload_css_option($class_name) === 'yes';
        $is_preload_css_all    = Settings::get_child_option(Settings::OPTION_SETTINGS_GENERAL, Settings::PRELOAD_CSS_ALL) === 'yes';

        if ($is_preload_css_all || $is_preload_css_widget) {
            foreach ($this->get_style_depends() as $style) wp_enqueue_style($style);
        }
    }

    /** wp_localize_script fired here in both edit mode and on frontend. accessible using themesharkLocalizedData.MY_KEY */
    public static function localize_scripts()
    {
    }

    /** wp_localize_script fired here in edit mode. accessible using themesharkLocalizedData.MY_KEY */
    public static function localize_editor_scripts()
    {
    }

    /** 
     * returns the url for directory 
     * @param {__DIR__} $dir __DIR__
     * @param {String} $url_extension addon to url ex: 'some-extension.css' = https://mysite.com/my-dir/some-extension.css
     */
    public static function get_dir_url($dir, $url_extension = null)
    {
        return Helpers::get_dir_url($dir, $url_extension);
    }


    /**
     * adds localized js data to themesharkLocalizedData object. 
     * 
     * @param {String} $key themesharkLocalizedData[$key] 
     * @param {String|Array} $data themesharkLocalizedData[$key] = $data
     */
    public static function localize_script($key, $data)
    {
        Themeshark::$instance->localized_scripts[$key] = $data;
    }

    public function on_after_construct()
    {
    }


    public function on_before_construct()
    {
    }

    public static function register_scripts()
    {
    }

    public static function register_styles()
    {
    }


    public function get_categories()
    {
        return ['themeshark'];
    }


    public static function get_class()
    {
        return get_called_class();
    }
    /**
     * Adds themeshark default keywords 
     */
    public static function keywords($keywords)
    {
        $default_keywords = ['themeshark ', 'ts '];
        return array_merge($default_keywords, $keywords);
    }

    public static function editor_script($handle, $src, $deps = [])
    {
        $default_deps = ['ts-controls-handler'];
        wp_register_script($handle, $src, array_merge($default_deps, $deps), false, false);
        \Themeshark_Elementor\Plugin::$instance->editor_scripts[] = $handle;
    }

    public static function widget_script($handle, $src, $deps = [])
    {
        $default_deps = ['ts-frontend', 'ts-functions', 'elementor-frontend'];
        wp_register_script($handle, $src, array_merge($default_deps, $deps), false, true);
    }

    public static function widget_style($handle, $src, $deps = [])
    {
        $default_deps = ['ts-common'];
        wp_register_style($handle, $src, array_merge($default_deps, $deps));
    }

    public function get_title()
    {
        $widget_class = get_called_class();
        if (!defined("$widget_class::TITLE")) {
            wp_die("$widget_class is required to have constant TITLE");
        }
        return __($widget_class::TITLE, THEMESHARK_TXTDOMAIN);
    }

    public function get_name()
    {
        $widget_class = get_called_class();
        if (!defined("$widget_class::NAME")) {
            wp_die("$widget_class is required to have constant NAME");
        }
        return $widget_class::NAME;
    }
}
