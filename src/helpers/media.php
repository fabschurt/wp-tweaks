<?php

/**
 * Parses a given IPTC tag from an image file and returns its value.
 *
 * @param string  $file_path       Path to the file to parse the IPTC tag from
 * @param string  $iptc_tag        The IPTC tag to parse
 * @param boolean $multiple_values Whether the entire array of values shall be returned, or its first element only
 *
 * @throws RuntimeException         If required PHP functions are not available
 * @throws InvalidArgumentException If $file_path does not exist or is not readable
 * @throws InvalidArgumentException If $file_path is not an image with IPTC tags support (i.e. JPEG or TIFF)
 *
 * @return string[]|string|boolean A string value or an array of string values, or false on failure
 */
function _fswpt_get_iptc_tag_from_file($file_path, $iptc_tag, $multiple_values = false)
{
    // Sanity check
    if (!function_exists('getimagesize') || !function_exists('iptcparse')) {
        throw new RuntimeException('Required PHP functions are not available.');
    }
    if (!is_file($file_path) || !is_readable($file_path)) {
        throw new InvalidArgumentException('Target file does not exist or is not readable.');
    }
    $file_type = wp_check_filetype($file_path);
    if (!in_array($file_type['type'], array('image/jpeg', 'image/tiff'), true)) {
        throw new InvalidArgumentException('Target file must be an image supporting IPTC tags (JPEG or TIFF namely).');
    }

    $img_info = getimagesize($file_path, $img_metadata);
    if (empty($img_metadata['APP13'])) {
        return false;
    }

    $iptc_data     = iptcparse($img_metadata['APP13']);
    $iptc_tag_name = '2#'.$iptc_tag;
    if (
        isset($iptc_data[$iptc_tag_name])
        && is_array($iptc_data[$iptc_tag_name])
        && count($iptc_data[$iptc_tag_name])
    ) {
        $values = array_map('trim', $iptc_data[$iptc_tag_name]);

        return ($multiple_values ? $values : $values[0]);
    }

    return false;
}

/**
 * Manually inserts an attachement into the WP platform.
 *
 * @param string  $file_path
 * @param integer $parent_post_id
 * @param string  $fallback_title
 *
 * @throws InvalidArgumentException If $file_path does not exist or is not readable
 * @throws RuntimeException         If an error occurs while uploading the file
 * @throws RuntimeException         If an error occurs while inserting attachment data in the DB
 *
 * @return integer ID of the newly inserted attachment
 */
function _fswpt_insert_attachment($file_path, $parent_post_id = 0, $fallback_title = null)
{
    // Sanity check
    if (!is_file($file_path) || !is_readable($file_path)) {
        throw new InvalidArgumentException('Target file does not exist or is not readable.');
    }

    // Handle initial upload
    $file_info     = pathinfo($file_path);
    $uploaded_file = wp_upload_bits($file_info['basename'], null, file_get_contents($file_path));
    if (!empty($uploaded_file['error'])) {
        throw new RuntimeException('Error while uploading local attachment: '.$uploaded_file['error']);
    }

    // Set post title
    $post_title = ($fallback_title ? $fallback_title : $file_info['filename']);

    // Require image library
    if (!function_exists('wp_read_image_metadata')) {
        require_once ABSPATH.'wp-admin/includes/image.php';
    }

    // For JPEG files, try to read title and description from file metadata
    $post_content = '';
    $filetype     = wp_check_filetype($uploaded_file['file']);
    if ($filetype['type'] === 'image/jpeg') {
        $img_metadata = wp_read_image_metadata($uploaded_file['file']);
        if (!empty($img_metadata['title'])) {
            $post_title = $img_metadata['title'];
        }
        if (!empty($img_metadata['caption'])) {
            $post_content = $img_metadata['caption'];
        }
    }

    // Insert attachment
    $attachment = array(
        'post_parent'    => ($parent_post_id ? $parent_post_id : 0),
        'post_title'     => $post_title,
        'post_mime_type' => $filetype['type'],
        'post_content'   => $post_content,
        'guid'           => $uploaded_file['url'],
    );
    $attachment_id = wp_insert_attachment($attachment, $uploaded_file['file']);
    if (is_wp_error($attachment_id)) {
        throw new RuntimeException('Error while inserting new attachment: '.$attachment_id->get_error_message());
    }

    // Parse and save image metadata
    if (wp_attachment_is_image($attachment_id)) {
        wp_update_attachment_metadata(
            $attachment_id,
            wp_generate_attachment_metadata($attachment_id, $uploaded_file['file'])
        );
    }

    return $attachment_id;
}

/**
 * Manually inserts a batch of files (stored in a ZIP archive) into the WP platform.
 *
 * Supports: ZIP (.zip) files only.
 *
 * @param WP_Filesystem_Base $wp_filesystem
 * @param string             $zip_file_path
 * @param integer            $parent_post_id
 *
 * @throws InvalidArgumentException If $zip_file_path does not exist or is not readable
 * @throws InvalidArgumentException If $zip_file_path is not a ZIP (.zip) file
 * @throws RuntimeException         If a temp dir cannot be created for file extraction
 * @throws RuntimeException         If an error occurs while unzipping the archive
 */
function _fswpt_insert_attachments_from_zip(WP_Filesystem_Base $filesystem, $zip_file_path, $parent_post_id = 0)
{
    // Sanity check
    if (!is_file($zip_file_path) || !is_readable($zip_file_path)) {
        throw new InvalidArgumentException('Target file does not exist or is not readable.');
    }
    $filetype = wp_check_filetype($zip_file_path);
    if ($filetype['type'] !== 'application/zip') {
        throw new InvalidArgumentException('Target file is not a ZIP (.zip) file.');
    }

    // Try to create a temp dir
    $tmp_dir_path = rtrim(get_temp_dir(), '/').'/'.uniqid('fswpt_', true);
    if (!$filesystem->mkdir($tmp_dir_path, 0777)) {
        throw new RuntimeException('Could not create a temporary directory.');
    }

    // Try to unzip files
    $result = unzip_file($zip_file_path, $tmp_dir_path);
    if (is_wp_error($result)) {
        throw new RuntimeException('Error while unzipping files: '.$result->get_error_message());
    }

    // Try to insert each file as attachment
    $files = array_filter(scandir($tmp_dir_path), function($filename) {
        return ($filename !== '.' && $filename !== '..');
    });
    foreach ($files as $filename) {
        // Sanity check
        $file_path = "{$tmp_dir_path}/{$filename}";
        if (!is_file($file_path) || !is_readable($file_path) || !filesize($file_path)) {
            continue;
        }

        _fswpt_insert_attachment($file_path, $parent_post_id);
    }

    // Clean the mess up
    $filesystem->rmdir($tmp_dir_path, true);
}

/**
 * Tries to fetch the created date from an attachement's metadata.
 *
 * @param integer $attachment_id
 *
 * @return integer|boolean The timestamp on success, false on failure or metadata not found
 */
function _fswpt_get_attachment_created_date($attachment_id)
{
    $timestamp       = false;
    $attachment_meta = wp_get_attachment_metadata($attachment_id);
    if ($attachment_meta && !empty($attachment_meta['image_meta']['created_timestamp'])) {
        $timestamp = intval($attachment_meta['image_meta']['created_timestamp']);
    }

    return $timestamp;
}
