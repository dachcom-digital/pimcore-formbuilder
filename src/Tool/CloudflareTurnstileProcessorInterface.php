<?php

namespace FormBuilderBundle\Tool;

use FormBuilderBundle\Tool\CloudflareTurnstile\Response;

interface CloudflareTurnstileProcessorInterface
{
    public function verify(mixed $value): Response;
}
