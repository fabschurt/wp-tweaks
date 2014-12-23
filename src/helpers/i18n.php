<?php

/**
 * Returns a post ID, according to current language.
 *
 * Compatible with: Polylang.
 *
 * @param integer $base_post_id
 *
 * @return integer
 */
function _fswpt_get_i18n_post_id($base_post_id)
{
    // Sanity check
    if (!function_exists('is_plugin_active')) {
        require_once ABSPATH.'wp-admin/includes/plugin.php';
    }

    $post_id = $base_post_id;
    if (is_plugin_active('polylang/polylang.php')) {
        $post_id = pll_get_post($base_post_id);
    }

    return intval($post_id);
}

/**
 * Returns a taxonomy term ID, according to current language.
 *
 * Compatible with: Polylang.
 *
 * @param integer $base_term_id
 *
 * @return integer
 */
function _fswpt_get_i18n_term_id($base_term_id)
{
    // Sanity check
    if (!function_exists('is_plugin_active')) {
        require_once ABSPATH.'wp-admin/includes/plugin.php';
    }

    $term_id = $base_term_id;
    if (is_plugin_active('polylang/polylang.php')) {
        $term_id = pll_get_term($base_term_id);
    }

    return intval($term_id);
}

/**
 * Returns the permalink for a post, according to current language.
 *
 * @param integer $base_post_id
 * @param boolean $escape
 *
 * @return string
 */
function _fswpt_get_i18n_permalink($base_post_id, $escape = true)
{
    $i18n_post_id = _fswpt_get_i18n_post_id($base_post_id);
    $permalink    = get_permalink($i18n_post_id);
    if ($escape) {
        $permalink = esc_url($permalink);
    }

    return $permalink;
}
