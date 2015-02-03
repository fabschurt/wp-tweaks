<?php

/**
 * Check if the running version of WordPress is 3.8+.
 *
 * @return boolean
 */
function _fswpt_is_revamped_wordpress_ui()
{
    return (version_compare(get_bloginfo('version'), '3.8') >= 0);
}

/**
 * Returns the IDs of all the blogs in the network, or the current blog ID if
 * multisite is not enabled.
 *
 * @return integer[]
 */
function _fswpt_get_network_blog_ids()
{
    if (!is_multisite()) {
        return array(get_current_blog_id());
    }

    global $wpdb;
    $blog_ids = $wpdb->get_col(
        "
        SELECT *
        FROM `{$wpdb->blogs}`
        ORDER BY `site_id` ASC, `blog_id` ASC
        "
    );

    return array_map('intval', $blog_ids);
}
