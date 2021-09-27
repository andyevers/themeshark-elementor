<?php

namespace Themeshark_Elementor\Controls;

if (!defined('ABSPATH')) exit;


/**
 * Code from elementor section.php
 */
final class Background_Video
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
        add_action('elementor/widgets/widgets_registered', [$this, 'register_video_scripts']);
    }


    //This will get enqueued when another widget lists 'ts-background-video' in the script dependencies
    public function register_video_scripts()
    {
        $dir_url = THEMESHARK_URL . 'controls/background-video';
        wp_register_script('ts-background-video', "$dir_url/background-video.js", ['ts-frontend'], false, true);
    }

    public static function render($controls_stack, $group_name)
    {
        $settings = $controls_stack->get_settings_for_display();
?>
        <?php
        if ('video' === $settings[$group_name . '_background']) :
            if ($settings[$group_name . '_video_link']) :
                $video_properties = \Elementor\Embed::get_video_properties($settings[$group_name . '_video_link']);

                $controls_stack->add_render_attribute('background-video-container', 'class', 'elementor-background-video-container');

                if (!$settings[$group_name . '_play_on_mobile']) {
                    $controls_stack->add_render_attribute('background-video-container', 'class', 'elementor-hidden-phone');
                }
        ?>
                <div <?php echo $controls_stack->get_render_attribute_string('background-video-container'); ?>>
                    <?php if ($video_properties) : ?>
                        <div class="elementor-background-video-embed"></div>
                    <?php
                    else :
                        $video_tag_attributes = 'autoplay muted playsinline';
                        if ('yes' !== $settings[$group_name . '_play_once']) :
                            $video_tag_attributes .= ' loop';
                        endif;
                    ?>
                        <video class="elementor-background-video-hosted elementor-html5-video" <?php echo $video_tag_attributes; ?>></video>
                    <?php endif; ?>
                </div>
        <?php
            endif;
        endif;
        ?>
    <?php
    }
    public static function render_template($group_name)
    {
    ?>
        <# if ( settings.<?php echo $group_name; ?>_video_link ) { let videoAttributes='autoplay muted playsinline' ; if ( ! settings.background_play_once ) { videoAttributes +=' loop' ; } view.addRenderAttribute( 'background-video-container' , 'class' , 'elementor-background-video-container' ); if ( ! settings.<?php echo $group_name; ?>_play_on_mobile ) { view.addRenderAttribute( 'background-video-container' , 'class' , 'elementor-hidden-phone' ); } #>
            <div {{{ view.getRenderAttributeString( 'background-video-container' ) }}}>
                <div class="elementor-background-video-embed"></div>
                <video class="elementor-background-video-hosted elementor-html5-video" {{ videoAttributes }}></video>
            </div>
            <# } #>

        <?php
    }
}
