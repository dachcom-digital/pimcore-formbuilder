<?php

namespace Formbuilder\Zend\Form\Element\Html5Text;

class Html5Number extends \Formbuilder\Zend\Form\Element\Html5Text
{
    public function init()
    {
        if ($this->isAutoloadFilters())
        {
            $this->addFilter('Digits');
        }

        if ($this->isAutoloadValidators())
        {
            $this->addValidator('Digits');
            $validatorOpts = array_filter(array(
                'min' => $this->getAttrib('min'),
                'max' => $this->getAttrib('max'),
            ));
            $validator = null;
            if (2 === count($validatorOpts))
            {
                $validator = 'Between';
            }
            else if (isset($validatorOpts['min']))
            {
                $validator = 'GreaterThan';
            }
            else if (isset($validatorOpts['max']))
            {
                $validator = 'LessThan';
            }
            if (null !== $validator)
            {
                $this->addValidator($validator, false, $validatorOpts);
            }
        }
    }
}
