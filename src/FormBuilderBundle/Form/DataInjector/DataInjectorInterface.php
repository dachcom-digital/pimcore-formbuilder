<?php

namespace FormBuilderBundle\Form\DataInjector;

interface DataInjectorInterface
{
    public function getName(): string;

    public function getDescription(): ?string;

    public function parseData(array $config): mixed;
}
