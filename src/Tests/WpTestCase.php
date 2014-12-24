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
}
