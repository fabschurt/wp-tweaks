<?php

/**
 * Returns the absolute URL of a theme asset.
 *
 * @param string  $relative_url If the URL starts with `@bower/`, the prefix will be the value of
 *                              $GLOBALS['bower_components_root_url'] if it exists
 * @param boolean $escape
 *
 * @return string
 */
function _fswpt_get_asset($relative_url, $escape = true)
{
    if (isset($GLOBALS['bower_components_root_url']) && substr($relative_url, 0, 7) === '@bower/') {
        $url = $GLOBALS['bower_components_root_url'].'/'.substr($relative_url, 7);
    } else {
        $url = get_stylesheet_directory_uri()."/{$relative_url}";
    }
    if ($escape) {
        $url = esc_url($url);
    }

    return $url;
}

/**
 * Returns the absolute URL of an image attachment, according to format.
 *
 * @param integer $attachment_id
 * @param string  $format
 * @param boolean $escape
 *
 * @return string
 */
function _fswpt_get_image_src($attachment_id, $format = 'thumbnail', $escape = true)
{
    $img_src = wp_get_attachment_image_src($attachment_id, $format);
    $src     = (isset($img_src[0]) ? $img_src[0] : '');
    if ($escape) {
        $src = esc_url($src);
    }

    return $src;
}
