<?php

namespace DachcomBundle\Test\acceptance\ConditionalLogic\Condition;

/**
 * Condition "is_greater". Must work on:
 */
class ElementValueIsGreaterConditionCest extends AbstractConditionCest
{
    protected $action = [
        'type'   => 'toggleElement',
        'fields' => ['simple_text_input_1'],
        'state'  => 'hide'
    ];
}
