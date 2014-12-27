<?php

use Fabschurt\WpTweaks\Tests\WpTestCase;
use Fabschurt\WpTweaks\Tests\PolylangProxy;
use Symfony\Component\Process\Process;

class I18nHelpersTest extends WpTestCase
{
    /**
     * @var PolylangProxy
     */
    private $polylang;

    /**
     * @var integer
     */
    private $enPostId;

    /**
     * @var integer
     */
    private $frPostId;

    /**
     * @var integer
     */
    private $enTermId;

    /**
     * @var integer
     */
    private $frTermId;

    public function tearDown()
    {
        deactivate_plugins($this->getPluginFileAbsolutePath('polylang'));
    }

    public function testFswptGetI18nPostIdReturnsArgumentIfPolylangIsNotActivated()
    {
        $id = 9001;
        $this->assertSame(_fswpt_get_i18n_post_id($id), $id);
    }

    public function testFswptGetI18nTermIdReturnsArgumentIfPolylangIsNotActivated()
    {
        $id = 9001;
        $this->assertSame(_fswpt_get_i18n_post_id($id), $id);
    }

    public function testFswptGetI18nPostIdReturnsPostIdOfTranslatedPost()
    {
        $this->initPolylang();
        $this->polylang->setCurrentLanguage('en');
        $this->assertSame(_fswpt_get_i18n_post_id($this->frPostId), $this->enPostId);
    }

    public function testFswptGetI18nTermIdReturnsTermIdOfTranslatedTerm()
    {
        $this->initPolylang();
        $this->polylang->setCurrentLanguage('en');
        $this->assertSame(_fswpt_get_i18n_term_id($this->frTermId), $this->enTermId);
    }

    /**
     * @return void
     */
    protected function initPolylang()
    {
        activate_plugin($this->getPluginFileAbsolutePath('polylang'));
        global $polylang;
        $this->polylang = new PolylangProxy($polylang, array(
            array(
                'name'       => 'English',
                'locale'     => 'en_US',
                'slug'       => 'en',
                'rtl'        => 0,
                'term_group' => '',
            ),
            array(
                'name'       => 'French',
                'locale'     => 'fr_FR',
                'slug'       => 'fr',
                'rtl'        => 0,
                'term_group' => '',
            ),
        ));

        $this->insertTestTerms();
        $this->insertTestPosts();
    }

    /**
     * @return void
     */
    protected function insertTestTerms()
    {
        $en_term_info   = wp_insert_term('awesome', 'post_tag');
        $fr_term_info   = wp_insert_term('génial', 'post_tag');
        $this->enTermId = $en_term_info['term_id'];
        $this->frTermId = $fr_term_info['term_id'];
        pll_set_term_language($this->enTermId, 'en');
        pll_set_term_language($this->frTermId, 'fr');
        pll_save_term_translations(array(
            'en' => $this->enTermId,
            'fr' => $this->frTermId,
        ));
    }

    /**
     * @return void
     */
    protected function insertTestPosts()
    {
        $this->enPostId = wp_insert_post(array(
            'post_title'   => 'My Awesome Post!',
            'post_content' => 'This is some awesome post. Period.',
            'post_status'  => 'publish',
            'tags_input'   => 'awesome',
        ));
        $this->frPostId = wp_insert_post(array(
            'post_title'   => 'Mon post de fou!',
            'post_content' => 'Ceci est un post de fou. Point.',
            'post_status'  => 'publish',
            'tags_input'   => 'génial',
        ));
        pll_set_post_language($this->enPostId, 'en');
        pll_set_post_language($this->frPostId, 'fr');
        pll_save_post_translations(array(
            'en' => $this->enPostId,
            'fr' => $this->frPostId,
        ));
    }
}
