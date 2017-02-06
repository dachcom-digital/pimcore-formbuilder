<?php

namespace Formbuilder\Lib\Form\Frontend\Mapper;

class Captcha extends MapAbstract
{
    /**
     * @param array  $element
     * @param string $formType
     *
     * @return array
     */
    public static function parse($element = [], $formType = '')
    {
        //rearrange reCaptcha (v2) config
        if ($element['options']['captcha'] == 'reCaptcha' && isset($element['options']['captchaOptions'])) {
            $captchaOptions = $element['options']['captchaOptions'];

            $element['type'] = 'recaptcha';
            $element['options'] = [
                'secretKey' => $captchaOptions['secretKey'],
                'siteKey'   => $captchaOptions['siteKey'],
                'classes'   => [$element['options']['class']]
            ];

            unset($element['options']['captchaOptions']);
        }

        return $element;
    }
}