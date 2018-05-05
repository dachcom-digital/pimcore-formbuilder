<?php

namespace FormBuilderBundle\Form;

interface AdvancedChoiceBuilderInterface extends ChoiceBuilderInterface
{
    public function getChoiceValue($element = null);
    
    public function getChoiceLabel($element, $key, $index);

    public function getChoiceAttributes($element, $key, $index);

    public function getGroupBy($element, $key, $index);

    public function getPreferredChoices($element, $key, $index);
}