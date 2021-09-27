<?php

namespace Themeshark_Elementor\Widgets\Posts;

if (!defined('ABSPATH')) exit;

use \Themeshark_Elementor\Controls\Query_Control\Query_Module;
use \Themeshark_Elementor\Controls\Query_Control\Group_Control_Query;

require_once __DIR__ . '/posts-base.php';
require_once __DIR__ . '/skins/skin-base.php';
require_once __DIR__ . '/skins/skin-classic.php';

class TS_Posts extends Posts_Base
{

    const NAME = 'ts-posts';
    const TITLE = 'ThemeShark Posts';


    public function get_keywords()
    {
        return self::keywords(['posts', 'query', 'posts', 'archive', 'blog']);
    }

    public static function register_styles()
    {
        self::widget_style('ts-posts', self::get_dir_url(__DIR__, 'ts-posts.css'));
    }

    public function get_style_depends()
    {
        return ['ts-posts'];
    }

    public function get_icon()
    {
        return 'tsicon-themeshark-posts';
    }

    public function on_import($element)
    {

        if (isset($element['settings']['posts_post_type']) && !get_post_type_object($element['settings']['posts_post_type'])) {
            $element['settings']['posts_post_type'] = 'post';
        }

        return $element;
    }

    protected function register_skins()
    {
        $this->add_skin(new Skins\Skin_Classic($this));
    }

    protected function register_controls()
    {
        parent::register_controls();

        $this->section_query();
        $this->section_pagination();
    }

    public function section_query()
    {
        $this->start_controls_section('section_query', [
            'label' => 'Query'
        ]);

        $this->add_group_control(Group_Control_Query::get_type(), [
            'name'    => self::QUERY_GROUP_NAME,
            'presets' => ['full'],
            'exclude' => ['posts_per_page']
        ]);

        $this->end_controls_section();
    }

    public function query_posts()
    {
        /** @var Query_Module $elementor_query */
        $elementor_query = Query_Module::instance();
        $query_args = [
            'posts_per_page' => $this->get_current_skin()->get_instance_value('posts_per_page'),
            'paged' => $this->get_current_page(),
        ];
        $this->_query = $elementor_query->get_query($this, self::QUERY_GROUP_NAME, $query_args);
    }
}
