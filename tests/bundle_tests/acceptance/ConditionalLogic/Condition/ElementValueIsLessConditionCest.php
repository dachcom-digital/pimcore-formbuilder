<?php

namespace DachcomBundle\Test\acceptance\ConditionalLogic\Condition;

/**
 * Condition "is_less". Must work on:
 */
class ElementValueIsLessConditionCest extends AbstractConditionCest
{
    protected $action = [
        'type'   => 'toggleElement',
        'fields' => ['simple_text_input_1'],
        'state'  => 'hide'
    ];
}
