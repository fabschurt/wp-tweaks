<?php

use Fabschurt\WpTweaks\Tests\WpTestCase;

class AssetsHelpersTest extends WpTestCase
{
    public function testGetAssetTransformsRelativeSlugIntoAbsoluteUrl()
    {
        $this->setTestTheme();
        $this->clearBowerRootUrlFromGlobals();
        $this->assertSame(
            _fswpt_get_asset('genericons/Genericons.svg'),
            'http://example.org/wp-content/themes/twentyfifteen/genericons/Genericons.svg'
        );
    }

    public function testGetAssetCanReturnAbsoluteUrlWithBowerComponentsPrefix()
    {
        $this->setTestTheme();
        $this->clearBowerRootUrlFromGlobals();
        $GLOBALS['bower_components_root_url'] = 'http://example.org/bower_components';
        $this->assertSame(
            _fswpt_get_asset('@bower/fancybox/fancybox.js'),
            'http://example.org/bower_components/fancybox/fancybox.js'
        );
    }

    public function testGetAssetIgnoresBowerPrefixIfBowerRootUrlIsNotDefined()
    {
        $this->setTestTheme();
        $this->clearBowerRootUrlFromGlobals();
        $this->assertSame(
            _fswpt_get_asset('@bower/fancybox/fancybox.js'),
            'http://example.org/wp-content/themes/twentyfifteen/@bower/fancybox/fancybox.js'
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
            sprintf('http://example.org/wp-content/uploads/%s/%s/image-with-no-iptc-tag-32x32.jpg', date('Y'), date('m'))
        );
    }

    protected function clearBowerRootUrlFromGlobals()
    {
        if (isset($GLOBALS['bower_components_root_url'])) {
            unset($GLOBALS['bower_components_root_url']);
        }
    }
}
