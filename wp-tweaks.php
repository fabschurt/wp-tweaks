<?php

/*
Plugin Name: WP Tweaks
Plugin URI: https://github.com/fabschurt/wp-tweaks
Description: Some useful WP tweaks.
Author: Fabien Schurter
Author URI: http://fabschurt.net/
Version: 0.1
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
