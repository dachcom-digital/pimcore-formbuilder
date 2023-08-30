<?php

namespace FormBuilderBundle\Form\Type;

use FormBuilderBundle\Configuration\Configuration;
use FormBuilderBundle\Form\Data\FormData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class DynamicFormType extends AbstractType
{
    public const EMPTY_RUNTIME_DATA_KEY = 'no-runtime-data';

    public function __construct(
        protected CsrfTokenManagerInterface $defaultTokenManager,
        protected Configuration $configuration
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $addHoneypot = $this->configuration->getConfigFlag('use_honeypot_field');
        $spamProtectionConfig = $this->configuration->getConfig('spam_protection');
        $honeyPotConfig = $spamProtectionConfig['honeypot'];

        $builder
            ->add('formId', HiddenType::class, [
                'mapped' => false,
                'data'   => $options['current_form_id'],
            ])
            ->add('formCl', HiddenType::class, [
                'mapped' => false,
                'data'   => $options['conditional_logic'] ?? null,
            ]);

        if ($addHoneypot === true) {
            $builder->add($honeyPotConfig['field_name'], HoneypotType::class, ['mapped' => false]);
        }

        $this->addRuntimeData($builder, $options);

        $builder->get('formCl')->addModelTransformer(new CallbackTransformer(
            function ($conditionalLogic) {
                return is_array($conditionalLogic) ? json_encode($conditionalLogic) : null;
            },
            function ($conditionalLogic) {
                return empty($conditionalLogic) ? null : json_decode($conditionalLogic, true);
            }
        ));

        $builder->get('formRuntimeData')->addModelTransformer(new CallbackTransformer(
            function ($runtimeData) {
                return is_array($runtimeData) ? json_encode($runtimeData) : null;
            },
            function ($runtimeData) {
                return empty($runtimeData) ? null : json_decode($runtimeData, true);
            }
        ));
    }

    protected function addRuntimeData(FormBuilderInterface $builder, array $options): void
    {
        $runtimeData = $options['runtime_data'] ?? null;

        if (isset($runtimeData['email'], $runtimeData['email']['_deprecated_note'])) {
            unset($runtimeData['email']['_deprecated_note']);
        }

        $token = is_array($runtimeData) ? md5(json_encode($runtimeData)) : md5(self::EMPTY_RUNTIME_DATA_KEY);

        $builder
            ->add('formRuntimeData', HiddenType::class, [
                'mapped' => false,
                'data'   => $runtimeData,
            ]);

        $builder
            ->add('formRuntimeDataToken', HiddenType::class, [
                'mapped' => false,
                'data'   => (string) $this->defaultTokenManager->getToken($token),
            ]);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $runtimeData = $data['formRuntimeData'] ?? null;
            $tokenValue = $data['formRuntimeDataToken'] ?? null;

            if (empty($runtimeData)) {
                $runtimeData = self::EMPTY_RUNTIME_DATA_KEY;
            }

            $rtCsrfToken = new CsrfToken(md5($runtimeData), $tokenValue);
            if ($tokenValue === null || !$this->defaultTokenManager->isTokenValid($rtCsrfToken)) {
                $event->getForm()->addError(new FormError('Manipulated runtime token detected.', '', [], null, $rtCsrfToken));
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'current_form_id'    => 0,
            'conditional_logic'  => [],
            'runtime_data'       => [],
            'allow_extra_fields' => true,
            'csrf_protection'    => true,
            'data_class'         => FormData::class
        ]);
    }
}
