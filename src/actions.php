<?php

// Enforce locale to match current Polylang language
if (is_plugin_active('polylang/polylang.php')) {
    setlocale(LC_ALL, pll_current_language('locale'));
}

// Clean HTML <head> up a bit
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head');

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
