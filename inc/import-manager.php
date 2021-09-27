<?php

namespace Themeshark_Elementor\Inc;

if (!defined('ABSPATH')) exit;

use \Elementor\TemplateLibrary\Source_Local;

/**
 * ThemeShark Import Manager
 *
 * Handles the importing of all themeshark templates to both the elementor
 * and themeshark libraries
 *
 * @since 1.0.0
 */
final class Import_Manager
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
        add_action('wp_ajax_import_elementor', [$this, 'ajax_import_elementor']);
    }

    public function import_to_elementor_library($template_data)
    {
        $source = new Source_Local;
        $tmp_file = tmpfile();
        fwrite($tmp_file, $template_data);
        $uri = stream_get_meta_data($tmp_file)['uri'];
        $saved_file = $source->import_template('', $uri);
        fclose($tmp_file);
        return $saved_file[0];
    }

    public function ajax_import_elementor()
    {
        $themeshark_id  = isset($_POST['themeshark_id']) ? sanitize_key($_POST['themeshark_id']) : null;
        $import_data    = isset($_POST['template']) ? json_decode(stripslashes($_POST['template']), true) : null;
        $saved_template = $this->import_to_elementor_library(json_encode($import_data));
        $elementor_id   = $saved_template['template_id'];

        if ($themeshark_id !== null) add_post_meta($elementor_id, 'themeshark_template', $themeshark_id);

        echo intval(sanitize_key($elementor_id));
        exit;
    }
}
