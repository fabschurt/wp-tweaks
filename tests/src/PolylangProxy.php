<?php

namespace Fabschurt\WpTweaks\Tests;

class PolylangProxy
{
    /**
     * @var object
     */
    private $internalInstance;

    /**
     * @param object $polylang_object
     * @param array  $languages
     */
    public function __construct($polylang_object, array $languages)
    {
        $this->internalInstance = $polylang_object;
        $this->insertLanguages($languages);
    }

    /**
     * @param string $language_code
     *
     * @return void
     */
    public function setCurrentLanguage($language_code)
    {
        $this->internalInstance->curlang = $this->internalInstance->model->get_language($language_code);
    }

    /**
     * @param array $languages
     *
     * @return void
     */
    protected function insertLanguages(array $languages)
    {
        foreach ($languages as $language) {
            if (
                !is_array($language) ||
                !isset($language['name']) ||
                !isset($language['locale']) ||
                !isset($language['slug']) ||
                !isset($language['rtl']) ||
                !isset($language['term_group'])
            ) {
                continue;
            }
            $this->internalInstance->model->add_language($language);
            $this->emptySettingsErrors();
        }
    }

    /**
     * @return void
     */
    protected function emptySettingsErrors()
    {
        global $wp_settings_errors;
        $wp_settings_errors = array();
    }
}
