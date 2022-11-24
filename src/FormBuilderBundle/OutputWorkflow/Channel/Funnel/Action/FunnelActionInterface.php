<?php

namespace FormBuilderBundle\OutputWorkflow\Channel\Funnel\Action;

interface FunnelActionInterface
{
    public function getName(): string;

    public function getFormType(): string;
}
