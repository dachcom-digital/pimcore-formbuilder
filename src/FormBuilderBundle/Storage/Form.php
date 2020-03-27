<?php

namespace FormBuilderBundle\Storage;

/**
 * @deprecated since version 3.3, to be removed in 4.0; use FormBuilderBundle\Model\FormDefinition instead.
 */
class Form implements FormInterface
{
    /**
     * @param mixed $method
     * @param mixed $args
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        if (property_exists($this, 'formDefinition')) {
            if (method_exists($this->formDefinition, $method)) {
                @trigger_error(
                    sprintf('Calling $formData->%s() has been deprecated with FormBuilder 3.3 and will be removed with 4.0, use $formData->getFormDefinition()->%s() instead.', $method, $method),
                    E_USER_DEPRECATED
                );

                return $this->formDefinition->{$method}(...$args);
            }
        }

        throw new \BadMethodCallException('Call to undefined method ' . get_class($this) . '::' . $method . '()');
    }
}
