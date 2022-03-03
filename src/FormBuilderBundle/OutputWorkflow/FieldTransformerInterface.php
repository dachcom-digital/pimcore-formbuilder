<?php

namespace FormBuilderBundle\OutputWorkflow;

interface FieldTransformerInterface
{
    public function getName(): string;

    public function getDescription(): ?string;

    public function transform(mixed $value, array $context): mixed;
}
