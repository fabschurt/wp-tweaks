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

    /**
     * @expectedException InvalidArgumentException
     */
    public function testAttachmentInsertionFailsIfFileDoesNotExist()
    {
        _fswpt_insert_attachment($this->nonExistentFilePath);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testAttachmentInsertionFailsIfFileIsNotReadable()
    {
        $non_readable_file = new vfsStreamFile($this->nonReadableFileName, 0000);
        $this->fsRoot->addChild($non_readable_file);
        _fswpt_insert_attachment($non_readable_file->url());
    }

    public function testAttachmentInsertionWithDefaultArgumentsReturnsIdOfLastInsertedAttachment()
    {
        $attachment_id   = _fswpt_insert_attachment("{$this->assetsPath}/Some_good_advice.pdf");
        $last_attachment = $this->getLastAttachmentRow();
        $this->assertSame($attachment_id, intval($last_attachment->ID));

        return $last_attachment;
    }

    /**
     * @depends testAttachmentInsertionWithDefaultArgumentsReturnsIdOfLastInsertedAttachment
     */
    public function testAttachmentInsertionWithDefaultArgumentsCreatesAnOrphanAttachment($last_attachment)
    {
        $this->assertSame(intval($last_attachment->post_parent), 0);

        return $last_attachment;
    }

    /**
     * @depends testAttachmentInsertionWithDefaultArgumentsCreatesAnOrphanAttachment
     */
    public function testAttachmentInsertionWithDefaultArgumentsGetsTitleFromFileName($last_attachment)
    {
        $this->assertSame($last_attachment->post_title, 'Some_good_advice');

        return $last_attachment;
    }

    public function testAttachmentInsertionAcceptsCustomParentId()
    {
        $parent_id = 9001;
        _fswpt_insert_attachment("{$this->assetsPath}/Some_good_advice.pdf", $parent_id);
        $last_attachment = $this->getLastAttachmentRow();
        $this->assertSame($parent_id, intval($last_attachment->post_parent));
    }

    public function testAttachmentInsertionAcceptsCustomTitle()
    {
        $title = 'Awesome title';
        _fswpt_insert_attachment("{$this->assetsPath}/Some_good_advice.pdf", 0, $title);
        $last_attachment = $this->getLastAttachmentRow();
        $this->assertSame($title, $last_attachment->post_title);
    }

    public function testTitleIsReadFromIptcTagsAndTitlePassedAsArgumentIsIgnoredDuringAttachmentInsertion()
    {
        _fswpt_insert_attachment("{$this->assetsPath}/image-with-some-iptc-tags.jpg", 0, 'Ignored title');
        $last_attachment = $this->getLastAttachmentRow();
        $data_provider   = $this->iptcTagsProvider();
        $this->assertSame($data_provider[0][1], $last_attachment->post_title);

        return $last_attachment;
    }

    /**
     * @depends testTitleIsReadFromIptcTagsAndTitlePassedAsArgumentIsIgnoredDuringAttachmentInsertion
     */
    public function testContentIsReadFromIptcTagsDuringAttachmentInsertion($last_attachment)
    {
        $data_provider = $this->iptcTagsProvider();
        $this->assertSame($data_provider[1][1], $last_attachment->post_content);
    }

    public function testMetadataIsGeneratedDuringAttachmentInsertion()
    {
        _fswpt_insert_attachment("{$this->assetsPath}/image-with-some-iptc-tags.jpg");
        $last_attachment = $this->getLastAttachmentRow();
        $metadata        = get_post_meta($last_attachment->ID, '_wp_attachment_metadata', true);
        $this->assertTrue(!empty($metadata));
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

    protected function getLastAttachmentRow()
    {
        global $wpdb;

        $attachments = $wpdb->get_results(
            "
            SELECT * FROM `{$wpdb->posts}`
            WHERE `post_type` LIKE 'attachment'
            AND `post_status` LIKE 'inherit'
            ORDER BY `ID` DESC
            LIMIT 1
            "
        );

        return $attachments[0];
    }
}
