<?php

namespace FormBuilderBundle\Transformer;

class ChoicesTransformer implements OptionsTransformerInterface
{
    public function transform(mixed $values, ?array $optionConfig = null): array
    {
        $parsedChoices = [];
        foreach ($values as $choice) {
            //groups
            if (isset($choice[0])) {
                $groupName = $choice[0]['name'];
                foreach ($choice as $index => $choiceGroup) {
                    $parsedChoices[$groupName][$choiceGroup['option']] = $choiceGroup['value'];
                }
            } else {
                $parsedChoices[$choice['option']] = $choice['value'];
            }
        }

        return $parsedChoices;
    }

    public function reverseTransform(mixed $values, ?array $optionConfig = null): array
    {
        $parsedChoices = [];

        $groupCounter = 0;
        foreach ($values as $choiceKey => $choiceValue) {
            //groups
            if (is_array($choiceValue)) {
                $groupName = $choiceKey;
                foreach ($choiceValue as $choiceGroupKey => $choiceGroupValue) {
                    $parsedChoices[$groupCounter][] = [
                        'option' => $choiceGroupKey,
                        'value'  => $choiceGroupValue,
                        'name'   => $groupName
                    ];
                }

                $groupCounter++;
            } else {
                $parsedChoices[] = [
                    'option' => $choiceKey,
                    'value' => $choiceValue
                ];
            }
        }

        return $parsedChoices;
    }
}
