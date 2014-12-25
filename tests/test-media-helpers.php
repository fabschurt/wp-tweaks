<?php

use Fabschurt\WpTweaks\Tests\WpTestCase;

class MediaHelpersTest extends WpTestCase
{
    /**
     * @expectedException InvalidArgumentException
     */
    public function testIptcParsingFailsIfFileDoesNotExist()
    {
        _fswpt_get_iptc_tag_from_file($this->getNonExistentFilePath(), 'whatever');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testIptcParsingFailsIfFileIsNotReadable()
    {
        _fswpt_get_iptc_tag_from_file($this->getNonReadableFile()->url(), 'whatever');
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
        $this->assertFalse(_fswpt_get_iptc_tag_from_file("{$this->getAssetsPath()}/image-with-no-iptc-tag.jpg", '105'));
    }

    /**
     * @dataProvider iptcTagsProvider
     */
    public function testIptcTagsCanBeReadFromFile($iptc_tag_id, $iptc_tag_value)
    {
        $this->assertSame(
            _fswpt_get_iptc_tag_from_file("{$this->getAssetsPath()}/image-with-some-iptc-tags.jpg", $iptc_tag_id),
            $iptc_tag_value
        );
    }

    public function testIptcTagArraysCanBeReadFromFile()
    {
        $this->assertSame(
            _fswpt_get_iptc_tag_from_file("{$this->getAssetsPath()}/image-with-some-iptc-tags.jpg", '025', true),
            array('test', 'fake', 'dupe', 'mock', 'stub')
        );
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testAttachmentInsertionFailsIfFileDoesNotExist()
    {
        _fswpt_insert_attachment($this->getNonExistentFilePath());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testAttachmentInsertionFailsIfFileIsNotReadable()
    {
        _fswpt_insert_attachment($this->getNonReadableFile()->url());
    }

    public function testAttachmentInsertionWithDefaultArgumentsReturnsIdOfLastInsertedAttachment()
    {
        $attachment_id   = _fswpt_insert_attachment("{$this->getAssetsPath()}/Some_good_advice.pdf");
        $last_attachment = $this->getLastAttachmentRows();
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
        _fswpt_insert_attachment("{$this->getAssetsPath()}/Some_good_advice.pdf", $parent_id);
        $last_attachment = $this->getLastAttachmentRows();
        $this->assertSame($parent_id, intval($last_attachment->post_parent));
    }

    public function testAttachmentInsertionAcceptsCustomTitle()
    {
        $title = 'Awesome title';
        _fswpt_insert_attachment("{$this->getAssetsPath()}/Some_good_advice.pdf", 0, $title);
        $last_attachment = $this->getLastAttachmentRows();
        $this->assertSame($title, $last_attachment->post_title);
    }

    public function testTitleIsReadFromIptcTagsAndTitlePassedAsArgumentIsIgnoredDuringAttachmentInsertion()
    {
        _fswpt_insert_attachment("{$this->getAssetsPath()}/image-with-some-iptc-tags.jpg", 0, 'Ignored title');
        $last_attachment = $this->getLastAttachmentRows();
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
        _fswpt_insert_attachment("{$this->getAssetsPath()}/image-with-some-iptc-tags.jpg");
        $last_attachment = $this->getLastAttachmentRows();
        $metadata        = get_post_meta($last_attachment->ID, '_wp_attachment_metadata', true);
        $this->assertTrue(!empty($metadata));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testZipInsertionFailsIfFileDoesNotExist()
    {
        _fswpt_insert_attachments_from_zip($this->getWpFilesystem(), $this->getNonExistentFilePath());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testZipInsertionFailsIfFileIsNotReadable()
    {
        _fswpt_insert_attachments_from_zip($this->getWpFilesystem(), $this->getNonReadableFile()->url());
    }

    /**
     * @dataProvider      incompatibleFilePathProvider
     * @expectedException InvalidArgumentException
     */
    public function testZipInsertionFailsIfFileIsNotZipArchive($file_path)
    {
        _fswpt_insert_attachments_from_zip($this->getWpFilesystem(), $file_path);
    }

    public function testZipInsertionWithDefaultArguments()
    {
        define('WP_TEMP_DIR', $this->getMockTempDir()->url());
        _fswpt_insert_attachments_from_zip(
            $this->getWpFilesystem(),
            "{$this->getAssetsPath()}/zip-file.zip"
        );
        $last_attachments = $this->getLastAttachmentRows(3);
        $this->assertSame(
            $this->extractFieldValuesFromPostsArray('post_title', $last_attachments),
            array(
                'text-file',
                'incompatible-image-file',
                'Some_good_advice',
            )
        );

        return $last_attachments;
    }

    /**
     * @depends testZipInsertionWithDefaultArguments
     */
    public function testZipInsertionWithDefaultArgumentsCreatesOrphanAttachments($last_attachments)
    {
        $this->assertSame(
            $this->extractFieldValuesFromPostsArray('post_parent', $last_attachments, 'intval'),
            array(0, 0, 0)
        );
    }

    public function testZipInsertionAcceptsCustomParentId()
    {
        _fswpt_insert_attachments_from_zip(
            $this->getWpFilesystem(),
            "{$this->getAssetsPath()}/zip-file.zip",
            9001
        );
        $this->assertSame(
            $this->extractFieldValuesFromPostsArray('post_parent', $this->getLastAttachmentRows(3), 'intval'),
            array(9001, 9001, 9001)
        );
    }

    public function incompatibleFilePathProvider()
    {
        return array(
            array("{$this->getAssetsPath()}/test-file.txt"),
            array("{$this->getAssetsPath()}/incompatible-image-file.png"),
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
