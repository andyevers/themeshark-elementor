<?php

namespace Themeshark_Elementor;

if (!defined('ABSPATH')) exit;

use \Themeshark_Elementor\Controls\Sticky;
use \Themeshark_Elementor\Inc\Globals_Fixer;
use \Themeshark_Elementor\Pages\Page_Manager;
use \Themeshark_Elementor\Controls\Animations;
use \Themeshark_Elementor\Controls\Page_Controls;
use \Themeshark_Elementor\Controls\Background_Video;
use \Themeshark_Elementor\Controls\Controls_Handler;
use \Themeshark_Elementor\Controls\Pseudo_Backgrounds;
use \Themeshark_Elementor\Inc\Admin_Notices\Admin_Messages;
use \Themeshark_Elementor\Controls\Query_Control\Query_Module;
use Themeshark_Elementor\Inc\Templates_Manager;
use Themeshark_Elementor\Inc\Settings;

/**
 * ThemeShark Elementor Plugin
 *
 * Includes required assets, adds actions, & prepares plugin
 * 
 * @since 1.0.0
 */
class Plugin
{

    public static $instance = null;

    public $registered_widgets = []; //class names of registered widgets go here to be initialized
    public $editor_scripts = []; //holds script handles that will only be enqueued when the editor is open
    public $localized_scripts = []; //js data accessible using themesharkLocalizedData.MY_KEY

    // private $_widget_classes = null;

    private $_did_require_widgets = false;

    public $assets_url = THEMESHARK_URL . 'assets';

    public function __construct()
    {
        self::$instance = $this;

        Page_Manager::register();
        Admin_Messages::register();
        Controls_Handler::register();
        Globals_fixer::register();
        Query_Module::register();

        add_action('admin_enqueue_scripts', [$this, 'register_common_scripts']); //admin & frontend scripts
        add_action('admin_enqueue_scripts', [$this, 'register_admin_scripts']); //admin only scripts
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_common']); //admin only common scripts
        add_action('admin_enqueue_scripts', [$this, '_localize_themeshark_scripts']); //admin only common scripts
        add_action('elementor/init', [$this, 'elementor_init']);
    }

    public function elementor_init()
    {
        $this->register_widgets();

        //common
        add_action('elementor/widgets/widgets_registered', [$this, 'register_common_scripts']); //admin & frontend scripts register
        add_action('elementor/widgets/widgets_registered', [$this, 'register_frontend_scripts']); //frontend only scripts register
        add_action('elementor/frontend/after_register_scripts', [$this, 'enqueue_frontend_common']);
        add_action('elementor/frontend/after_enqueue_scripts', [$this, 'enqueue_frontend_common']); //all scripts frontend not widget here

        //localize scripts
        add_action('elementor/frontend/before_enqueue_scripts', [$this, '_localize_themeshark_scripts']);
        add_action('elementor/editor/before_enqueue_scripts', [$this, '_localize_themeshark_scripts']);

        //widgets
        add_action('elementor/widgets/widgets_registered', [$this, 'init_widgets']); // register widgets

        //editor
        add_action('elementor/editor/before_enqueue_scripts', [$this, 'enqueue_editor_styles']);
        add_action('elementor/preview/enqueue_styles', [$this, 'enqueue_editor_iframe_styles']);
        add_action('elementor/editor/after_enqueue_scripts', [$this, 'enqueue_editor_scripts']);
        add_action('elementor/elements/categories_registered', [$this, 'add_widget_categories']);

        //controls
        add_action('elementor/controls/controls_registered', [$this, 'register_controls']);
    }


    public function _localize_themeshark_scripts()
    {
        // wp_die();
        $is_edit_mode = \Elementor\Plugin::instance()->editor->is_edit_mode();
        $is_admin_dashboard = !$is_edit_mode && is_admin();
        $script_handle = $is_edit_mode ? 'elementor-editor' : 'elementor-frontend';
        if ($is_admin_dashboard) $script_handle = 'ts-functions';

        $localized_scripts = $this->localized_scripts;

        // data in here will only show for logged in user in admin area
        if (is_admin()) {
            $localized_scripts = array_merge([
                'importUrl'          => admin_url('admin-ajax.php'),
                'assetsDir'          => THEMESHARK_URL . 'assets',
                'fallbackImageUrl'   => THEMESHARK_URL . 'assets/images/placeholder.png',
                'ownedTemplates'     => Templates_Manager::get_owned_templates(),
                'restRoute'          => \Themeshark_Elementor::REST_ROUTE,
                'sharedData'         => Settings::get_allowed_shared_data()
            ], $localized_scripts);
        }

        wp_localize_script($script_handle, 'themesharkLocalizedData', $localized_scripts);
    }

    public function register_widgets()
    {
        $widget_classes = self::get_widget_classes();
        foreach ($widget_classes as $widget_class) {
            $widget_class::register();
        }
    }

    public static function require_widgets_once()
    {
        if (self::$instance->_did_require_widgets === true) return;

        require_once 'inc/widget-class.php';
        require_once 'inc/skin-class.php';

        $widgets_dir = THEMESHARK_PATH . 'widgets';

        require_once "$widgets_dir/ts-svg-text/ts-svg-text.php";
        require_once "$widgets_dir/ts-divider/ts-divider.php";
        require_once "$widgets_dir/ts-nav-menu/ts-nav-menu.php";
        require_once "$widgets_dir/ts-expander/ts-expander.php";
        require_once "$widgets_dir/ts-button/ts-button.php";
        require_once "$widgets_dir/ts-timeline/ts-timeline.php";
        require_once "$widgets_dir/ts-heading/ts-heading.php";
        require_once "$widgets_dir/ts-sticky-image/ts-sticky-image.php";
        require_once "$widgets_dir/ts-contact-form-7/ts-contact-form-7.php";
        require_once "$widgets_dir/ts-image-links/ts-image-link-posts.php";
        require_once "$widgets_dir/ts-image-links/ts-image-link.php";
        require_once "$widgets_dir/ts-gallery/ts-gallery.php";
        require_once "$widgets_dir/ts-team-member/ts-team-member.php";
        require_once "$widgets_dir/ts-pricing/ts-price-card.php";
        require_once "$widgets_dir/ts-swiper/ts-testimonial-carousel/ts-testimonial-carousel.php";
        require_once "$widgets_dir/ts-lottie/ts-lottie.php";
        require_once "$widgets_dir/ts-posts/ts-posts.php";

        self::$instance->_did_require_widgets = true;
    }

    public static function get_widget_classes()
    {
        self::require_widgets_once();

        $widget_classes = [
            Widgets\TS_Expander::get_class(),
            Widgets\TS_SVG_Text::get_class(),
            Widgets\TS_Divider::get_class(),
            Widgets\TS_Nav_Menu::get_class(),
            Widgets\TS_Button::get_class(),
            Widgets\TS_Timeline::get_class(),
            Widgets\TS_Heading::get_class(),
            Widgets\TS_Image::get_class(),
            Widgets\TS_Image_Link_Posts::get_class(),
            Widgets\TS_Image_Link::get_class(),
            Widgets\TS_Gallery::get_class(),
            Widgets\TS_Contact_Form_7::get_class(),
            Widgets\TS_Team_Member::get_class(),
            Widgets\TS_Price_Card::get_class(),
            Widgets\TS_Lottie::get_class(),
            Widgets\Posts\TS_Posts::get_class(),
            Widgets\TS_Swiper\TS_Testimonial_Carousel::get_class()
        ];
        return $widget_classes;
    }

    public function register_common_scripts()
    {
        $assets = $this->assets_url;

        wp_register_script('ts-functions', "$assets/js/ts-functions.js", ['jquery']);
        wp_register_script('render-html', "$assets/lib/render-html.js", ['ts-functions'], false, true);
        wp_register_script('scroll-observer', "$assets/lib/scroll-observer.js", ['ts-functions'], false, true);
        wp_register_script('smartmenus', "$assets/lib/jquery.smartmenus.js", ['jquery'], false, true);
        wp_register_script('elementor-sticky', "$assets/lib/jquery.sticky.js", ['jquery'], false, true);
        wp_register_script('lottie', "$assets/lib/lottie.js", [], false, true);
        wp_register_script('shuffle', "$assets/lib/shuffle.js", [], false, true);

        wp_register_script('ts-shuffle-grid', "$assets/js/shuffle-grid.js", ['shuffle', 'ts-templates-manager', 'render-html'], false, true);
        wp_register_script('ts-loading-overlay', "$assets/js/loading-overlay.js", [], false, true);
        wp_register_script('ts-templates-manager', "$assets/js/templates-manager.js", ['ts-functions'], false, true);
        wp_register_script('ts-template-preview', "$assets/js/template-preview.js", ['render-html'], false, true);
        wp_register_script('ts-template-modal', "$assets/js/template-modal.js", ['jquery', 'ts-functions', 'render-html']);
    }


    public function enqueue_editor_iframe_styles()
    {
        $assets = $this->assets_url;

        wp_enqueue_style('ts-editor-iframe', "$assets/css/editor-iframe.css", false);
        wp_enqueue_style('ts-fonts', "$assets/fonts/fonts.css", false); //editor widget icons

    }

    public function register_frontend_scripts()
    {
        $assets = $this->assets_url;

        wp_register_script('ts-frontend', "$assets/js/ts-frontend.js", ['ts-functions', 'scroll-observer'], false, false);
        wp_register_style('ts-common', "$assets/css/common.css");
    }


    public function register_admin_scripts()
    {
        $assets = $this->assets_url;

        wp_register_style('ts-dashboard', "$assets/css/dashboard.css");
        wp_register_style('ts-fonts', "$assets/fonts/fonts.css");
        wp_register_style('ts-admin-common', "$assets/css/admin-common.css");
    }


    public function enqueue_editor_styles()
    {
        $assets = $this->assets_url;

        wp_enqueue_style('ts-fonts', "$assets/fonts/fonts.css", false); //editor widget icons
        wp_enqueue_style('ts-admin-common', "$assets/css/admin-common.css", false);
        wp_enqueue_style('ts-templates-popup', "$assets/css/templates-popup.css", false);
    }

    public function enqueue_editor_scripts()
    {
        wp_enqueue_script('shuffle');
        wp_enqueue_script('ts-shuffle-grid');
        wp_enqueue_script('ts-loading-overlay');
        wp_enqueue_script('ts-template-preview');
        wp_enqueue_script('ts-template-modal');
        wp_enqueue_script('ts-templates-manager');
        foreach ($this->editor_scripts as $script) wp_enqueue_script($script);
    }

    public function enqueue_frontend_common()
    {
        wp_enqueue_script('ts-functions');
        wp_enqueue_script('ts-frontend');
        wp_enqueue_style('ts-common');
    }


    public function enqueue_admin_common()
    {
        wp_enqueue_script('ts-functions');
        wp_enqueue_style('ts-dashboard');
        wp_enqueue_style('ts-admin-common');
        wp_enqueue_style('ts-fonts');
    }


    public function init_widgets()
    {
        $widgets_manager = \Elementor\Plugin::instance()->widgets_manager;
        foreach ($this->registered_widgets as $widget) {
            $widgets_manager->register_widget_type(new $widget());
        }
    }

    public function register_controls()
    {
        $controls_dir = THEMESHARK_PATH . 'controls';
        $control_groups_dir = THEMESHARK_PATH . 'controls/groups';

        require_once "$control_groups_dir/group-transform.php";
        require_once "$control_groups_dir/group-transition.php";
        require_once "$controls_dir/sticky/sticky.php";
        require_once "$controls_dir/animations/animations.php";
        require_once "$controls_dir/pseudo-backgrounds/pseudo-backgrounds.php";
        require_once "$controls_dir/page-controls/page-controls.php";
        require_once "$controls_dir/background-video/background-video.php";
        require_once "$controls_dir/select-conditional/select-conditional.php";

        $controls_manager = \Elementor\Plugin::instance()->controls_manager;
        $controls_manager->add_group_control('transform-controls', new Controls\Group_Control_Transform());
        $controls_manager->add_group_control('transition-controls', new Controls\Group_Control_Transition());

        Background_Video::register();
        Pseudo_Backgrounds::register();
        Page_Controls::register();
        Animations::register();
        Sticky::register();
    }

    public function add_widget_categories($elements_manager)
    {
        $elements_manager->add_category('themeshark', [
            'title' => __('ThemeShark', THEMESHARK_TXTDOMAIN),
            'icon'  => 'font'
        ]);
    }



    public static function has_elementor_pro()
    {
        return defined('ELEMENTOR_PRO_VERSION');
    }

    /**
     * returns date in seconds when plugin was first installed
     */
    public static function get_install_date()
    {
        $install_date_option = Settings::OPTION_INSTALL_DATE;
        return get_option($install_date_option);
    }
}
