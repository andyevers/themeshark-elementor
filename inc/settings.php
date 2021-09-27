<?php

namespace Themeshark_Elementor\Inc;

use Themeshark_Elementor\Widgets;


if (!defined('ABSPATH')) exit;

/**
 * ThemeShark Settings
 *
 * @since 1.0.0
 */

final class Settings
{
    const OPTION_SETTINGS_GENERAL = 'themeshark_elementor_settings_general';
    const OPTION_INSTALL_DATE     = 'themeshark_elementor_install_date';

    // CSS PRELOAD VARS
    //-----------------------------------------------
    // preload css vars should start with preload_css_ and end with widget get_name() with - replaced with _

    const _PREFIX_PRELOAD_CSS  = 'preload_css_';

    const PRELOAD_CSS_ALL      = self::_PREFIX_PRELOAD_CSS . '_all';
    const USAGE_DOWNLOAD_EMAIL = 'usage_download_email';


    public static function get_preload_css_option_name($widget_class)
    {
        $formatted_name = str_replace('-', '_', $widget_class::NAME);
        return self::_PREFIX_PRELOAD_CSS . $formatted_name;
    }


    public static function get_preload_css_option($widget_class)
    {
        $preload_css_parent_option = self::OPTION_SETTINGS_GENERAL;
        $option_name = self::get_preload_css_option_name($widget_class);
        return self::get_child_option($preload_css_parent_option, $option_name);
    }


    public static function get_child_option($parent_option_name, $child_option_name)
    {
        $parent_option = get_option($parent_option_name);
        $child_option = isset($parent_option[$child_option_name]) ? $parent_option[$child_option_name] : null;
        return $child_option;
    }


    public static function get_setting_general($setting_name = null)
    {
        if ($setting_name === null) {
            return get_option(self::OPTION_SETTINGS_GENERAL);
        }

        return self::get_child_option(self::OPTION_SETTINGS_GENERAL, $setting_name);
    }


    public static function get_allowed_shared_data()
    {
        $current_user        = wp_get_current_user();
        $email               = $current_user->user_email;
        $allowed_email_share = self::get_setting_general(self::USAGE_DOWNLOAD_EMAIL) === 'yes';

        $allowed_data = [
            'email' => $allowed_email_share ? $email : null
        ];

        return $allowed_data;
    }
}
