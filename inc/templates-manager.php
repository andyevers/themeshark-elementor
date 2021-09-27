<?php

namespace Themeshark_Elementor\Inc;

use Themeshark_Elementor\Inc\Helpers;

final class Templates_Manager
{

    private static $_instance = null;


    public static function get_owned_templates()
    {
        $posts = get_posts([
            'post_type'      => 'elementor_library',
            'meta_key'       => 'themeshark_template',
            'posts_per_page' => -1,
        ]);

        $owned_templates = [];
        foreach ($posts as $post) {
            $post_id = $post->ID;
            $ts_template_id = get_post_meta($post_id, 'themeshark_template', true);
            $owned_templates[$ts_template_id] = $post_id;
        }

        return $owned_templates;
    }

    public static function register()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct()
    {
    }
}
