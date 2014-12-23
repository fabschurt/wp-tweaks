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
