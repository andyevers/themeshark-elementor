<?php

namespace Themeshark_Elementor\Inc;

if (!defined('ABSPATH')) exit;

final class Helpers
{
    public static function get_dir_url($dir, $url_extension = null)
    {
        $plugin_dirname = basename(THEMESHARK_PATH);
        $dirname_length = strlen($plugin_dirname);

        $dir_url = THEMESHARK_URL . substr($dir, strpos(THEMESHARK_PATH, "$plugin_dirname/") + $dirname_length + 1);
        if ($url_extension) $dir_url .= "/$url_extension";
        return $dir_url;
    }

    /**
     * Returns a gravatar image from assets/images directory
     * @param {String} $type the type of gravatar to be returned
     * @param {Boolean} $html whether to return the gravatar as <img> html. if false, returns src
     */
    public static function get_placeholder_gravatar($type = 'standard', $html = false)
    {
        $images_dir = THEMESHARK_URL . 'assets/images';

        switch ($type) {
            case 'standard':
                $src = "$images_dir/gravatar-standard.png";
                break;
            default:
                TS_Error::die("$type is not a valid placeholder gravitar");
        }

        return $html === true ? "<img src='$src' height='128' width='128'>" : $src;
    }


    /**
     * Gets name of current browser. returns empty string if not recognized.
     * 
     * @return {String} Possible return strings: 'chrome', 'firefox', 'safari', 'internet_explorer', 'opera', 'opera_mini', ''
     */
    public static function get_browser_name()
    {
        // BROWSER STRINGS
        //-----------------------------------------------
        $chrome            = 'chrome';
        $firefox           = 'firefox';
        $safari            = 'safari';
        $internet_explorer = 'internet_explorer';
        $opera             = 'opera';
        $opera_mini        = 'opera_mini';

        $user_agent = $_SERVER['HTTP_USER_AGENT'];

        if (strpos($user_agent, 'MSIE') !== FALSE)           return $internet_explorer;
        elseif (strpos($user_agent, 'Trident') !== FALSE)    return $internet_explorer;
        elseif (strpos($user_agent, 'Firefox') !== FALSE)    return $firefox;
        elseif (strpos($user_agent, 'Chrome') !== FALSE)     return $chrome;
        elseif (strpos($user_agent, 'Opera Mini') !== FALSE) return $opera_mini;
        elseif (strpos($user_agent, 'Opera') !== FALSE)      return $opera;
        elseif (strpos($user_agent, 'Safari') !== FALSE)     return $safari;
        else return '';
    }



    /**
     * Returns all values for a given postmeta key
     */
    public static function get_meta_values($meta_key, $post_type = 'post')
    {
        $posts = get_posts(
            array(
                'post_type' => $post_type,
                'meta_key' => $meta_key,
                'posts_per_page' => -1,
            )
        );

        $meta_values = array();
        foreach ($posts as $post) {
            $meta_values[] = get_post_meta($post->ID, $meta_key, true);
        }

        return $meta_values;
    }

    public static function esc_wysiwyg($string)
    {
        // $allowed_tags = ['p', 'i', 'tbody', 'br', 'em', 'td', 'a', 'strong', 'th', 'img', 'strike', 'tr', 'div', 'u', 'caption', 'span', 's', 'colgroup', 'ul', 'span', 'col', 'li', 'pre', 'tfoot', 'ol', 'table', 'code', 'b', 'thead'];

        // $allowed_tags_string = '';
        // foreach ($allowed_tags as $allowed_tag) {
        //     $allowed_tags_string .= "<$allowed_tag>";
        // }

        // return strip_tags($string, $allowed_tags_string);
        return $string;
    }
}
