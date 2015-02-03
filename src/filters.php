<?php

// Remove the «Private» label from the beginning of private posts' titles
add_filter('private_title_format', function($title) {
    return '%s';
});

// Hide frontend admin bar from regular users
add_filter('show_admin_bar', function($show_admin_bar) {
    return current_user_can('manage_options');
});

// In development, send all outbound e-mails from and to the blog's e-mail address
add_filter('wp_mail', function(array $params) {
    if (defined('WP_ENV') && WP_ENV === 'development') {
        $params['from'] = $params['to'] = get_bloginfo('admin_email');
    }

    return $params;
});
