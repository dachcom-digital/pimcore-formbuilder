<?php

namespace FormBuilderBundle\Form;

interface AdvancedChoiceBuilderInterface extends ChoiceBuilderInterface
{
    /**
     * @param mixed $element
     *
     * @return callable|string
     */
    public function getChoiceValue($element = null);

    /**
     * @param mixed  $choiceValue
     * @param string $key
     * @param mixed  $value
     *
     * @return string|callable|bool
     */
    public function getChoiceLabel($choiceValue, $key, $value);

    /**
     * @param mixed  $element
     * @param string $key
     * @param mixed  $value
     *
     * @return array|callable|string
     */
    public function getChoiceAttributes($element, $key, $value);

    /**
     * @param mixed  $element
     * @param string $key
     * @param mixed  $value
     *
     * @return array|callable|string
     */
    public function getGroupBy($element, $key, $value);

    /**
     * @param mixed  $element
     * @param string $key
     * @param mixed  $value
     *
     * @return array|callable|string
     */
    public function getPreferredChoices($element, $key, $value);
}