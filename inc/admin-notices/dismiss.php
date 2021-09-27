<?php

namespace Themeshark_Elementor\Inc\Admin_Notices;

/**
 * The Dismiss class, responsible for dismissing and checking the status of admin notices.
 *
 * @since 1.0.0
 */
class Dismiss
{
    /** The notice-ID. */
    public $id;

    /** The prefix we'll be using for the option/user-meta. */
    public $prefix;

    /** $scope The notice's scope. Can be "user" or "global". */
    public $scope;

    /** How long in seconds dismissal lasts. -1 = forever */
    public $duration;

    /** time in seconds in which if dismissed before this date, the message is returned as undismissed. -1 = forever */
    public $undismiss_before;

    /**
     * @param string $id     A unique ID for this notice. Can contain lowercase characters and underscores.
     * @param string $prefix The prefix that will be used for the option/user-meta.
     * @param string $scope  Controls where the dismissal will be saved: user or global.
     * @param string $duration  Negative means forever. how long dismissal lasts before messaged comes back
     * @param string $undismiss_before Undismisses messages that were dismissed before that time
     */
    public function __construct($id, $prefix, $scope = 'global', $duration = -1, $undismiss_before = -1)
    {
        // Set the object properties.
        $this->id               = sanitize_key($id);
        $this->prefix           = sanitize_key($prefix);
        $this->scope            = (in_array($scope, ['global', 'user'], true)) ? $scope : 'global';
        $this->duration         = $duration;
        $this->undismiss_before = $undismiss_before;

        // Handle AJAX requests to dismiss the notice.
        add_action('wp_ajax_themeshark_dismiss_notice', [$this, 'ajax_maybe_dismiss_notice']);
    }

    /**
     * Print the script for dismissing the notice.
     */
    public function print_script()
    {

        // Create a nonce.
        $nonce = wp_create_nonce('themeshark_dismiss_notice_' . $this->id);
?>
        <script>
            window.addEventListener('load', function() {
                var htmlId = '#themeshark-notice-<?php echo esc_attr($this->id); ?>';
                var dismissBtns = document.querySelectorAll(`${htmlId} .notice-dismiss, ${htmlId} .dismiss-link`);

                dismissBtns.forEach(dismissBtn => {
                    // Add an event listener to the dismiss button.

                    if (dismissBtn.classList.contains('dismiss-link')) makeNoticeDismissible(dismissBtn)

                    dismissBtn.addEventListener('click', function(event) {
                        var httpRequest = new XMLHttpRequest(),
                            postData = '';

                        // Build the data to send in our request.
                        // Data has to be formatted as a string here.
                        postData += 'id=<?php echo esc_attr(rawurlencode($this->id)); ?>';
                        postData += '&action=themeshark_dismiss_notice';
                        postData += '&nonce=<?php echo esc_html($nonce); ?>';

                        var data = event.target.dataset,
                            userAction = data ? data['user_action'] : null


                        if (userAction) postData += '&user_action=' + userAction

                        httpRequest.open('POST', '<?php echo esc_url(admin_url('admin-ajax.php')); ?>');
                        httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')
                        httpRequest.send(postData);
                    });
                })

                function makeNoticeDismissible(noticeEl) {
                    const $button = jQuery(noticeEl)
                    const $el = $button.closest('.notice')
                    $button.on('click.wp-dismiss-notice', function(event) {
                        event.preventDefault();
                        $el.fadeTo(100, 0, function() {
                            $el.slideUp(100, function() {
                                $el.remove();
                            });
                        });
                    });
                }
            });
        </script>
<?php
    }



    public function get_dismissal_data()
    {
        $dismissal_record = 'user' === $this->scope
            ? get_user_meta(get_current_user_id(), "{$this->prefix}_{$this->id}", true) //user level dismissal
            : get_option("{$this->prefix}_{$this->id}"); //global level dismissal

        return json_decode($dismissal_record, true);
    }

    /**
     * Check if the notice has been dismissed or not.
     */
    public function is_dismissed()
    {
        $dismissal_record = 'user' === $this->scope
            ? get_user_meta(get_current_user_id(), "{$this->prefix}_{$this->id}", true) //user level dismissal
            : get_option("{$this->prefix}_{$this->id}"); //global level dismissal

        if (!$dismissal_record) return false;

        $dismissal_data = json_decode($dismissal_record, true);

        if ($this->undismiss_before > $dismissal_data['dismissed_time']) return false;
        if ($dismissal_data['dismissed_duration'] < 0) return true;

        return time() - $dismissal_data['dismissed_time'] < $dismissal_data['dismissed_duration'];
    }

    /**
     * Run check to see if we need to dismiss the notice.
     * If all tests are successful then call the dismiss_notice() method.
     */
    public function ajax_maybe_dismiss_notice()
    {
        if (!isset($_POST['action']) || 'themeshark_dismiss_notice' !== $_POST['action']) return;
        if (!isset($_POST['id']) || $this->id !== $_POST['id']) return;
        check_ajax_referer('themeshark_dismiss_notice_' . $this->id, 'nonce', true);


        $user_action = isset($_POST['user_action']) ? sanitize_key($_POST['user_action']) : null;

        $this->dismiss_notice($user_action);
    }



    /**
     * Creates/updates dismiss record with date and duration 
     */
    private function dismiss_notice($user_action = null)
    {
        $dismissal_data = json_encode([
            'dismissed_time'     => time(),
            'dismissed_duration' => $this->duration,
            'user_action'        => $user_action
        ]);

        if ('user' === $this->scope) {
            update_user_meta(get_current_user_id(), "{$this->prefix}_{$this->id}", $dismissal_data);
            return;
        }
        update_option("{$this->prefix}_{$this->id}", $dismissal_data, false);
    }
}
