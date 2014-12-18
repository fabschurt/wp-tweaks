<?php

/*
Plugin Name: WP Tweaks
Plugin URI: https://github.com/fabschurt/wp-tweaks
Description: Some useful WordPress tweaks.
Author: Fabien Schurter
Author URI: http://fabschurt.net/
Version: 0.2.0
License: MIT
License URI: http://opensource.org/licenses/MIT

The MIT License (MIT)

Copyright (c) 2014 Fabien Schurter

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/

// Load dependencies
if (!function_exists('is_plugin_active')) {
    require_once ABSPATH.'wp-admin/includes/plugin.php';
}

/**
 * Returns a taxonomy term's current (1-based) level in taxonomy hierarchy.
 *
 * @param object $term_object
 *
 * @throws InvalidArgumentException If the passed term object is not valid
 *
 * @return integer
 */
function _fswpt_get_taxonomy_term_level($term_object)
{
    // Sanity check
    if (!isset($term_object->term_id) || !isset($term_object->taxonomy)) {
        throw new InvalidArgumentException('Invalid term object.');
    }

    return count(get_ancestors($term_object->term_id, $term_object->taxonomy)) + 1;
}

/**
 * Returns a taxonomy term's highest ancestor in taxonomy hierarchy.
 *
 * @param object $term_object
 *
 * @throws InvalidArgumentException If the passed term object is not valid
 *
 * @return WP_Term|boolean A WP_Term object on success, false on failure
 */
function _fswpt_get_taxonomy_term_ancestor($term_object)
{
    // Sanity check
    if (!isset($term_object->term_id) || !isset($term_object->taxonomy)) {
        throw new InvalidArgumentException('Invalid term object.');
    }

    $term_ancestors        = get_ancestors($term_object->term_id, $term_object->taxonomy);
    $term_highest_ancestor = get_term(end($term_ancestors), $term_object->taxonomy);

    if (!$term_highest_ancestor || is_wp_error($term_highest_ancestor)) {
        return false;
    } else {
        return $term_highest_ancestor;
    }
}

/**
 * Returns the first taxonomy term attached to a post.
 *
 * @param integer $post_id
 * @param string  $taxonomy
 *
 * @return WP_Term|boolean A WP_Term object on success, false on failure
 */
function _fswpt_get_post_main_taxonomy_term($post_id, $taxonomy = 'category')
{
    // Sanity check
    if (!taxonomy_exists($taxonomy)) {
        return false;
    }

    $post_terms = wp_get_post_terms($post_id, $taxonomy);

    if (!$post_terms || is_wp_error($post_terms)) {
        return false;
    } else {
        return $post_terms[0];
    }
}

/**
 * Returns a string containing a list of comma-separated terms in a given taxonomy for a given post.
 *
 * @param integer $post_id
 * @param string  $taxonomy_name
 * @param boolean $with_links
 *
 * @return string
 */
function _fswpt_get_html_term_list_for_post($post_id, $taxonomy_name, $with_links = false)
{
    $terms_list = get_the_term_list($post_id, $taxonomy_name, '', ', ');
    if (!$with_links) {
        $terms_list = strip_tags($terms_list);
    }

    return $terms_list;
}

/**
 * Returns a list of posts belonging to a given post type and filtered by an array of post IDs.
 *
 * @param string    $post_type
 * @param integer[] $post_ids
 * @param boolean   $as_array
 *
 * @return WP_Query|array
 */
function _fswpt_get_posts_per_ids($post_type, array $post_ids, $as_array = false)
{
    $list = new WP_Query(array(
        'post_type'           => $post_type,
        'post__in'            => $post_ids,
        'posts_per_page'      => -1,
        'ignore_sticky_posts' => true,
    ));
    if ($as_array) {
        $list = $list->posts;
    }

    return $list;
}

/**
 * Returns a post ID, according to current language.
 *
 * Compatible with: Polylang.
 *
 * @param integer $base_post_id
 *
 * @return integer
 */
function _fswpt_get_i18n_post_id($base_post_id)
{
    // Sanity check
    if (!function_exists('is_plugin_active')) {
        require_once ABSPATH.'wp-admin/includes/plugin.php';
    }

    $post_id = $base_post_id;
    if (is_plugin_active('polylang/polylang.php')) {
        $post_id = pll_get_post($base_post_id);
    }

    return intval($post_id);
}

/**
 * Returns a taxonomy term ID, according to current language.
 *
 * Compatible with: Polylang.
 *
 * @param integer $base_term_id
 *
 * @return integer
 */
function _fswpt_get_i18n_term_id($base_term_id)
{
    // Sanity check
    if (!function_exists('is_plugin_active')) {
        require_once ABSPATH.'wp-admin/includes/plugin.php';
    }

    $term_id = $base_term_id;
    if (is_plugin_active('polylang/polylang.php')) {
        $term_id = pll_get_term($base_term_id);
    }

    return intval($term_id);
}

/**
 * Returns the permalink for a post, according to current language.
 *
 * @param integer $base_post_id
 * @param boolean $escape
 *
 * @return string
 */
function _fswpt_get_i18n_permalink($base_post_id, $escape = true)
{
    $i18n_post_id = _fswpt_get_i18n_post_id($base_post_id);
    $permalink    = get_permalink($i18n_post_id);
    if ($escape) {
        $permalink = esc_url($permalink);
    }

    return $permalink;
}

/**
 * Returns the absolute URL of a theme asset.
 *
 * @param string  $relative_url
 * @param boolean $escape
 *
 * @return string
 */
function _fswpt_get_asset($relative_url, $escape = true)
{
    $url = get_stylesheet_directory_uri()."/{$relative_url}";
    if ($escape) {
        $url = esc_url($url);
    }

    return $url;
}

/**
 * Returns the absolute URL of an image attachment, according to format.
 *
 * @param integer $attachment_id
 * @param string  $format
 * @param boolean $escape
 *
 * @return string
 */
function _fswpt_get_image_src($attachment_id, $format = 'thumbnail', $escape = true)
{
    $img_src = wp_get_attachment_image_src($attachment_id, $format);
    $src     = (isset($img_src[0]) ? $img_src[0] : '');
    if ($escape) {
        $src = esc_url($src);
    }

    return $src;
}

/**
 * Cleanly terminates an AJAX call in case of error.
 *
 * @param string $error_msg
 *
 * @return void
 */
function _fswpt_terminate_ajax($error_msg = 'An AJAX error has occured.')
{
    header('HTTP/1.0 500 Internal Server Error');
    header('Content-Type: text/plain; charset=UTF-8');
    echo $error_msg;
    exit();
}

// Enforce locale to match current Polylang language
if (is_plugin_active('polylang/polylang.php')) {
    setlocale(LC_ALL, pll_current_language('locale'));
}

// Clean HTML <head> up a bit
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head');

// Remove the «Private» label from the beginning of private posts' titles
add_filter('private_title_format', function($title) {
    return '%s';
});

// Hide frontend admin bar from regular users
add_filter('show_admin_bar', function($show_admin_bar) {
    return current_user_can('manage_options');
});

// Ensure that some sensitive admin menus are hidden from regular users
add_action('admin_menu', function() {
    if (!current_user_can('manage_options')) {
        remove_menu_page('wpfront-user-role-editor-all-roles');
        remove_menu_page('tools.php');
        remove_menu_page('options-general.php');
    }
});

// In user list, hide super admin users from all but themselves
add_action('pre_user_query', function() {
    if (current_user_can('manage_options')) {
        return;
    }

    global $wpdb;
    $user_search->query_where = str_replace(
        'WHERE 1=1',
        "WHERE 1=1 AND {$wpdb->users}.ID != 1",
        $user_search->query_where
    );
});

// In development, send all outbound e-mails to the blog's e-mail address
add_filter('wp_mail', function (array $params) {
    if (defined('WP_ENV') && WP_ENV === 'development') {
        $params['from'] = $params['to'] = get_bloginfo('admin_email');
    }

    return $params;
});
