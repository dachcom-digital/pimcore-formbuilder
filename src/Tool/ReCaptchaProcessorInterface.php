<?php

namespace FormBuilderBundle\Tool;

use FormBuilderBundle\Tool\ReCaptcha\Response;

interface ReCaptchaProcessorInterface
{
    public function verify(mixed $value): Response;
}
