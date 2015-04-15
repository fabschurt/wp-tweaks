<?php

/**
 * This file is part of the fabschurt/wp-tweaks package.
 *
 * (c) 2014-2015 Fabien Schurter <dev@fabschurt.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author    Fabien Schurter <dev@fabschurt.net>
 * @license   MIT
 * @copyright 2014-2015 Fabien Schurter
 */

// Load dependencies
if (!function_exists('is_plugin_active')) {
    require_once ABSPATH.'wp-admin/includes/plugin.php';
}

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
add_action('pre_user_query', function($userSearch) {
    if (current_user_can('manage_options')) {
        return;
    }

    global $wpdb;
    $userSearch->query_where = str_replace(
        'WHERE 1=1',
        "WHERE 1=1 AND {$wpdb->users}.ID != 1",
        $userSearch->query_where
    );
});

// Load language file overrides if needed
add_action('load_textdomain', function($domain, $moFilePath) {
    global $language_domains_to_override;
    if (!$language_domains_to_override) {
        return;
    }

    $overridePath = sprintf(
        '%1$s/%2$s/%2$s-%3$s.mo',
        WP_LANG_DIR,
        $domain,
        apply_filters('plugin_locale', get_locale(), $domain)
    );
    if (in_array($domain, $language_domains_to_override, true) && $moFilePath !== $overridePath) {
        load_textdomain($domain, $overridePath);
    }
}, 10, 2);

// Disable admin UI customization for unauthorized users
add_action('init', function() {
    if (!current_user_can('manage_options') && !isset($_COOKIE['wp_allow_admin_ui_customization'])) {
        add_action('admin_init', function() {
            wp_deregister_script('postbox');
        });
        add_action('admin_head', function() { ?>
            <style>
                #screen-options-link-wrap,
                .postbox > .handlediv {
                    display: none !important;
                }

                .postbox > .hndle {
                    cursor: auto !important;
                }
            </style>
        <?php });
    }
});
