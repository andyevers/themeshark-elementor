<?php

/**
 * Plugin Name: ThemeShark Templates & Widgets for Elementor
 * Description: Access 150+ pre-made Elementor templates and use custom widgets to take your website to the next level. Animations, Amazing Designs, & All you need.
 * Plugin URI: https://wordpress.org/plugins/themeshark-elementor/
 * Author: ThemeShark
 * Author URI: https://themeshark.com
 * Version: 1.1.7
 * Text Domain: themeshark-elementor
 * License: GPL3
 */


if (!defined('ABSPATH')) exit;

define('THEMESHARK__FILE__', __FILE__);
define('THEMESHARK_PATH', plugin_dir_path(__FILE__));
define('THEMESHARK_URL', plugins_url('/', __FILE__));
define('THEMESHARK_TXTDOMAIN', 'themeshark-elementor');

use \Themeshark_Elementor\Plugin;
use \Themeshark_Elementor\Inc\Import_Manager;
use \Themeshark_Elementor\Inc\Settings;

/**
 * ThemeShark Elementor
 *
 * Checks for all requirements & defines constants 
 * before initializing plugin. 
 * 
 * @since 1.0.0
 */
final class Themeshark_Elementor
{
    const VERSION                   = '1.1.7';
    const MINIMUM_ELEMENTOR_VERSION = '3.2.0';
    const MINIMUM_PHP_VERSION       = '7.0';

    const REQUEST_URL = 'https://themeshark.com';
    const REST_ROUTE  = self::REQUEST_URL . '/wp-json/themeshark/templates/v1';
    const RATING_URL  = 'https://wordpress.org/support/plugin/themeshark-elementor/reviews';
    const PATREON_URL = 'https://www.patreon.com/themeshark';

    private static $_instance = null;

    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }


    public function __construct()
    {
        // Load translation
        add_action('init', array($this, 'i18n'));
        add_action('plugins_loaded', array($this, 'init'));
    }
    public function i18n()
    {
        load_plugin_textdomain(THEMESHARK_TXTDOMAIN);
    }

    public function init()
    {
        // Check if Elementor installed and activated
        if (!did_action('elementor/loaded')) {
            add_action('admin_notices', [$this, 'admin_notice_missing_main_plugin']);
            return;
        }

        // Check for required Elementor version
        if (!version_compare(ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=')) {
            add_action('admin_notices', [$this, 'admin_notice_minimum_elementor_version']);
            return;
        }

        // Check for required PHP version
        if (version_compare(PHP_VERSION, self::MINIMUM_PHP_VERSION, '<')) {
            add_action('admin_notices', [$this, 'admin_notice_minimum_php_version']);
            return;
        }

        $this->require_files();
        Import_Manager::register();
        $this->ensure_install_date();
        new Plugin();
    }

    public function ensure_install_date()
    {
        $install_date_option = Settings::OPTION_INSTALL_DATE;
        $install_date = get_option($install_date_option);
        if (empty($install_date)) add_option($install_date_option, time());
        $install_date = get_option($install_date_option);
    }

    public function require_files()
    {
        require_once 'inc/errors.php';
        require_once 'inc/settings.php';
        require_once 'inc/helpers.php';
        require_once 'pages/page-manager.php';
        require_once 'inc/import-manager.php';
        require_once 'inc/shorthand-controls.php';
        require_once 'inc/globals-fixer.php';
        require_once 'inc/admin-notices/admin-messages.php';
        require_once 'controls/controls-handler/controls-handler.php';
        require_once 'controls/posts-query/query-module.php';
        require_once 'inc/templates-manager.php';
        require_once 'plugin.php';
    }

    public function admin_notice_missing_main_plugin()
    {
        if (isset($_GET['activate'])) unset($_GET['activate']);

        $message = sprintf(
            /* translators: 1: Plugin name 2: Elementor */
            esc_html__('"%1$s" requires "%2$s" to be installed and activated.', THEMESHARK_TXTDOMAIN),
            '<strong>' . esc_html__('ThemeShark Templates & Widgets for Elementor', THEMESHARK_TXTDOMAIN) . '</strong>',
            '<strong>' . esc_html__('Elementor', THEMESHARK_TXTDOMAIN) . '</strong>'
        );

        printf('<div class="notice notice-error"><p>%1$s</p></div>', $message);
    }

    public function admin_notice_minimum_elementor_version()
    {
        if (isset($_GET['activate'])) unset($_GET['activate']);

        $message = sprintf(
            /* translators: 1: Plugin name 2: Elementor 3: Required Elementor version */
            esc_html__('"%1$s" requires "%2$s" version %3$s or greater.', THEMESHARK_TXTDOMAIN),
            '<strong>' . esc_html__('ThemeShark Templates & Widgets for Elementor', THEMESHARK_TXTDOMAIN) . '</strong>',
            '<strong>' . esc_html__('Elementor', THEMESHARK_TXTDOMAIN) . '</strong>',
            self::MINIMUM_ELEMENTOR_VERSION
        );

        printf('<div class="notice notice-error"><p>%1$s</p></div>', $message);
    }

    public function admin_notice_minimum_php_version()
    {
        if (isset($_GET['activate'])) unset($_GET['activate']);

        $message = sprintf(
            /* translators: 1: Plugin name 2: PHP 3: Required PHP version */
            esc_html__('"%1$s" requires "%2$s" version %3$s or greater.', THEMESHARK_TXTDOMAIN),
            '<strong>' . esc_html__('ThemeShark Templates & Widgets for Elementor', THEMESHARK_TXTDOMAIN) . '</strong>',
            '<strong>' . esc_html__('PHP', THEMESHARK_TXTDOMAIN) . '</strong>',
            self::MINIMUM_PHP_VERSION
        );

        printf('<div class="notice notice-error"><p>%1$s</p></div>', $message);
    }
}

Themeshark_Elementor::instance();
