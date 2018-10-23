<?php

namespace DachcomBundle\Test\Helper;

use Codeception\Lib\ModuleContainer;

class Unit extends \Codeception\Module
{
    /**
     * @inheritDoc
     */
    public function __construct(ModuleContainer $moduleContainer, $config = null)
    {
        parent::__construct($moduleContainer, $config);
    }
}
