<?php



namespace Themeshark_Elementor\Pages;

if (!defined('ABSPATH')) exit;

use \Themeshark_Elementor\Pages\Page_Manager;

/**
 * ThemeShark Messages
 *
 * Handles all messages that appear on the admin dashboard
 * and when to display them
 *
 * @since 1.0.0
 */
abstract class Admin_Page
{

    const SLUG = null;


    //required functions
    abstract protected function get_title();
    abstract protected function get_slug();
    abstract protected function render();

    public function get_script_depends()
    {
        return [];
    }

    public function get_style_depends()
    {
        return [];
    }

    public function on_before_render()
    {
        return;
    }

    public function on_before_construct()
    {
    }

    public function __construct()
    {

        $this->on_before_construct();

        add_action('admin_init', [$this, 'on_admin_init']);
        add_action('admin_menu', [$this, 'enqueue_scripts']); // Create TS Admin pages
        add_action('admin_menu', [$this, 'register']);
    }

    public function on_admin_init()
    {
    }
    public function activate()
    {
        $this->on_before_render();
        $this->render();
    }

    public function register()
    {
        $page_slug  = $this->get_slug();
        $page_label = $this->get_title();
        $main_page  = Page_Manager::get_main_page();

        add_submenu_page(
            $main_page,
            $page_label,
            $page_label,
            'manage_options',
            $page_slug,
            [$this, 'activate']
        );
    }


    public function enqueue_scripts()
    {
        $style_depends = $this->get_style_depends();
        $script_depends = $this->get_script_depends();

        if (!isset($_GET['page'])) return;
        if ($_GET['page'] !== $this->get_slug()) return;

        foreach ($style_depends as $style) wp_enqueue_style($style);
        foreach ($script_depends as $script) wp_enqueue_script($script);
    }

    public function text($text_to_translate)
    {
        _e($text_to_translate, THEMESHARK_TXTDOMAIN);
    }

    public static function _($text_to_translate)
    {
        return __($text_to_translate, THEMESHARK_TXTDOMAIN);
    }



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

    public function get_tab_atts($tab_id, $default = false)
    {
        $is_active = false;
        if (!isset($_GET['tab']) && $default === true) $is_active = true;
        if (isset($_GET['tab']) && $_GET['tab'] === $tab_id) $is_active = true;
        return $is_active ? "id='$tab_id' class='tab-content active' style='display:block;'" : "id='$tab_id' class='tab-content' style='display:none;'";
    }

    public function is_active_tab($tab_id)
    {
        return $_GET['tab'] === $tab_id ? true : false;
    }
}
