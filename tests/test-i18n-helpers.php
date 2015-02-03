<?php

use Fabschurt\WpTweaks\Tests\WpTestCase;
use Fabschurt\WpTweaks\Tests\PolylangDecorator;

class I18nHelpersTest extends WpTestCase
{
    /**
     * @var PolylangDecorator
     */
    private static $polylang;

    /**
     * @var integer
     */
    private static $enPostId;

    /**
     * @var integer
     */
    private static $frPostId;

    /**
     * @var integer
     */
    private static $enTermId;

    /**
     * @var integer
     */
    private static $frTermId;

    public static function setUpBeforeClass()
    {
        exec('./vendor/bin/wp plugin install polylang');
    }

    public static function tearDownAfterClass()
    {
        exec('./vendor/bin/wp plugin uninstall polylang');
    }

    public function tearDown()
    {
        parent::tearDown();
        if (is_plugin_active($this->getPluginFileRelativePath('polylang'))) {
            deactivate_plugins($this->getPluginFileAbsolutePath('polylang'));
        }
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

    /**
     * @needsRollback false
     */
    public function testFswptGetI18nPostIdReturnsPostIdOfTranslatedPost()
    {
        $this->initPolylang();
        static::$polylang->setCurrentLanguage('en');
        $this->assertSame(_fswpt_get_i18n_post_id(static::$frPostId), static::$enPostId);
    }

    /**
     * @needsRollback false
     */
    public function testFswptGetI18nTermIdReturnsTermIdOfTranslatedTerm()
    {
        $this->initPolylang();
        static::$polylang->setCurrentLanguage('en');
        $this->assertSame(_fswpt_get_i18n_term_id(static::$frTermId), static::$enTermId);
    }

    protected function initPolylang()
    {
        if (!is_plugin_active($this->getPluginFileRelativePath('polylang'))) {
            activate_plugin($this->getPluginFileAbsolutePath('polylang'));
        }

        if (is_null(static::$polylang)) {
            global $polylang;

            $model_stub = $this->getMockBuilder('\PLL_Admin_Model')
                               ->setMethods(array('validate_lang'))
                               ->setConstructorArgs(array(&$polylang->model->options))
                               ->getMock();
            $model_stub->method('validate_lang')->willReturn(true);
            $polylang->model = $model_stub;

            static::$polylang = new PolylangDecorator($polylang, array(
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
            static::insertTestTerms();
            static::insertTestPosts();
        }
    }

    protected function insertTestTerms()
    {
        $en_term_info     = wp_insert_term('awesome', 'post_tag');
        $fr_term_info     = wp_insert_term('génial', 'post_tag');
        static::$enTermId = $en_term_info['term_id'];
        static::$frTermId = $fr_term_info['term_id'];
        pll_set_term_language(static::$enTermId, 'en');
        pll_set_term_language(static::$frTermId, 'fr');
        pll_save_term_translations(array(
            'en' => static::$enTermId,
            'fr' => static::$frTermId,
        ));
    }

    protected function insertTestPosts()
    {
        static::$enPostId = wp_insert_post(array(
            'post_title'   => 'My Awesome Post!',
            'post_content' => 'This is some awesome post. Period.',
            'post_status'  => 'publish',
            'tags_input'   => 'awesome',
        ));
        static::$frPostId = wp_insert_post(array(
            'post_title'   => 'Mon post de fou!',
            'post_content' => 'Ceci est un post de fou. Point.',
            'post_status'  => 'publish',
            'tags_input'   => 'génial',
        ));
        pll_set_post_language(static::$enPostId, 'en');
        pll_set_post_language(static::$frPostId, 'fr');
        pll_save_post_translations(array(
            'en' => static::$enPostId,
            'fr' => static::$frPostId,
        ));
    }
}
