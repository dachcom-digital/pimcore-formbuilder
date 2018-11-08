<?php

namespace FormBuilderBundle\Form;

interface AdvancedChoiceBuilderInterface extends ChoiceBuilderInterface
{
    /**
     * @param null $element
     *
     * @return mixed
     */
    public function getChoiceValue($element = null);

    /**
     * @param $element
     * @param $key
     * @param $index
     *
     * @return mixed
     */
    public function getChoiceLabel($element, $key, $index);

    /**
     * @param $element
     * @param $key
     * @param $index
     *
     * @return mixed
     */
    public function getChoiceAttributes($element, $key, $index);

    /**
     * @param $element
     * @param $key
     * @param $index
     *
     * @return mixed
     */
    public function getGroupBy($element, $key, $index);

    /**
     * @param $element
     * @param $key
     * @param $index
     *
     * @return mixed
     */
    public function getPreferredChoices($element, $key, $index);
}