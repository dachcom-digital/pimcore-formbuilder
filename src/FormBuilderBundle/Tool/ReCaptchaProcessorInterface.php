<?php

namespace FormBuilderBundle\Tool;

use FormBuilderBundle\Tool\ReCaptcha\Response;

interface ReCaptchaProcessorInterface
{
    /**
     * @param mixed $value
     *
     * @return Response
     */
    public function verify($value);
}
