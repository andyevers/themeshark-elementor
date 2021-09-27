<?php

namespace Themeshark_Elementor\Pages;

use \Themeshark_Elementor\Inc\Settings;
use Themeshark_Elementor\Inc\Helpers;
use Themeshark_Elementor\Inc\WP_Settings_Page_Trait;

if (!defined('ABSPATH')) exit;

/**
 * ThemeShark Template Library Page
 *
 * Lists all templates that are available for download from the ThemeShark server
 * by sending ajax request to server
 * 
 * @since 1.0.0
 */
class Page_Settings extends Admin_Page
{
    use WP_Settings_Page_Trait;

    const SLUG = 'themeshark-settings';


    public function get_option()
    {
        return Settings::OPTION_SETTINGS_GENERAL;
    }

    public function get_slug()
    {
        return self::SLUG;
    }

    protected function get_title()
    {
        return self::_('Settings');
    }

    public function get_style_depends()
    {
        wp_register_style('ts-settings-page', Helpers::get_dir_url(__DIR__, 'settings.css'));
        return ['ts-settings-page'];
    }

    public function on_before_construct()
    {
        add_action('admin_init', array($this, 'register_settings_fields'));
    }

    protected function register_fields()
    {
        $this->start_fields_section('preload_css', 'Preload CSS', [
            'description' => self::_('Select the ThemeShark widgets that you would like to preload CSS for. Note that proloading a lot of CSS may increase page loading times.'),
            'field_options' => [
                'class' => 'ts-field-tight'
            ]
        ]);

        $this->field_check(Settings::PRELOAD_CSS_ALL, 'Preload All CSS');

        //create preload css option for each widget
        $widget_classes = \Themeshark_Elementor\Plugin::get_widget_classes();
        foreach ($widget_classes as $widget_class) {
            $preload_css_option = Settings::get_preload_css_option_name($widget_class);
            $this->field_check($preload_css_option, $widget_class::TITLE);
        }

        $this->end_fields_section();


        $this->start_fields_section('usage_sharing', 'Usage Sharing', [
            'description' => self::_('Share your data with ThemeShark to allow for general improvements. All of your data is kept private and secure')
        ]);

        $this->field_check(Settings::USAGE_DOWNLOAD_EMAIL, 'Template Downloads Email', [
            'description' => self::_('Allow ThemeShark to see which templates you download by sharing your email address to recommend future templates and improve your usage expierence.')
        ]);



        $this->end_fields_section();
    }

    public function render()
    {
?>
        <div class="wrap">

            <h1><?php echo $this->get_title(); ?></h1>

            <form <?php echo $this->get_form_attribute_string(); ?>>

                <?php $this->do_fields_section('preload_css'); ?>
                <hr>
                <?php $this->do_fields_section('usage_sharing'); ?>

                <?php submit_button(); ?>

            </form>
        </div>
<?php
    }
}
