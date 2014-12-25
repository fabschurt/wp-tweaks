<?php

namespace Fabschurt\WpTweaks\Tests;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;

abstract class WpTestCase extends \WP_UnitTestCase
{
    /**
     * @var vfsStreamDirectory
     */
    private $mockFsRoot;

    /**
     * @var vfsStreamDirectory
     */
    private $mockTempDir;

    /**
     * @var string
     */
    private $nonExistentFilePath;

    /**
     * @var vfsStreamFile
     */
    private $nonReadableFile;

    /**
     * @var string
     */
    private $assetsPath = './tests/assets';

    /**
     * @return vfsStreamDirectory
     */
    protected function getMockFilesystemRoot()
    {
        if (is_null($this->mockFsRoot)) {
            $this->mockFsRoot = vfsStream::setup();
        }

        return $this->mockFsRoot;
    }

    protected function getMockTempDir()
    {
        if (is_null($this->mockTempDir)) {
            $this->mockTempDir = new vfsStreamDirectory('tmp', 0777);
            $this->getMockFilesystemRoot()->addChild($this->mockTempDir);
        }

        return $this->mockTempDir;
    }

    /**
     * @return string
     */
    protected function getNonExistentFilePath()
    {
        return $this->getMockFilesystemRoot()->url().'/nil/void.0';
    }

    /**
     * @return vfsStreamFile
     */
    protected function getNonReadableFile()
    {
        if (is_null($this->nonReadableFile)) {
            $this->nonReadableFile = new vfsStreamFile('you-shall-not-read.me', 0000);
            $this->getMockFilesystemRoot()->addChild($this->nonReadableFile);
        }

        return $this->nonReadableFile;
    }

    /**
     * @return string
     */
    protected function getAssetsPath()
    {
        return $this->assetsPath;
    }

    /**
     * @throws \RuntimeException If the filesystem object can't be initialized
     *
     * @return \WP_Filesystem
     */
    protected function getWpFilesystem()
    {
        global $wp_filesystem;

        if (!function_exists('WP_Filesystem')) {
            require_once ABSPATH.'wp-admin/includes/file.php';
        }
        if (!isset($wp_filesystem) && !WP_Filesystem()) {
            throw new \RuntimeException('Error while initializing the filesystem object.');
        }

        return $wp_filesystem;
    }

    /**
     * @param string    $field_name
     * @param WP_Post[] $posts_array
     * @param callable  $filter_callback optional
     *
     * @return array
     */
    protected function extractFieldValuesFromPostsArray($field_name,
                                                        array $posts_array,
                                                        $filter_callback = null)
    {
        $container = array();
        foreach ($posts_array as $post) {
            if (isset($post->$field_name)) {
                $value = $post->$field_name;
                if (!is_null($filter_callback) && is_callable($filter_callback)) {
                    $value = call_user_func($filter_callback, $value);
                }
                $container[] = $value;
            }
        }

        return $container;
    }

    /**
     * @return void
     */
    protected function setTestTheme()
    {
        add_filter('stylesheet', function() {
            return 'twentyfifteen';
        });
        add_filter('template', function() {
            return 'twentyfifteen';
        });
    }

    /**
     * @param integer $limit
     *
     * @return object|object[]
     */
    protected function getLastAttachmentRows($limit = 1)
    {
        global $wpdb;

        $attachments = $wpdb->get_results($wpdb->prepare(
            "
            SELECT * FROM `{$wpdb->posts}`
            WHERE `post_type` LIKE 'attachment'
            AND `post_status` LIKE 'inherit'
            ORDER BY `ID` DESC
            LIMIT %d
            ", $limit
        ));

        return (count($attachments) == 1 ? $attachments[0] : $attachments);
    }

    /**
     * @return void
     */
    protected function deleteAllUploads()
    {
        $upload_dir_info = wp_upload_dir();
        if (empty($upload_dir_info['basedir'])) {
            throw new RuntimeException('wp_upload_dir() failed to return required information.');
        }

        $upload_dir = $upload_dir_info['basedir'];
        $uploads    = array_filter(scandir($upload_dir), function($element) {
            return ($element !== '.' && $element !== '..');
        });
        foreach ($uploads as $upload) {
            $this->getWpFilesystem()->delete("{$upload_dir}/{$upload}", true);
        }
    }
}