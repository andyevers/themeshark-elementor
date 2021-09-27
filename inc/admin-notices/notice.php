<?php

namespace Themeshark_Elementor\Inc\Admin_Notices;

/**
 * The Admin_Notice class, responsible for creating admin notices.
 *
 * Each notice is a new instance of the object.
 *
 * @since 1.0.0
 */
class Notice
{
    const DEFAULT_PREFIX = 'themeshark_notice_dismissed';

    /** The notice-ID. */
    public $id;

    /** The notice message. */
    public $message;

    /** The notice title. */
    public $title;

    /** An instance of the Dismiss object. */
    public $dismiss;

    /** The notice arguments. */
    public $options = [
        'scope'              => 'global',
        'type'               => 'info',
        'alt_style'          => false,
        'capability'         => 'edit_theme_options',
        'option_prefix'      => self::DEFAULT_PREFIX,
        'dismissed_duration' => -1, //negative = forever
        'undismiss_before'   => null, // anything dismissed before the time provided will be undismissed 
        'screens'            => [],
        'use_sidebar'        => false,
        'container_type'     => 'standard', // accepts 'wp' or 'themeshark'. notice container class.
        'is_dismissible'     => true
    ];

    /** Allowed HTML in the message. */
    public $allowed_html = [
        'p'      => [],
        'div'    => [
            'class' => []
        ],
        'a'      => [
            'href'             => [],
            'rel'              => [],
            'class'            => [],
            'data-user_action' => [],
            'target'           => []
        ],
        'em'     => [],
        'strong' => [],
        'br'     => [],
    ];

    /** An array of allowed types. */
    public $allowed_types = [
        'info',
        'success',
        'error',
        'warning',
    ];


    /**
     * Constructor.
     * 
     * @typedef (array) NoticeOptions something test
     * 
     * 'scope'              => (string) Can be "global" or "user". Determines if the dismissed status will be saved as an option or user-meta. Defaults to "global". <br><br>
     * 'type'               => (string) Can be one of "info", "success", "warning", "error". Defaults to "info".
     * 'alt_style'          => (bool)   Whether we want to use alt styles or not. Defaults to false.
     * 'capability'         => (string) The user capability required to see the notice. Defaults to "edit_theme_options".
     * 'option_prefix'      => (string) The prefix that will be used to build the option (or post-meta) name. Can contain lowercase latin letters and underscores.
     * 'screens'            => (array)  An array of screens where the notice will be displayed. Leave empty to always show. Defaults to an empty array.
     * 'undismiss_before'   => (number) date in seconds - anything dismissed before the time provided will be undismissed 
     * 'dismissed_duration' => (number) duration in seconds - how long the dismiss lasts. -1 = forever
     * 
     * @param string $id      A unique ID for this notice. Can contain lowercase characters and underscores.
     * @param string $title   The title for our notice.
     * @param string $message The message for our notice.
     * @param NoticeOptions $options An array of additional options to change the defaults for this notice.
     */
    public function __construct($id, $title, $message, $options = [])
    {

        // Set the object properties.
        $this->id      = $id;
        $this->title   = $title;
        $this->message = $message;
        $this->options = wp_parse_args($options, $this->options);

        if (!$this->id || !$this->message) return;

        /** Allow filtering the allowed HTML tags array. */
        $this->allowed_html = apply_filters('themeshark_admin_notices_allowed_html', $this->allowed_html);

        // Instantiate the Dismiss object.
        $this->dismiss = new Dismiss($this->id, $this->options['option_prefix'], $this->options['scope'], $this->options['dismissed_duration'], $this->options['undismiss_before']);
    }

    private function get_container_type_class()
    {
        $container = $this->options['container_type'];
        switch ($container) {
            case 'thick':
                return 'themeshark-notice-thick';
            case 'standard':
                return 'themeshark-notice-standard';
            default:
                return null;
        }
    }

    /**
     * Prints the notice.
     */
    public function the_notice()
    {
        // Early exit if we don't want to show this notice.
        if (!$this->show()) return;

        $sidebar = '<div class=" themeshark-notice-aside"><div class="themeshark-icon-wrapper"></div></div>';

        $html  = $this->options['use_sidebar'] ? $sidebar : '';
        $html .= '<div class="themeshark-notice-content">';
        $html .= $this->get_title();
        $html .= $this->get_message();
        $html .= '</div>';

        // Print the notice.
        printf(

            '<div id="%1$s" class="%2$s">%3$s</div>',
            'themeshark-notice-' . esc_attr($this->id), // The ID.
            esc_attr($this->get_classes()), // The classes.
            $html // The HTML.
        );
    }

    /**
     * Determine if the notice should be shown or not.
     */
    public function show()
    {
        // Don't show if the user doesn't have the required capability.
        if (!current_user_can($this->options['capability'])) return false;

        // Don't show if we're not on the right screen.
        if (!$this->is_screen()) return false;

        // Don't show if notice has been dismissed.
        if ($this->dismiss->is_dismissed()) return false;

        return true;
    }

    /**
     * Get the notice classes.
     */
    public function get_classes()
    {
        $classes = [
            'notice',
            'themeshark-notice',
        ];

        $ts_type_class = $this->get_container_type_class();
        if ($this->options['is_dismissible']) $classes[] = 'is-dismissible';
        if ($ts_type_class) $classes[] = $ts_type_class;

        // Make sure the defined type is allowed.
        $this->options['type'] = in_array($this->options['type'], $this->allowed_types, true) ? $this->options['type'] : 'info';

        // Add the class for notice-type.
        $classes[] = 'notice-' . $this->options['type'];

        // Do we want alt styles?
        if ($this->options['alt_style']) $classes[] = 'notice-alt';

        // Combine classes to a string.
        return implode(' ', $classes);
    }

    /**
     * Returns the title.
     */
    public function get_title()
    {
        // Sanity check: Early exit if no title is defined.
        if (!$this->title) return '';

        return sprintf(
            '<h2 class="notice-title">%s</h2>',
            wp_strip_all_tags($this->title)
        );
    }

    /**
     * Returns the message.
     */
    public function get_message()
    {
        return wpautop(wp_kses($this->message, $this->allowed_html));
    }

    /**
     * Evaluate if we're on the right place depending on the "screens" argument.
     */
    private function is_screen()
    {
        // If screen is empty we want this shown on all screens.
        if (!$this->options['screens'] || empty($this->options['screens'])) return true;

        // Make sure the get_current_screen function exists.
        if (!function_exists('get_current_screen')) require_once ABSPATH . 'wp-admin/includes/screen.php';

        /** @var \WP_Screen $current_screen */
        $current_screen = get_current_screen();

        // Check if we're on one of the defined screens.
        return (in_array($current_screen->id, $this->options['screens'], true));
    }
}
