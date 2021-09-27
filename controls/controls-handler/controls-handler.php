<?php

namespace Themeshark_Elementor\Controls;

if (!defined('ABSPATH')) exit;

/**
 * ThemeShark Messages
 */
final class Controls_Handler
{
    private static $_instance = null;

    public static function register()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    const RESET_WRAPPER_CLASS     = 'reset_wrapper_class';
    const REQUIRE_STICKY          = 'sticky';
    const NO_TRANSITION           = 'onchange_no_transition';
    const LINK_TEXT               = 'link_text';
    const LINK_HTML               = 'link_html';
    const LINK_ATTRIBUTE          = 'link_attribute';
    const LINK_CLASS              = 'link_class';
    const LINK_REPLACE_TAG        = 'link_replace_tag';
    const NEW_SLIDES_ADD_HANDLERS = 'new_slides_add_handlers';

    public function __construct()
    {
        add_action('elementor/editor/before_enqueue_scripts', [$this, 'register_handlers']);
        add_action('elementor/editor/after_enqueue_scripts', [$this, 'enqueue_handlers']);
    }

    private $handlers = []; //handlers pushed here when registered with handler_script() then enqueued

    /*
     * Handles must be in 'handlers' folder and match syntax: "ts-$filename" (without .js)
     */
    public function register_handlers()
    {
        $dir_url = THEMESHARK_URL . 'controls/controls-handler/';
        wp_register_script('ts-controls-handler', "$dir_url/controls-handler.js", ['ts-functions'], false, false);

        $this->handler_script('ts-handler-no-transition');
        $this->handler_script('ts-handler-reset-class');
        $this->handler_script('ts-handler-new-slides');
        $this->handler_script('ts-handler-link-element');
    }

    public function enqueue_handlers()
    {
        foreach ($this->handlers as $handler) wp_enqueue_script($handler);
    }

    private function handler_script($handle, $deps = [], $src = null)
    {
        $filename = substr($handle, 3); //remove "ts-" from handle

        $dir_url = THEMESHARK_URL . 'controls/controls-handler/handlers';
        $default_deps = ['ts-controls-handler'];
        $src = $src === null ? "$dir_url/$filename.js" : $src;
        wp_register_script($handle, $src, array_merge($default_deps, $deps), false, true);
        $this->handlers[] = $handle;
    }
}
