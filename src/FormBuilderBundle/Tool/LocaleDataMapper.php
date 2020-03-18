<?php

namespace FormBuilderBundle\Tool;

class LocaleDataMapper
{
    /**
     * @param string $locale
     * @param array  $data
     *
     * @return mixed
     */
    public function mapHref(string $locale, array $data)
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

    /**
     * @param string $requestedLocale
     * @param string $identifier
     * @param bool   $isHref
     * @param array  $data
     *
     * @return array
     */
    public function mapMultiDimensional(string $requestedLocale, string $identifier, bool $isHref, array $data)
    {
        $blockGenerator = function ($locale) use ($isHref, $data, $identifier) {
            if ($isHref === true) {
                return isset($data[$locale][$identifier]['id']) && $data[$locale][$identifier]['id'] !== null;
            }

            return isset($data[$locale][$identifier]) && $data[$locale][$identifier] !== null;
        };

        if ($blockGenerator($requestedLocale) === true) {
            return $data[$requestedLocale];
        }

        // search for fallback locale
        $fallbackLanguages = \Pimcore\Tool::getFallbackLanguagesFor($requestedLocale);
        foreach ($fallbackLanguages as $fallbackLanguage) {
            if ($blockGenerator($fallbackLanguage) === true) {
                return $data[$fallbackLanguage];
            }
        }

        // search for default locale
        $defaultLocale = \Pimcore\Tool::getDefaultLanguage();
        if ($blockGenerator($defaultLocale) === true) {
            return $data[$defaultLocale];
        }

        //no locale found. use the first one.
        $firstElement = reset($data);

        return $firstElement;
    }
}
