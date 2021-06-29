<?php

namespace FormBuilderBundle\Tool;

use FormBuilderBundle\Tool\ReCaptcha\Response;

interface ReCaptchaProcessorInterface
{
    public function verify(string $value): Response;
}
