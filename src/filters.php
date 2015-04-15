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

// Remove the «Private» label from the beginning of private posts' titles
add_filter('private_title_format', function($title) {
    return '%s';
});

// Hide frontend admin bar from regular users
add_filter('show_admin_bar', function($showAdminBar) {
    return current_user_can('manage_options');
});

// In development, send all outbound e-mails from and to the blog's e-mail address
add_filter('wp_mail', function(array $params) {
    if (defined('WP_ENV') && WP_ENV === 'development') {
        $params['to'] = get_bloginfo('admin_email');
    }

    return $params;
});
add_filter('wp_mail_from', function($toAddr) {
    if (defined('WP_ENV') && WP_ENV === 'development') {
        return get_bloginfo('admin_email');
    }

    return $toAddr;
});
