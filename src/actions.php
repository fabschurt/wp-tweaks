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
        remove_menu_page('tools.php');
        remove_menu_page('options-general.php');
    }
});

// In user list, hide super admin users from all but themselves
add_action('pre_user_query', function($user_search) {
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

// Load language file overrides if needed
add_action('load_textdomain', function($domain, $mo_file_path) {
    global $language_domains_to_override;
    if (!$language_domains_to_override) {
        return;
    }

    $override_path = WP_LANG_DIR.sprintf('/%1$s/%1$s-%2$s.mo',
                                         $domain,
                                         apply_filters('plugin_locale', get_locale(), $domain));
    if (in_array($domain, $language_domains_to_override, true) && $mo_file_path !== $override_path) {
        load_textdomain($domain, $override_path);
    }
}, 10, 2);
