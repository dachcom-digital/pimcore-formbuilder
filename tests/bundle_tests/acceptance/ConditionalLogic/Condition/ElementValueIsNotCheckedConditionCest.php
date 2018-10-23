<?php

namespace DachcomBundle\Test\acceptance\ConditionalLogic\Condition;

/**
 * Condition "is_not_checked". Must work on:
 */
class ElementValueIsNotCheckedConditionCest extends AbstractConditionCest
{
    protected $action = [
        'type'   => 'toggleElement',
        'fields' => ['simple_text_input_1'],
        'state'  => 'hide'
    ];
}
