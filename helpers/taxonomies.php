<?php

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
