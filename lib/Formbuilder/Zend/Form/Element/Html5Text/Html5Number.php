<?php

namespace Formbuilder\Zend\Form\Element\Html5Text;

class Html5Number extends \Formbuilder\Zend\Form\Element\Html5Text
{
    /**
     *
     */
    public function init()
    {
        if ($this->isAutoloadFilters()) {
            $this->addFilter('Digits');
        }

        if ($this->isAutoloadValidators()) {
            if ($this->getValidator('Digits') === FALSE) {
                $this->addValidator('Digits');
            }

            $validatorOpts = array_filter([
                'min' => $this->getAttrib('min'),
                'max' => $this->getAttrib('max'),
            ]);

            $validator = NULL;
            if (2 === count($validatorOpts)) {
                $validator = 'Between';
            } else if (isset($validatorOpts['min'])) {
                $validator = 'GreaterThan';
            } else if (isset($validatorOpts['max'])) {
                $validator = 'LessThan';
            }
            if (NULL !== $validator) {
                if ($this->getValidator($validator) === FALSE) {
                    $this->addValidator($validator, FALSE, $validatorOpts);
                }
            }
        }
    }
}
