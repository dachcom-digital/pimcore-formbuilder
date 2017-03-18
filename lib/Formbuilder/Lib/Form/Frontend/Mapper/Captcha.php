<?php

namespace Formbuilder\Lib\Form\Frontend\Mapper;

class Captcha extends MapAbstract
{
    /**
     * @param array  $element
     * @param string $formType
     * @param array  $formInfo
     *
     * @return array
     */
    public static function parse($element = [], $formType = '', $formInfo = [])
    {
        //rearrange reCaptcha (v2) config
        if ($element['options']['captcha'] == 'reCaptcha' && isset($element['options']['captchaOptions'])) {
            $captchaOptions = $element['options']['captchaOptions'];

            $element['type'] = 'recaptcha';
            $element['options'] = [
                'secretKey' => $captchaOptions['secretKey'],
                'siteKey'   => $captchaOptions['siteKey'],
                'classes'   => [$element['options']['class']],
                'order'     => $element['options']['order']
            ];

            unset($element['options']['captchaOptions']);
        }

        return $element;
    }
}