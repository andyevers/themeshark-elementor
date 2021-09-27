<?php

namespace Themeshark_Elementor\Inc\Admin_Notices;

/**
 * The Admin_Notice class, responsible for creating admin notices.
 *
 * Each notice is a new instance of the object.
 *
 * @since 1.0.0
 */
class Notices
{

    const UNDISMISS_ALL = '_ALL_';

    /**
     * An array of notices.
     */
    private $notices = [];

    /**
     * Adds actions for the notices.
     */
    public function boot()
    {
        // Add the notice.
        add_action('admin_notices', [$this, 'the_notices']);

        // Print the script to the footer.
        add_action('admin_footer', [$this, 'print_scripts']);
    }

    /**
     * Add a notice.
     * 
     * @param string $id      A unique ID for this notice. Can contain lowercase characters and underscores.
     * @param string $title   The title for our notice.
     * @param string $message The message for our notice.
     * @param array  $options An array of additional options to change the defaults for this notice.
     *                        See Notice::__constructor() for details.
     * @return void
     */
    public function add($id, $title, $message, $options = [])
    {
        $this->notices[$id] = new Notice($id, $title, $message, $options);
    }

    /**
     * Remove a notice.
     */
    public function remove($id)
    {
        unset($this->notices[$id]);
    }

    /**
     * Get a single notice.
     * 
     * @return Notice|null
     */
    public function get($id)
    {
        if (isset($this->notices[$id])) {
            return $this->notices[$id];
        }
        return null;
    }

    /**
     * Get all notices.
     * 
     * @return array
     */
    public function get_all()
    {
        return $this->notices;
    }

    /**
     * Prints the notice.
     */
    public function the_notices()
    {
        $notices = $this->get_all();

        foreach ($notices as $notice) {
            $notice->the_notice();
        }
    }

    /**
     * Prints scripts for the notices.
     */
    public function print_scripts()
    {
        $notices = $this->get_all();

        foreach ($notices as $notice) {
            if ($notice->show()) {
                $notice->dismiss->print_script();
            }
        }
    }
}
