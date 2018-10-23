<?php

namespace DachcomBundle\Test\acceptance\ConditionalLogic\Condition;

/**
 * Condition "is_value". Must work on:
 */
class ElementValueIsValueConditionCest extends AbstractConditionCest
{
    protected $action = [
        'type'   => 'toggleElement',
        'fields' => ['simple_text_input_1'],
        'state'  => 'hide'
    ];
}
