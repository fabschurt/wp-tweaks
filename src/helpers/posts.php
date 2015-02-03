<?php

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
 * Extracts previous/next posts' URLs from `get_previous_posts_link()` and
 * `get_next_posts_link()` return values.
 *
 * @param string[] $base_urls
 *
 * @return string[]
 */
function _fswpt_extract_nav_urls(array $base_urls)
{
    $regex          = '/href="(.+?)"/';
    $extracted_urls = array();

    if (isset($base_urls['next_posts_link'])) {
        preg_match($regex, $base_urls['next_posts_link'], $matches);
        if (!empty($matches[1])) {
            $extracted_urls['next_posts_url'] = $matches[1];
        }
    }
    if (isset($base_urls['previous_posts_link'])) {
        preg_match($regex, $base_urls['previous_posts_link'], $matches);
        if (!empty($matches[1])) {
            $extracted_urls['previous_posts_url'] = $matches[1];
        }
    }

    return $extracted_urls;
}
