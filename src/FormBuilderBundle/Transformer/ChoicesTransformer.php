<?php

namespace FormBuilderBundle\Transformer;

class ChoicesTransformer implements OptionsTransformerInterface {

    /**
     * Transform ExtJs Array to valid symfony choices array.
     *
     * @param $choices
     *
     * @return array
     */
    public function transform($choices)
    {
        $parsedChoices = [];
        foreach($choices as $choice) {

            //groups
            if(isset($choice[0])) {
                $groupName = $choice[0]['name'];
                foreach($choice as $index => $choiceGroup) {
                    $parsedChoices[$groupName][$choiceGroup['option']] = $choiceGroup['value'];
                }
            } else {
                $parsedChoices[$choice['option']] = $choice['value'];
            }
        }

        return $parsedChoices;
    }

    /**
     * Transform symfony choices array into valid ExtJs Array
     *
     * @param $choices
     *
     * @return array
     */
    public function reverseTransform($choices)
    {
        $parsedChoices = [];
        foreach($choices as $choiceKey => $choiceValue) {

            //groups
            if(is_array($choiceValue)) {
                $groupName = $choiceKey;
                $c = 0;
                foreach($choiceValue as $choiceGroupKey => $choiceGroupValue) {
                    $parsedChoices[$c][] = ['option' => $choiceGroupKey, 'value' => $choiceGroupValue, 'name' => $groupName];
                    $c++;
                }
            } else {
                $parsedChoices[] = ['option' => $choiceKey, 'value' => $choiceValue];
            }
        }

        return $parsedChoices;
    }
}