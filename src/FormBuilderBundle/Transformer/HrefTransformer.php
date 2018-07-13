<?php

namespace FormBuilderBundle\Transformer;

use Pimcore\Model\Element\Service;

class SnippetTransformer implements OptionsTransformerInterface
{
    /**
     * Transform snippet data
     *
     * @param $optionValue
     * @param $optionConfig
     *
     * @return mixed
     */
    public function transform($optionValue, $optionConfig = null)
    {
        $transformedValues = [];
        foreach ($optionValue as $locale => $value) {
            $transformedValues[$locale] = [
                'id'   => isset($value['id']) ? $value['id'] : null,
                'type' => isset($value['null']) ? $value['null'] : null,
            ];
        }

        return $transformedValues;
    }

    /**
     * Transform snippet path/id to deeper array
     *
     * @param $optionValue
     * @param $optionConfig
     *
     * @return mixed
     */
    public function reverseTransform($optionValue, $optionConfig = null)
    {
        $values = [];
        // legacy
        if (is_string($optionValue)) {
            $websiteLocales = \Pimcore\Tool::getValidLanguages();
            foreach ($websiteLocales as $locale) {
                // we don't now the type here: since we only had documents at this point: guess it's a document!
                $values[$locale] = ['id' => $optionValue, 'type' => 'document'];
            }
        } else {
            $values = $optionValue;
        }

        $optionValues = [];
        foreach ($values as $locale => $value) {

            $optionValues[$locale] = [];

            $type = $value['type'];
            $id = $value['id'];

            if (is_numeric($id)) {
                $element = Service::getElementById($type, $id);
            } else {
                // legacy
                $element = Service::getElementByPath($type, $id);
            }

            if ($element) {
                $optionValues[$locale] = [
                    'id'      => $element->getId(),
                    'type'    => $type,
                    'subtype' => $element->getType(),
                    'path'    => $element->getFullPath()
                ];
            }
        }

        return $optionValues;

    }
}