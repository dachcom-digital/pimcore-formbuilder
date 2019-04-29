<?php

namespace FormBuilderBundle\Registry;

use FormBuilderBundle\MailEditor\Widget\MailEditorFieldDataWidgetInterface;
use FormBuilderBundle\MailEditor\Widget\MailEditorWidgetInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MailEditorWidgetRegistry
{
    /**
     * @var array
     */
    protected $provider = [];

    /**
     * @var OptionsResolver
     */
    protected $widgetOptionsResolver;

    /**
     * @param string                    $identifier
     * @param MailEditorWidgetInterface $service
     */
    public function register($identifier, $service)
    {
        if (!in_array(MailEditorWidgetInterface::class, class_implements($service), true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    '%s needs to implement "%s", "%s" given.',
                    get_class($service),
                    MailEditorWidgetInterface::class,
                    implode(', ', class_implements($service))
                )
            );
        }

        $widgetConfig = $service instanceof MailEditorFieldDataWidgetInterface
            ? $service->getWidgetConfigByField([]) : $service->getWidgetConfig();

        if (count($widgetConfig) > 0) {
            foreach ($widgetConfig as $config) {
                $this->getOptionsResolver()->resolve($config);
            }
        }

        $this->provider[$identifier] = $service;
    }

    /**
     * @param string $identifier
     *
     * @return bool
     */
    public function has($identifier)
    {
        return isset($this->provider[$identifier]);
    }

    /**
     * @param string $identifier
     *
     * @return MailEditorWidgetInterface
     *
     * @throws \Exception
     */
    public function get($identifier)
    {
        if (!$this->has($identifier)) {
            throw new \Exception('"' . $identifier . '" widget service does not exist.');
        }

        return $this->provider[$identifier];
    }

    /**
     * @return array|MailEditorWidgetInterface[]
     */
    public function getAll()
    {
        return $this->provider;
    }

    /**
     * @return array
     */
    public function getAllIdentifier()
    {
        return array_keys($this->provider);
    }

    /**
     * @return OptionsResolver
     */
    protected function getOptionsResolver()
    {
        if ($this->widgetOptionsResolver !== null) {
            return $this->widgetOptionsResolver;
        }

        $optionsResolver = new OptionsResolver();

        $optionsResolver->setRequired(['defaultValue', 'type', 'label']);
        $optionsResolver->setAllowedTypes('label', ['string']);
        $optionsResolver->setAllowedTypes('defaultValue', ['null', 'string', 'bool']);
        $optionsResolver->setAllowedValues('type', ['checkbox', 'input', 'read-only']);

        $this->widgetOptionsResolver = $optionsResolver;

        return $this->widgetOptionsResolver;
    }
}
