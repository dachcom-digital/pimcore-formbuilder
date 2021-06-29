<?php

namespace FormBuilderBundle\Transformer;

use Pimcore\Model\Element\Service;

class HrefTransformer implements OptionsTransformerInterface
{
    /**
     * {@inheritDoc}
     */
    public function transform(array $optionValue, ?array $optionConfig = null): array
    {
        $transformedValues = [];
        foreach ($optionValue as $locale => $value) {
            $transformedValues[$locale] = [
                'id'   => $value['id'] ?? null,
                'type' => $value['type'] ?? null,
            ];
        }

        return $transformedValues;
    }

    /**
     * {@inheritDoc}
     */
    public function reverseTransform(array $optionValue, ?array $optionConfig = null): array
    {
        $values = $optionValue;

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
