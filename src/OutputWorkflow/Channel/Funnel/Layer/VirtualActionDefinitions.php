<?php

namespace FormBuilderBundle\OutputWorkflow\Channel\Funnel\Layer;

use FormBuilderBundle\Model\FunnelActionDefinition;

class VirtualActionDefinitions
{
    public static function getVirtualFunnelActionDefinitions(): array
    {
        return [
            new FunnelActionDefinition('virtualFunnelSuccess', 'On Success'),
            new FunnelActionDefinition('virtualFunnelError', 'On Error'),
        ];
    }
}
