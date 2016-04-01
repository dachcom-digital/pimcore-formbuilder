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
 * Декоратор контейнеров элементов управления для горизонтальных форм
 * 
 * @category Forms
 * @package Twitter_Bootstrap3_Form
 * @subpackage Decorator
 */
class Twitter_Bootstrap3_Form_Decorator_HorizontalControls extends Zend_Form_Decorator_HtmlTag
{
    /**
     * У элемента нету этикетки?
     * @var bool 
     */
    protected $_noLabel = false;
    
    /**
     * Controls container dimension
     * @var string 
     */
    protected $_dimension = 'sm-10';
    
    /**
     * Label dimension
     * @var string 
     */
    protected $_dimensionLabel= 'sm-2';
    
    /**
     * Обернуть элементы управления в контейнер
     * 
     * @param  string $content
     * @return string
     */
    public function render($content)
    {
        $element = $this->getElement();
        $class = ' ' . $this->getOption('class');
        
        $dimensionControls = $this->getDimension();
        if (!empty($dimensionControls)) {
            foreach (explode(',', $dimensionControls) as $size) {
                $class .= ' col-' . trim($size);
            }
        }
        
        if (true == $this->isNoLabel() || null == $element->getLabel()) {
            $dimensionLabel = $this->getDimensionLabel();
            if (!empty($dimensionLabel)) {
                foreach (explode(',', $dimensionLabel) as $size) {
                    $class .= ' col-' . trim(str_replace('-', '-offset-', $size));
                }
            }
        }
        
        if ('_File' == substr($element->getType(), -5)) {
            $class .= ' form-control-static';
        }
        
        $class = trim($class);
        if (!empty($class)) {
            $this->setOption('class', $class);
        }
        
        return parent::render($content);
    }
    
    /**
     * У элемента нету этикетки?
     * @return bool
     */
    public function isNoLabel()
    {
        if (null !== ($noLabel = $this->getOption('noLabel'))) {
            $this->_noLabel = $noLabel;
            $this->removeOption('noLabel');
        }
        
        return $this->_noLabel;
    }
    
    /**
     * Get controls container dimension
     * 
     * @return null|string
     */
    public function getDimension()
    {
        $element = $this->getElement();
        if (null !== ($dimension = $this->getOption('dimension'))) {
            $this->_dimension = $dimension;
            $this->removeOption('dimension');
        } elseif (null !== ($dimension = $element->getAttrib('dimensionControls'))) {
            $this->_dimension = $dimension;
            $element->setAttrib('dimensionControls', null);
        }
        
        return $this->_dimension;
    }
    
    /**
     * Get label dimension
     * 
     * @return null|string
     */
    public function getDimensionLabel()
    {
        $element = $this->getElement();
        if (null !== ($dimension = $this->getOption('dimensionLabel'))) {
            $this->_dimensionLabel = $dimension;
            $this->removeOption('dimensionLabel');
        } elseif (null !== ($dimension = $element->getAttrib('dimensionLabel'))) {
            $this->_dimensionLabel = $dimension;
            $element->setAttrib('dimensionLabel', null);
        }
        
        return $this->_dimensionLabel;
    }
}
