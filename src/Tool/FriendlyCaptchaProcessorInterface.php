<?php

namespace FormBuilderBundle\Tool;

use FormBuilderBundle\Tool\FriendlyCaptcha\Response;

interface FriendlyCaptchaProcessorInterface
{
    public function verify(mixed $value): Response;
}
