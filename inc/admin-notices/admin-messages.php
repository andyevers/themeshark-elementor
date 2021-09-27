<?php

namespace Themeshark_Elementor\Inc\Admin_Notices;

if (!defined('ABSPATH')) exit;

use Themeshark_Elementor\Pages;

require_once __DIR__ . '/dismiss.php';
require_once __DIR__ . '/notice.php';
require_once __DIR__ . '/notices.php';

/**
 * ThemeShark Messages
 *
 * Handles all messages that appear on the admin dashboard
 * and when to display them
 *
 * @since 1.0.0
 */
final class Admin_Messages
{


    private static $_instance = null;

    /** @var Notices notices object */
    private $notices;

    public static function register()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public static function _($string)
    {
        return __($string, 'elementor-themeshaark');
    }

    public function __construct()
    {
        if (!is_admin()) return;
        add_action('admin_init', [$this, 'init']);
    }

    public function init()
    {
        $this->notices = new Notices();
        $this->get_messages();
        $this->notices->boot();
    }

    public function get_messages()
    {
        $this->message_contribute();
        $this->message_leave_rating();
        $this->message_updated_settings();
    }

    private function get_rating_link()
    {
        $codecanyon_link  = \Themeshark_Elementor::RATING_URL;
        return $codecanyon_link;
    }


    private function days_until($time)
    {
        $remaining_seconds = $time - time();
        $days = round($remaining_seconds / DAY_IN_SECONDS);
        return $days;
    }

    private function days_passed($time)
    {
        return $this->days_until($time) * -1;
    }

    public function get_notice($notice_id)
    {
        $notices = $this->notices->get_all();

        foreach ($notices as $notice) {
            if ($notice->id === $notice_id) {
                return $notice;
            }
        }
        return null;
    }

    public function get_dismissal_record($notice_id, $scope = 'global', $notice_prefix = Notice::DEFAULT_PREFIX)
    {
        $dismissal_record = 'user' === $scope
            ? get_user_meta(get_current_user_id(), "{$notice_prefix}_{$notice_id}", true) //user level dismissal
            : get_option("{$notice_prefix}_{$notice_id}"); //global level dismissal

        return json_decode($dismissal_record, true);
    }

    private function create_dismissal_action_link($user_action = null, $message = null, $additional_classes = '')
    {
        $user_action = $user_action === null ? '' : "data-user_action='$user_action'";
        $message     = $message === null ? '' : $message;
        return "<a class='dismiss-link $additional_classes' $user_action>$message</a>";
    }

    private function create_notice_button($text, $href, $type = 'standard')
    {
        return "<a class='themeshark-notice-button themeshark-notice-button-$type' href='$href' target='_blank'>"
            . self::_($text) . "</a>";
    }


    public function get_last_dismissal_action($dismissal_record)
    {
        $previous_dismiss_action = $dismissal_record && $dismissal_record['user_action']
            ? $dismissal_record['user_action'] : null;

        return $previous_dismiss_action;
    }

    private function get_get_data($get_key)
    {
        return isset($_GET[$get_key]) ? $_GET[$get_key] : null;
    }


    // MESSAGES
    //-----------------------------------------------


    public function message_contribute()
    {
        $patreon_link = \Themeshark_Elementor::PATREON_URL;
        $btn_classes_standard  = 'themeshark-notice-button themeshark-notice-button-standard';
        $btn_classes_secondary = 'themeshark-notice-button themeshark-notice-button-secondary';

        // action links
        $ok_help  = "<a class='$btn_classes_standard' href='$patreon_link' target='_blank'>"
            . self::_("Ok, I'll help out!") . "</a>";

        $no_help  = $this->create_dismissal_action_link(
            'no_patreon',
            self::_('No, I don\'t want to'),
            $btn_classes_secondary
        );

        //message
        $message = self::_("Hey there! Could you please help me out by becoming a Patreon? This allows me to continue creating awesome templates and widgets available for everyone. Any contribution helps a ton! <br> - Andrew Evers, ThemeShark Templates & Widgets for Elementor Creator")
            . "<div class='themeshark-notice-actions'>"
            . "$ok_help $no_help"
            . "</div>";


        $this->notices->add('become_patreon', self::_('Please Consider Helping Out By Becoming A Patreon!'), $message, [
            'dismissed_duration' => WEEK_IN_SECONDS,
            'use_sidebar'        => true,
            'container_type'     => 'thick',
        ]);
    }

    /**
     * Shows after a week of usage
     */
    public function message_leave_rating()
    {
        $rating_link        = $this->get_rating_link();
        $install_date       = \Themeshark_Elementor\Plugin::get_install_date();
        $days_after_install = $this->days_passed($install_date);

        if ($days_after_install < 7) return;

        $dismissal_data        = $this->get_dismissal_record('leave_rating');
        $last_dismissal_action = $this->get_last_dismissal_action($dismissal_data);

        $btn_classes_standard  = 'themeshark-notice-button themeshark-notice-button-standard';
        $btn_classes_secondary = 'themeshark-notice-button themeshark-notice-button-secondary';

        // action links
        $you_deserve_it  = "<a class='$btn_classes_standard' href='$rating_link' target='_blank'>"
            . self::_("Ok, you deserve it") . "</a>";

        $already_rated   = $this->create_dismissal_action_link(
            'already_rated',
            self::_('I already did'),
            $btn_classes_secondary
        );
        $not_good_enough = $this->create_dismissal_action_link(
            'not_good_enough',
            self::_('No, not good enough'),
            $btn_classes_secondary
        );

        //message
        $stars   = '<span style="color:#ec9713;">&#9733;&#9733;&#9733;&#9733;&#9733;</span>';
        $message = self::_("You've been using ThemeShark Templates & Widgets for Elementor for more than 1 week. ")
            . self::_("Would you mind taking a few seconds to give it a 5-star $stars rating?")
            . self::_("Thank you in advance :) ")
            . "<div class='themeshark-notice-actions'>"
            . "$you_deserve_it $not_good_enough $already_rated"
            . "</div>";

        //dismiss duration
        $permanent_actions  = ['already_rated', 'not_good_enough'];
        $dismissed_duration = in_array($last_dismissal_action, $permanent_actions) ? -1 : WEEK_IN_SECONDS * 3;

        $this->notices->add('leave_rating', self::_('Please Leave A Rating!'), $message, [
            'dismissed_duration' => $dismissed_duration,
            'container_type'     => 'thick',
        ]);
    }


    /**
     * When settings page is updated
     */
    public function message_updated_settings()
    {
        $is_settings_page    = $this->get_get_data('page') === Pages\Page_Settings::SLUG;
        $is_settings_updated = $this->get_get_data('settings-updated');

        if (!($is_settings_page && $is_settings_updated)) return;

        $message = self::_('ThemeShark settings have been updated.');
        $this->notices->add('settings_updated', null, $message, [
            'type'           => 'success',
            'is_dismissible' => false
        ]);
    }
}
