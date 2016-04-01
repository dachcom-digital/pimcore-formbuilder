<?php
/**
 * Twitter Bootstrap v.3 Form for Zend Framework v.1
 * 
 * @category Forms
 * @package Twitter_Bootstrap3_Form
 * @subpackage Decorator
 * @author Ilya Serdyuk <ilya.serdyuk@youini.org>
 */

/**
 * Декоратор ярлыка элемента для горизонтальных форм
 * 
 * @category Forms
 * @package Twitter_Bootstrap3_Form
 * @subpackage Decorator
 */
class Twitter_Bootstrap3_Form_Decorator_HorizontalLabel extends Zend_Form_Decorator_Label
{
    /**
     * Label dimension
     * @var string 
     */
    protected $_dimension = 'sm-2';
    
    /**
     * Get class with which to define label
     *
     * Appends either 'optional' or 'required' to class, depending on whether
     * or not the element is required.
     *
     * @return string
     */
    public function getClass()
    {
        $class = parent::getClass() . ' control-label';
        
        $dimensionLabel = $this->getDimension();
        if (!empty($dimensionLabel)) {
            foreach (explode(',', $dimensionLabel) as $size) {
                $class .= ' col-' . trim($size);
            }
        }
        
        return $class;
    }
    
    /**
     * Get label dimension
     * 
     * @return null|string
     */
    public function getDimension()
    {
        $element = $this->getElement();
        if (null !== ($dimension = $this->getOption('dimension'))) {
            $this->_dimension = $dimension;
            $this->removeOption('dimension');
        } elseif (null !== ($dimension = $element->getAttrib('dimensionLabel'))) {
            $this->_dimension = $dimension;
            $element->setAttrib('dimensionLabel', null);
        }
        
        return $this->_dimension;
    }
}
