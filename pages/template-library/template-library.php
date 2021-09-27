<?php

namespace Themeshark_Elementor\Pages;

if (!defined('ABSPATH')) exit;

use Themeshark_Elementor\Inc\Helpers;

/**
 * Lists templates available for import from the elementor-templates folder
 * @since 1.0.0
 */
class Page_Template_Library extends Admin_Page
{
    const SLUG = 'themeshark-template-library';

    protected function get_title()
    {
        return 'Template Library';
    }
    protected function get_slug()
    {
        return self::SLUG;
    }

    public $saved_templates = [];

    public function get_style_depends()
    {
        $dir_url = Helpers::get_dir_url(__DIR__);

        wp_register_style('ts-template-library-page', "$dir_url/template-library-page.css");
        return ['ts-template-library-page'];
    }

    public function get_script_depends()
    {
        $dir_url = Helpers::get_dir_url(__DIR__);

        wp_register_script('ts-template-library-page', "$dir_url/template-library-page.js", ['render-html', 'shuffle', 'ts-shuffle-grid', 'ts-template-preview']);
        return ['ts-template-library-page', 'render-html', 'shuffle'];
    }

    public function render()
    {
?>
        <div class="wrap">

            <h1 style='display:none;' class="template-library-title"><?php $this->text('Template Library'); ?></h1>

            <div class='template-library-container'>

                <div class='template-library-header' style='background-image: url("<?php echo THEMESHARK_URL . 'assets/images/header-template-library.jpg' ?>")'>

                    <nav id='template-library-navigation'>
                        <a class='nav-filter' data-template-filter='full-website'><?php $this->text('Websites'); ?></a>
                        <a class='nav-filter' data-template-filter='page'><?php $this->text('Pages'); ?></a>
                        <a class='nav-filter' data-template-filter='block'><?php $this->text('Blocks'); ?></a>
                    </nav>

                    <div class='template-library-header-overlay'></div>

                    <div class='template-library-header-content'>

                        <h2 class="template-library-header-title">
                            ThemeShark <strong><?php $this->text('Templates'); ?></strong>
                        </h2>

                        <p class='template-library-header-text'>
                            <?php $this->text('Import beautiful, pre-made templates from here and create your professional website lightning fast.'); ?>
                        </p>

                        <div id='filter-container' class="filter-menu"></div>
                    </div>

                    <div class="themeshark-library-header-wave">

                        <img src='<?php echo THEMESHARK_URL . 'assets/images/wave.svg'; ?>' />
                    </div>
                </div>

                <div class='themeshark-library-filter-content' style='--fallback-image: url("<?php echo THEMESHARK_URL . 'assets/images/placeholder.png'; ?>");'>

                    <div id="message-container"></div>

                    <div id='themeshark-template-grid-wrap' class="filter-container"></div>
                </div>
            </div>
        </div>


        <template id='ts-template-loader-template'>

            <div class='ts-template-loader-large-wrap'>

                <div class="ts-template-loader-large"></div>
            </div>
            <?php $this->text('Loading Templates...'); ?>
        </template>

<?php

    }
}
