<?php

namespace Themeshark_Elementor\Pages;

use Themeshark_Elementor\Inc\Helpers;

if (!defined('ABSPATH')) exit;


/**
 * ThemeShark Template Library Page
 *
 * Lists all templates that are available for download from the ThemeShark server
 * by sending ajax request to server
 * 
 * @since 1.0.0
 */
class Page_Contribute extends Admin_Page
{

    const SLUG = 'themeshark-contribute';

    protected function get_title()
    {
        $star = '<span class="dashicons dashicons-star-filled" style="font-size: 17px; "></span>';
        $title = self::_('Contribute');
        return "<span class='ts-contribute-menu-item'>$star $title</span>";
    }

    protected function get_slug()
    {
        return self::SLUG;
    }

    public function get_style_depends()
    {
        wp_register_style('ts-contribute', Helpers::get_dir_url(__DIR__, 'contribute.css'));
        return ['ts-contribute', 'ts-admin-common'];
    }

    public function get_initials($name)
    {
        $words = explode(' ', $name);
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr(end($words), 0, 1));
        } else {
            preg_match_all('#([A-Z]+)#', $name, $capitals);
            if (count($capitals[1]) >= 2) {
                return substr(implode('', $capitals[1]), 0, 2);
            }
            return strtoupper(substr($name, 0, 2));
        }
    }

    public $contributors = [];

    public function render()
    {
?>
        <div class="wrap tsdry">
            <h1 style='display:none;'>Contribute</h1>
            <div class='inner-wrap'>
                <div class='header' style="background-image: url('<?php echo THEMESHARK_URL . 'assets/images/themeshark-elementor-banner.jpg' ?>');"></div>

                <div class='become-contributor bg-white box-shadow text-center p-5'>
                    <iframe class='box-shadow' width="560" height="315" src="https://www.youtube.com/embed/k3BqgWF_MrQ" title="ThemeShark Patreon Video" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    <h2 class='text-center inline-block regular'><?php $this->text('Become a Patreon'); ?></h2>

                    <p class='font-md mt-0 mb-3'><?php $this->text('ThemeShark is a new startup and needs your help to continue making awesome templates and widgets! Please consider helping out to keep ThemeShark Templates & Widgets for Elementor available for everyone!'); ?> </p>
                    <a class='become-patreon-btn btn-elementor inline-block relative over-hidden' href='https://patreon.com/themeshark' target='_blank'>
                        <span class='become-patreon-btn-text relative zindex-1'><?php $this->text('Start Contributing'); ?></span>
                    </a>
                </div>

                <div class='contributors flex p-5'>
                    <?php foreach ($this->contributors as $contributor) : ?>
                        <div class='contributor flex bg-white p-2'>
                            <span class='contributor-avatar mr-3'>
                                <div class='initials-gravatar relative'>
                                    <span class='initials-gravatar-text absolute pin-center bold'>
                                        <?php echo $this->get_initials($contributor['name']); ?>
                                    </span>
                                </div>
                            </span>
                            <div class='contributor-details'>
                                <div class='contributor-details-name font-md bold'><?php echo $contributor['name'] ?></div>
                                <div class='contributor-details-date'>
                                    <?php $this->text('Since ');
                                    echo $contributor['date'] ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

<?php
    }
}
