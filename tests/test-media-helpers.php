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

    public function setUp()
    {
        parent::setUp();
        $this->fsRoot = vfsStream::setup();
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testIptcParsingFailsIfFileDoesNotExist()
    {
        $non_existent_path = $this->fsRoot->url().'/nil/void.0';
        _fswpt_get_iptc_tag_from_file($non_existent_path, 'whatever');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testIptcParsingFailsIfFileIsNotReadable()
    {
        $non_readable_file_path = new vfsStreamFile('you-shall-not-read.me', 0000);
        $this->fsRoot->addChild($non_readable_file_path);
        _fswpt_get_iptc_tag_from_file($non_readable_file_path->url(), 'whatever');
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
        $this->assertFalse(_fswpt_get_iptc_tag_from_file('./tests/assets/image-with-no-iptc-tag.jpg', '105'));
    }

    /**
     * @dataProvider iptcTagsProvider
     */
    public function testIptcTagsCanBeReadFromFile($iptc_tag_id, $iptc_tag_value)
    {
        $this->assertSame(
            _fswpt_get_iptc_tag_from_file('./tests/assets/image-with-some-iptc-tags.jpg', $iptc_tag_id),
            $iptc_tag_value
        );
    }

    public function testIptcTagArraysCanBeReadFromFile()
    {
        $this->assertSame(
            _fswpt_get_iptc_tag_from_file('./tests/assets/image-with-some-iptc-tags.jpg', '025', true),
            array('test', 'fake', 'dupe', 'mock', 'stub')
        );
    }

    public function incompatibleFilePathProvider()
    {
        return array(
            array('./tests/assets/test-file.txt'),
            array('./tests/assets/incompatible-image-file.png'),
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
