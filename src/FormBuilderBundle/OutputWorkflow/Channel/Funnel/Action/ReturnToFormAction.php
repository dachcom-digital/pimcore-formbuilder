<?php

namespace FormBuilderBundle\OutputWorkflow\Channel\Funnel\Action;

use FormBuilderBundle\Form\Admin\Type\OutputWorkflow\Channel\Funnel\Action\Type\ReturnToFormActionType;

class ReturnToFormAction implements FunnelActionInterface
{
    public function getName(): string
    {
        return 'Return To Form';
    }

    public function getFormType(): string
    {
        return ReturnToFormActionType::class;
    }
}
