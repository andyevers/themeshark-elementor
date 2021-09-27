<?php

namespace Themeshark_Elementor\Widgets;

if (!defined('ABSPATH')) exit;

use \Elementor\Controls_Manager as CM;
use \Elementor\Utils;
use \Themeshark_Elementor\Inc\TS_Widget;
use \Themeshark_Elementor\Controls\Query_Control\Query_Module;
use \Themeshark_Elementor\Controls\Query_Control\Group_Control_Query;
use Themeshark_Elementor\Controls\Controls_Handler;

require_once __DIR__ . '/hover-image-template.php';


class TS_Image_Link_Posts extends TS_Widget
{
    use Hover_Image_Template;

    const NAME = 'ts-image-link-posts';
    const TITLE = 'Content Image Posts';

    const QUERY_GROUP_NAME = 'posts';

    private $_query = null;



    public function get_keywords()
    {
        return self::keywords(['posts', 'image link', 'archive']);
    }


    public function on_before_construct()
    {
        $this->add_section_action('section_image_style', [$this, 'update_section_image_style']);
    }

    public static function register_styles()
    {
        self::register_template_styles();
    }
    public function get_style_depends()
    {
        return ['ts-hover-image'];
    }

    public function get_icon()
    {
        return 'tsicon-content-image-posts';
    }

    public function section_layout()
    {
        $SC = $this->shorthand_controls();
        $this->start_controls_section('section_layout', [
            'label' => 'Layout'
        ]);



        $SC->control($this->control_key_skin, 'Skin', CM::SELECT, [
            'default' => 'corners',
            'prefix_class' => 'themeshark-hover-image--skin-',
            'render_type' => 'template',
            'options' => $SC::options_select(
                ['corners', 'Corners'],
                ['standard', 'Raise Content'],
                ['border-offset', 'Border Offset'],
                ['card', 'Card']
            )
        ]);


        $SC->responsive_control('columns', 'Columns', CM::SELECT, [
            'default' => '3',
            'tablet_default' => '2',
            'mobile_default' => '1',
            'options' => [
                '1' => '1',
                '2' => '2',
                '3' => '3',
                '4' => '4',
                '5' => '5',
                '6' => '6',
            ],
            'prefix_class' => 'elementor-grid%s-',
            'frontend_available' => true,
            'selectors' => [
                '{{WRAPPER}} .themeshark-image-link-posts' => 'grid-template-columns: repeat({{SIZE}}, 1fr);',
                '{{WRAPPER}} .themeshark-image-link-posts--skin-circles' => '--post-column-count: {{SIZE}};'
            ],
        ]);

        $SC->control('posts_per_page', 'Posts Per Page', CM::NUMBER, [
            'default' => 6,
        ]);

        $this->add_control_image_size();

        $this->add_control_title_size([
            'separator' => 'before'
        ]);


        $SC->control('excerpt_length', 'Excerpt Length', CM::NUMBER, [
            'default' => 15,
            'min'     => 0,
            'max'     => 50
        ]);

        $SC->control('show_readmore', 'Show Read More', CM::SWITCHER, [
            'condition' => [$this->control_key_skin => 'card'],
            'return_value' => 'yes',
            'separator' => 'before',
            'default' => 'yes'
        ]);

        $SC->control('readmore_text', 'Read More Text', CM::TEXT, [
            'condition' => ['show_readmore' => 'yes', $this->control_key_skin => 'card'],
            'default' => $SC::_('Read More'),
            'render_type' => 'ui',
            'themeshark_settings' => [
                Controls_Handler::LINK_TEXT => [
                    'selector' => '{{WRAPPER}} .themeshark-readmore-text'
                ]
            ]
        ]);
        $SC->control('readmore_bar_icon', 'Icon', CM::ICONS, [
            'condition' => ['show_readmore' => 'yes', $this->control_key_skin => 'card'],
            'fa4compatibility' => 'icon',
            'default' => [
                'value' => 'fas fa-arrow-right',
                'library' => 'fa-solid',
            ],
        ]);

        // $SC->control('meta_data', 'Meta Data', CM::SELECT2, [
        //     'label_block' => true,
        //     'type'        => CM::SELECT2,
        //     'default'     => ['date', 'comments'],
        //     'multiple'    => true,
        //     'separator'   => 'before',
        //     'options' => $SC::options_select(
        //         ['author',   'Author'],
        //         ['date',     'Date'],
        //         ['time',     'Time'],
        //         ['comments', 'Comments'],
        //         ['modified', 'Date Modified']
        //     ),
        // ]);

        // $SC->control('meta_separator', 'Separator Between', CM::TEXT, [
        //     'condition' => ['meta_data!' => []],
        //     'default'   => '///',
        //     'selectors' => $SC::selectors([
        //         '.themeshark-post-meta-data span + span::before' => [
        //             'content: "{{VALUE}}"',
        //         ]
        //     ]),
        // ]);





        $this->end_controls_section();
    }


    public function section_query()
    {
        $this->start_controls_section('section_query', [
            'label' => 'Query'
        ]);
        $this->add_group_control(Group_Control_Query::get_type(), [
            'name' => self::QUERY_GROUP_NAME,
            'presets' => ['full'],
            'exclude' => ['posts_per_page']
        ]);
        $this->end_controls_section();
    }



    public function update_section_image_style()
    {
        $SC = $this->shorthand_controls();

        if ($this->get_controls('column_gap')) return; //if already has column gap control

        $this->start_injection($SC->set_position('start', 'section_image_style', 'section', false));


        $SC->control('column_gap', 'Columns Gap', CM::SLIDER, [
            'range'     => $SC::range_default(['px', 0, 100]),
            'selectors' => $SC::selectors([
                '.themeshark-image-link-posts' => [
                    'column-gap: {{SIZE}}{{UNIT}}'
                ]
            ])
        ]);

        $SC->control('row_gap', 'Rows Gap', CM::SLIDER, [
            'range'     => $SC::range_default(['px', 0, 100]),
            'selectors' => $SC::selectors([
                '.themeshark-image-link-posts' => [
                    'row-gap: {{SIZE}}{{UNIT}}'
                ]
            ])
        ]);
        $this->end_injection();

        $this->remove_responsive_control('image_size_width');
        $this->remove_responsive_control('image_size_max_width');
    }

    public function register_controls()
    {
        $this->section_layout();
        $this->section_query();
        $this->section_effect();
        $this->section_image_style();
        $this->section_content_style();
        $this->section_readmore_bar_style();
        $this->section_border_style();
    }








    public function get_excerpt($post_id = null, $excerpt_length = null, $text_more = '...')
    {
        if (is_null($excerpt_length)) $excerpt_length = $this->get_settings('excerpt_length');
        return wp_trim_words(get_the_excerpt($post_id), $excerpt_length, $text_more);
    }

    public function get_image_html($post_id = null, $image_size_control_name = null)
    {
        $settings = $this->get_settings();
        $image_size_name = $image_size_control_name ? $image_size_control_name : $this->group_image_size_name;
        $settings[$image_size_name] = ['id' => get_post_thumbnail_id($post_id)];
        $image_html = \Elementor\Group_Control_Image_Size::get_attachment_image_html($settings, $this->group_image_size_name);
        if (empty($image_html)) $image_html = '<img src="' . Utils::get_placeholder_image_src() . '" />';
        return $image_html;
    }

    public function query_posts()
    {
        /** @var Query_Module $elementor_query */
        $elementor_query = Query_Module::instance();
        $query_args = [
            'posts_per_page' => $this->get_settings('posts_per_page')
        ];
        $this->_query = $elementor_query->get_query($this, self::QUERY_GROUP_NAME, $query_args);
    }

    public function get_query($run_new_query = true)
    {
        if ($run_new_query === true) $this->query_posts();
        return $this->_query;
    }

    public function render_standard_post_layout()
    {
        global $post;

        $image_html  = $this->get_image_html($post->ID);
        $description = $this->get_excerpt($post->ID);

        $link_string = 'href="' . get_the_permalink($post->ID) . '" ';

        $this->render_standard_layout('a', $image_html, $post->post_title, $description, $link_string);
    }


    protected function add_posts_attributes()
    {
        $this->add_render_attribute($this->attribute_item_wrap, 'class', 'themeshark-post-item');
    }

    public function render_classic_layout()
    {
        global $post;

        // $this->add_default_render_attributes();
        $image_html = $this->get_image_html($post->ID);
        $settings   = $this->get_settings();
        // $description_text = $this->get_excerpt($post->ID);
        // $title_text       = $post->post_title;
        // $settings         = $this->get_settings();
        // $tag_wrap         = $this->get_tag('item_wrap')

?>

        <article <?php $this->print_render_attribute_string($this->attribute_item_wrap); ?>>

            <?php $this->render_image_wrap($image_html); ?>

            <div <?php $this->print_render_attribute_string($this->attribute_item_content); ?>>

                <?php $this->render_title_wrap(get_the_title()); ?>

                <?php $this->render_description_wrap($this->get_excerpt($post->ID)); ?>

                <span class='themeshark-readmore-text'><?php esc_html_e($settings['readmore_text']); ?></span>
            </div>
            <?php $this->render_meta_data(); ?>
        </article>
    <?php
    }

    protected function render_meta_data()
    {
        /** @var array $settings e.g. [ 'author', 'date', ... ] */
        $settings = $this->get_settings('meta_data');
        if (empty($settings)) {
            return;
        }
    ?>
        <div class="themeshark-post-meta-data">
            <?php
            if (in_array('author', $settings)) {
                $this->render_author();
            }

            if (in_array('date', $settings)) {
                $this->render_date_by_type();
            }

            if (in_array('time', $settings)) {
                $this->render_time();
            }

            if (in_array('comments', $settings)) {
                $this->render_comments();
            }
            if (in_array('modified', $settings)) {
                $this->render_date_by_type('modified');
            }
            ?>
        </div>
    <?php
    }

    protected function render_date_by_type($type = 'publish')
    { ?>
        <span class="elementor-post-date">
            <?php
            switch ($type):
                case 'modified':
                    $date = get_the_modified_date();
                    break;
                default:
                    $date = get_the_date();
            endswitch;

            echo apply_filters('the_date', $date, get_option('date_format'), '', '');
            ?>
        </span>
    <?php
    }

    protected function render_author()
    { ?>
        <span class="themeshark-post-author"> <?php the_author(); ?></span>
    <?php
    }

    protected function render_time()
    { ?>
        <span class="themeshark-post-time"> <?php the_time(); ?></span>
    <?php
    }

    protected function render_comments()
    { ?>
        <span class="themeshark-post-avatar"> <?php comments_number(); ?></span>
<?php
    }

    protected function render()
    {
        $this->add_posts_attributes();
        $this->add_default_render_attributes();
        $wp_query = $this->get_query();

        $skin = $this->get_settings($this->control_key_skin);

        $this->add_render_attribute('posts_wrap', 'class', 'themeshark-image-link-posts');
        echo '<div ' . $this->get_render_attribute_string('posts_wrap') . '>';

        while ($wp_query->have_posts()) {
            $wp_query->the_post();

            if ($skin === 'classic') {
                $this->render_classic_layout();
            } else {
                $this->render_standard_post_layout();
            }
        }

        echo '</div>';
    }
}
