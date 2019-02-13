<?php

namespace FormBuilderBundle\Tool;

class HrefLocaleMapper
{
    /**
     * @param string $locale
     * @param array  $data
     *
     * @return mixed
     */
    public function map(string $locale, array $data)
    {
        // current locale found
        if (isset($data[$locale]) && !empty($data[$locale]['id'])) {
            return $data[$locale]['id'];
        }

        // search for fallback locale
        $fallbackLanguages = \Pimcore\Tool::getFallbackLanguagesFor($locale);
        foreach ($fallbackLanguages as $fallbackLanguage) {
            if (isset($data[$fallbackLanguage]) && !empty($data[$fallbackLanguage]['id'])) {
                return $data[$fallbackLanguage]['id'];
            }
        }

        // search for default locale
        $defaultLocale = \Pimcore\Tool::getDefaultLanguage();
        if (isset($data[$defaultLocale]) && !empty($data[$defaultLocale]['id'])) {
            return $data[$defaultLocale]['id'];
        }

        //no locale found. use the first one.
        $firstElement = reset($data);

        return $firstElement['id'];
    }
}
