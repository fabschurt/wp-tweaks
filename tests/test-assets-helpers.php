<?php

use Fabschurt\WpTweaks\Tests\WpTestCase;

class AssetsHelpersTest extends WpTestCase
{
    public function testGetAssetTransformsRelativeSlugIntoAbsoluteUrl()
    {
        $this->setTestTheme();
        $this->assertSame(
            _fswpt_get_asset('genericons/Genericons.svg'),
            'http://example.org/wp-content/themes/twentyfifteen/genericons/Genericons.svg'
        );
    }

    public function testGetImageSrcReturnsImageAbsoluteUrlAccordingToFormat()
    {
        add_image_size('test', 32, 32, true);
        $this->deleteAllUploads();
        _fswpt_insert_attachment("{$this->getAssetsPath()}/image-with-no-iptc-tag.jpg");
        $last_attachment = $this->getLastAttachmentRows();
        $this->assertSame(
            _fswpt_get_image_src($last_attachment->ID, 'test'),
            'http://example.org/wp-content/uploads/2014/12/image-with-no-iptc-tag-32x32.jpg'
        );
    }
}
