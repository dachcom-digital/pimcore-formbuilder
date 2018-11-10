<?php

namespace FormBuilderBundle\Form;

interface AdvancedChoiceBuilderInterface extends ChoiceBuilderInterface
{
    /**
     * @param null $element
     *
     * @return callable|string
     */
    public function getChoiceValue($element = null);

    /**
     * @param $element
     * @param $key
     * @param $index
     *
     * @return string|callable|bool
     */
    public function getChoiceLabel($element, $key, $index);

    /**
     * @param $element
     * @param $key
     * @param $index
     *
     * @return array|callable|string
     */
    public function getChoiceAttributes($element, $key, $index);

    /**
     * @param $element
     * @param $key
     * @param $index
     *
     * @return array|callable|string
     */
    public function getGroupBy($element, $key, $index);

    /**
     * @param $element
     * @param $key
     * @param $index
     *
     * @return array|callable|string
     */
    public function getPreferredChoices($element, $key, $index);
}