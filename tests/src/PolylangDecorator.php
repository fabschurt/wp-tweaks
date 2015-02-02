<?php

namespace Fabschurt\WpTweaks\Tests;

class PolylangDecorator
{
    /**
     * @var \Polylang
     */
    private $realSubject;

    /**
     * @param \Polylang $polylang_object
     * @param array     $languages
     */
    public function __construct($polylang_object, array $languages)
    {
        $this->realSubject = $polylang_object;
        $this->insertLanguages($languages);
    }

    /**
     * @param string $language_code
     *
     * @return void
     */
    public function setCurrentLanguage($language_code)
    {
        $this->realSubject->curlang = $this->realSubject->model->get_language($language_code);
    }

    /**
     * Provides delegation to real subject for unknown instance methods.
     *
     * @param string $method_name
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call($method_name, $arguments)
    {
        call_user_func_array(array($this->realSubject, $method_name), $arguments);
    }

    /**
     * Provides delegation to real subject for unknown class methods.
     *
     * @param string $method_name
     * @param array  $arguments
     *
     * @return mixed
     */
    public static function __callStatic($method_name, $arguments)
    {
        call_user_func_array(sprintf('%s::%s', get_class($this->realSubject), $method_name), $arguments);
    }

    /**
     * @param array $languages
     *
     * @return void
     */
    protected function insertLanguages(array $languages)
    {
        foreach ($languages as $language) {
            if ($this->languageIsValid($language)) {
                $this->realSubject->model->add_language($language);
                $this->clearInfoMessages();
            }
        }
    }

    /**
     * @param array $language
     *
     * @return boolean
     */
    protected function languageIsValid(array $language)
    {
        return (
            is_array($language) &&
            isset($language['name']) &&
            isset($language['locale']) &&
            isset($language['slug']) &&
            isset($language['rtl']) &&
            isset($language['term_group'])
        );
    }

    protected function clearInfoMessages()
    {
        global $wp_settings_errors;
        $wp_settings_errors = array_filter($wp_settings_errors, function($element) {
            return !(isset($element['type']) && $element['type'] == 'updated');
        });
    }
}
