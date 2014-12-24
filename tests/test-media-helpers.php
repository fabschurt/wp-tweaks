<?php

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;

class MediaHelpersTest extends WP_UnitTestCase
{
    /**
     * @var vfsStreamDirectory
     */
    private $fsRoot;

    /**
     * @var string
     */
    private $nonExistentFilePath;

    /**
     * @var string
     */
    private $nonReadableFileName;

    /**
     * @var string
     */
    private $assetsPath;

    public function setUp()
    {
        parent::setUp();
        $this->fsRoot              = vfsStream::setup();
        $this->nonExistentFilePath = $this->fsRoot->url().'/nil/void.0';
        $this->nonReadableFileName = 'you-shall-not-read.me';
        $this->assetsPath          = './tests/assets';
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testIptcParsingFailsIfFileDoesNotExist()
    {
        _fswpt_get_iptc_tag_from_file($this->nonExistentFilePath, 'whatever');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testIptcParsingFailsIfFileIsNotReadable()
    {
        $non_readable_file = new vfsStreamFile($this->nonReadableFileName, 0000);
        $this->fsRoot->addChild($non_readable_file);
        _fswpt_get_iptc_tag_from_file($non_readable_file->url(), 'whatever');
    }

    /**
     * @dataProvider      incompatibleFilePathProvider
     * @expectedException InvalidArgumentException
     */
    public function testIptcParsingFailsIfFileIsNotCompatibleImage($file_path)
    {
        _fswpt_get_iptc_tag_from_file($file_path, 'whatever');
    }

    public function testIptcParsingReturnsFalseIfThereIsNoTag()
    {
        $this->assertFalse(_fswpt_get_iptc_tag_from_file("{$this->assetsPath}/image-with-no-iptc-tag.jpg", '105'));
    }

    /**
     * @dataProvider iptcTagsProvider
     */
    public function testIptcTagsCanBeReadFromFile($iptc_tag_id, $iptc_tag_value)
    {
        $this->assertSame(
            _fswpt_get_iptc_tag_from_file("{$this->assetsPath}/image-with-some-iptc-tags.jpg", $iptc_tag_id),
            $iptc_tag_value
        );
    }

    public function testIptcTagArraysCanBeReadFromFile()
    {
        $this->assertSame(
            _fswpt_get_iptc_tag_from_file("{$this->assetsPath}/image-with-some-iptc-tags.jpg", '025', true),
            array('test', 'fake', 'dupe', 'mock', 'stub')
        );
    }

    public function incompatibleFilePathProvider()
    {
        return array(
            array("{$this->assetsPath}/test-file.txt"),
            array("{$this->assetsPath}/incompatible-image-file.png"),
        );
    }

    public function iptcTagsProvider()
    {
        return array(
            array('105', 'This is a freakin\' title'),
            array('120', 'This is some useless description.'),
        );
    }
}
