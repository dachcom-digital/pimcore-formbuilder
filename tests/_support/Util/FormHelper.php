<?php

namespace DachcomBundle\Test\Util;

use Codeception\Util\Autoload;
use FormBuilderBundle\Configuration\Configuration;
use Symfony\Component\Finder\Finder;

class FormHelper extends Autoload
{
    const AREA_TEST_NAMESPACE = 'dachcomBundleTest';

    public static function removeAllForms()
    {
        $formPath = Configuration::STORE_PATH;

        $finder = new Finder();
        $fileSystem = new \Symfony\Component\Filesystem\Filesystem();

        foreach ($finder->in($formPath)->name('*.yml') as $file) {
            $fileSystem->remove($file);
        }

        try {
            $db = \Pimcore\Db::get();
            $db->exec('TRUNCATE TABLE formbuilder_forms');
        } catch (\Exception $e) {
            \Codeception\Util\Debug::debug(sprintf('[FORMBUILDER ERROR] error while removing forms. message was: ' . $e->getMessage()));
        }
    }

    /**
     * @param string $formName
     *
     * @return array
     */
    public static function generateSimpleForm(string $formName = 'TEST_FORM')
    {
        return [
            'form_name'   => $formName,
            'form_config' => [
                'action'     => '/',
                'method'     => 'POST',
                'enctype'    => 'multipart/form-data',
                'noValidate' => false,
                'useAjax'    => false
            ],

            'form_fields' => [
                'fields' => [
                    0 => [
                        'name'         => 'salutation',
                        'display_name' => 'salutation',
                        'type'         => 'choice',
                        'constraints'  => [
                            0 => [
                                'type' => 'not_blank',
                            ],
                        ],
                        'options'      => [
                            'label'       => 'Salutation',
                            'expanded'    => false,
                            'multiple'    => false,
                            'placeholder' => false,
                            'choices'     => [
                                'Mr.'  => 'mr',
                                'Mrs.' => 'ms',
                            ],
                        ],
                        'optional'     => [
                            'template' => 'col-12',
                        ],
                    ],
                    1 => [
                        'name'         => 'prename',
                        'display_name' => 'prename',
                        'type'         => 'text',
                        'constraints'  => [
                            0 => [
                                'type' => 'not_blank',
                            ],
                        ],
                        'options'      => [
                            'label' => 'Prename',
                        ],
                        'optional'     => [
                            'template' => 'col-sm-6',
                        ],
                    ],
                    2 => [
                        'name'         => 'lastname',
                        'display_name' => 'lastname',
                        'type'         => 'text',
                        'constraints'  => [],
                        'options'      => [
                            'label' => 'Name',
                        ],
                        'optional'     => [
                            'template' => 'col-sm-6',
                        ],
                    ],
                    3 => [
                        'name'         => 'phone',
                        'display_name' => 'phone',
                        'type'         => 'text',
                        'constraints'  => [],
                        'options'      => [
                            'label' => 'Phone',
                        ],
                        'optional'     => [
                            'template' => 'col-sm-6',
                        ],
                    ],
                    4 => [
                        'name'         => 'email',
                        'display_name' => 'email',
                        'type'         => 'text',
                        'constraints'  => [
                            0 => [
                                'type' => 'not_blank',
                            ],
                            1 => [
                                'type' => 'email',
                            ],
                        ],
                        'options'      => [
                            'label' => 'Email',
                        ],
                        'optional'     => [
                            'template' => 'col-sm-6',
                        ],
                    ],
                    5 => [
                        'name'         => 'checkbox',
                        'display_name' => 'checkbox',
                        'type'         => 'choice',
                        'constraints'  => [
                            0 => [
                                'type' => 'not_blank',
                            ],
                        ],
                        'options'      => [
                            'label'       => 'Checkbox',
                            'expanded'    => true,
                            'multiple'    => true,
                            'placeholder' => false,
                            'choices'     => [
                                'Check 1' => 'check1',
                                'Check 2' => 'check2',
                                'Check 3' => 'check3',
                                'Check 4' => 'check4',
                            ],
                        ],
                        'optional'     => [
                            'template' => 'col-sm-6',
                        ],
                    ],
                    6 => [
                        'name'         => 'radios',
                        'display_name' => 'radios',
                        'type'         => 'choice',
                        'constraints'  => [
                            0 => [
                                'type' => 'not_blank',
                            ],
                        ],
                        'options'      =>
                            [
                                'label'       => 'Radios',
                                'expanded'    => true,
                                'multiple'    => false,
                                'placeholder' => false,
                                'choices'     => [
                                    'Radio A' => 'radio_a',
                                    'Radio B' => 'radio_b',
                                    'Radio C' => 'radio_c',
                                    'Radio D' => 'radio_d',
                                ],
                            ],
                        'optional'     => [
                            'template' => 'col-sm-6',
                        ],
                    ],
                    7 => [
                        'name'         => 'comment',
                        'display_name' => 'comment',
                        'type'         => 'textarea',
                        'constraints'  => [
                            0 => [
                                'type' => 'not_blank',
                            ],
                        ],
                        'options'      => [
                            'label' => 'Comment',
                        ],
                        'optional'     => [
                            'template' => 'col-12',
                        ],
                    ],
                    8 => [
                        'name'         => 'send',
                        'display_name' => 'send',
                        'type'         => 'submit',
                        'constraints'  => [],
                        'options'      => [
                            'label' => 'Send',
                        ],
                        'optional'     => [
                            'template' => 'col-sm-6',
                        ],
                    ],
                ]
            ]
        ];
    }
}
