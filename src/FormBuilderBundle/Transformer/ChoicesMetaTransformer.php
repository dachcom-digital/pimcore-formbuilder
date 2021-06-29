<?php

namespace FormBuilderBundle\Transformer;

use Pimcore\Model\Element\ElementInterface;
use Pimcore\Model\Element\Service;

class ChoicesMetaTransformer implements DynamicOptionsTransformerInterface
{
    public function transform($rawData, $transformedData, ?array $optionConfig = null): array
    {
        $parsedChoices = [];
        foreach ($rawData as $choice) {
            //groups
            if ($this->isAssocArray($choice) === false) {
                foreach ($choice as $groupIndex => $subChoice) {
                    if (null !== $choiceAttributes = $this->parseMetaToChoiceAttr($subChoice)) {
                        $parsedChoices[$subChoice['option']] = $choiceAttributes;
                    }
                }
            } else {
                if (null !== $choiceAttributes = $this->parseMetaToChoiceAttr($choice)) {
                    $parsedChoices[$choice['option']] = $choiceAttributes;
                }
            }
        }

        return $parsedChoices;
    }

    public function reverseTransform($rawData, $transformedData, ?array $optionConfig = null): array
    {
        $parsedChoices = [];

        foreach ($transformedData as &$choiceData) {
            //groups
            if ($this->isAssocArray($choiceData) === false) {
                foreach ($choiceData as $groupIndex => &$subChoice) {
                    if (null !== $choiceAttributes = $this->parseChoiceAttrToMeta($subChoice['option'], $rawData)) {
                        $subChoice['choice_meta'] = $choiceAttributes;
                    }
                }
            } else {
                if (null !== $choiceAttributes = $this->parseChoiceAttrToMeta($choiceData['option'], $rawData)) {
                    $choiceData['choice_meta'] = $choiceAttributes;
                }
            }

            $parsedChoices[] = $choiceData;
        }

        return $parsedChoices;
    }

    protected function parseMetaToChoiceAttr(array $data): ?array
    {
        if (!isset($data['choice_meta'])) {
            return null;
        }

        if (empty($data['choice_meta'])) {
            return null;
        }

        $choiceMeta = json_decode($data['choice_meta'], true);

        $attr = [];

        // remove null values
        $choiceMeta = array_filter($choiceMeta);

        // tooltip
        if (isset($choiceMeta['tooltip'])) {
            $attr['data-meta-tooltip'] = $choiceMeta['tooltip'];
            unset($choiceMeta['tooltip']);
        }

        // relation
        if (count($choiceMeta) > 0) {
            foreach ($choiceMeta as $relationKey => $relationData) {
                $relationInfo = explode('.', $relationKey);
                $relationLocale = $relationInfo[1];
                $attr[sprintf('data-meta-relation-%s-id', $relationLocale)] = $relationData['id'];
                $attr[sprintf('data-meta-relation-%s-type', $relationLocale)] = $relationData['type'];
                $attr[sprintf('data-meta-relation-%s-locale', $relationLocale)] = $relationLocale;
            }
        }

        if (count($attr) === 0) {
            return null;
        }

        return $attr;
    }

    protected function parseChoiceAttrToMeta(string $key, array $rawData): ?string
    {
        if (!isset($rawData[$key])) {
            return null;
        }

        $choiceData = $rawData[$key];

        if (!is_array($choiceData)) {
            return null;
        }

        $attr = [];
        foreach ($choiceData as $choiceDataKey => $choiceDataValue) {

            // tooltip
            if ($choiceDataKey === 'data-meta-tooltip') {
                $attr['tooltip'] = $choiceDataValue;
            }

            // relation
            if (strpos($choiceDataKey, '-locale') !== false) {
                if (null !== $relationData = $this->extractRelation($choiceDataValue, $choiceData)) {
                    $attr[sprintf('relation.%s', $choiceDataValue)] = $relationData;
                }
            }
        }

        if (count($attr) === 0) {
            return null;
        }

        return json_encode($attr);
    }

    protected function extractRelation(string $locale, array $data): ?array
    {
        $idKey = sprintf('data-meta-relation-%s-id', $locale);
        $typeKey = sprintf('data-meta-relation-%s-type', $locale);

        $path = null;

        try {
            $element = Service::getElementById($data[$typeKey], $data[$idKey]);
        } catch (\Throwable $e) {
            return null;
        }

        if (!$element instanceof ElementInterface) {
            return null;
        }

        return [
            'id'      => $element->getId(),
            'path'    => $element->getFullPath(),
            'type'    => $data[$typeKey],
            'subtype' => $element->getType(),
        ];
    }

    function isAssocArray(array $arr): bool
    {
        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}
