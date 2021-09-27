<?php

namespace Themeshark_Elementor\Pages;

/**
 * ThemeShark Page Manager
 *
 * Adds ThemeShark admin pages and includes required files
 *
 * @since 1.0.0
 */
final class Page_Manager
{

    public function tabs($tabs, $default_id = null)
    {
        $active_tab = isset($_GET['tab']) ? $_GET['tab'] : $default_id; ?>
        <div class="nav-tab-wrapper">
            <?php foreach ($tabs as $id => $name) : ?>
                <a class='nav-tab <?php echo $active_tab === $id ? 'active' : ''; ?>' data-tab='<?php echo $id ?>'><?php echo $name; ?></a>
            <?php endforeach; ?>
        </div>
<?php
    }

    public $pages_dir = THEMESHARK_PATH . 'pages/';
    public $pages = [];

    const MENU_LABEL = 'ThemeShark';
    const POSITION = 58.67;

    const TAB_CSS_CLASS = 'themeshark-admin-menu-tab';

    public static $instance = null;

    public static function register()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        $this->require_pages();


        add_action('admin_menu', [$this, 'add_menu_page']); // Create TS Admin pages
        add_action('admin_init', [$this, 'add_menu_class']);


        $this->pages = [
            //Main page should be created first
            new Page_Template_Library(),
            new Page_Settings(),
            new Page_Contribute()
        ];
    }


    public static function get_main_page()
    {
        return Page_Template_Library::SLUG;
    }

    public function add_menu_class()
    {
        global $menu;
        if (!$menu) return;
        foreach ($menu as $key => $value) {
            $page_slug = $value[2];
            $tab_classes_index = 4;

            if (self::get_main_page() == $page_slug) {
                $menu[$key][$tab_classes_index] .= ' ' . self::TAB_CSS_CLASS;
                break;
            }
        }
    }

    public function add_menu_page()
    {
        add_menu_page(
            self::MENU_LABEL,
            self::MENU_LABEL,
            'manage_options',
            self::get_main_page(),
            '', // no callback, uses main page submenu slug.
            'none', // icon set using css
            self::POSITION
        );
    }

    public function require_pages()
    {
        require_once $this->pages_dir . 'admin-page-class.php';
        require_once $this->pages_dir . 'settings-page-trait.php';
        require_once $this->pages_dir . 'template-library/template-library.php';
        require_once $this->pages_dir . 'settings/settings.php';
        require_once $this->pages_dir . 'contribute/contribute.php';
    }
}
