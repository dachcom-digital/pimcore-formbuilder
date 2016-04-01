<?php
/**
 * Twitter Bootstrap v.3 Form for Zend Framework v.1
 * 
 * @category Forms
 * @package Twitter_Bootstrap3_Form_Decorator
 * @subpackage Feedback
 * @author Ilya Serdyuk <ilya.serdyuk@youini.org>
 */

/**
 * Декоратор значков статуса элемента, помещаемых во внутрь строчных элементов
 * 
 * @category Forms
 * @package Twitter_Bootstrap3_Form_Decorator
 * @subpackage Feedback
 */
class Twitter_Bootstrap3_Form_Decorator_Feedback_State extends Twitter_Bootstrap3_Form_Decorator_Feedback
{
    /**
     *
     * @var bool 
     */
    protected $_renderIcon = true;
    
    /**
     * Иконка по умолчанию для успешно провалидированного элемента
     * @var string
     */
    protected $_successIcon = 'glyphicon glyphicon-ok';
    
    /**
     * Иконка по умолчанию для элемента с предупреждениями
     * @var string
     */
    protected $_warningIcon = 'glyphicon glyphicon-warning-sign';
    
    /**
     * Иконка по умолчанию для элемента с ошибками
     * @var string
     */
    protected $_errorIcon = 'glyphicon glyphicon-remove';
    
    /**
     * 
     * 
     * @param  string $content
     * @return string
     */
    public function render($content)
    {
        $element = $this->getElement();
        $attribs = $this->getOptions();
        $warning = $element->getAttrib('warning');
        
        if (false === $this->isRenderIcon()) {
            return $content;
        } elseif ($element->hasErrors()) {
            $iconClass = $this->getErrorIcon();
        } elseif (!empty($warning)) {
            $iconClass = $this->getWarningIcon();
        } elseif (true === $element->getAttrib('success')) {
            $iconClass = $this->getSuccessIcon();
        } else {
            // No valid information – no valid icon
            return $content;
        }
        
        if (array_key_exists('class', $attribs)) {
            $classes = explode(' ', $attribs['class']);
            if (!in_array($iconClass, $classes)) {
                array_push($classes, $iconClass);
            }
            $this->setOption('class', implode(' ', $classes));
        } else {
            $this->setOption('class', $iconClass);
        }
        
        $this->removeOption('successIcon');
        $this->removeOption('warningIcon');
        $this->removeOption('errorIcon');
        
        return parent::render($content);
    }
    
    /**
     * 
     * @return bool
     */
    public function isRenderIcon()
    {
        if (null !== ($renderIcon = $this->getOption('renderIcon'))) {
            $this->_renderIcon = $renderIcon;
            $this->removeOption('renderIcon');
        }
        
        return $this->_renderIcon;
    }
    
    /**
     * 
     * @return string
     */
    public function getSuccessIcon()
    {
        if (null !== ($icon = $this->getOption('successIcon'))) {
            $this->_successIcon = $icon;
            $this->removeOption('successIcon');
        }
        
        return $this->_successIcon;
    }
    
    /**
     * 
     * @return string
     */
    public function getWarningIcon()
    {
        if (null !== ($icon = $this->getOption('warningIcon'))) {
            $this->_warningIcon = $icon;
            $this->removeOption('warningIcon');
        }
        
        return $this->_warningIcon;
    }
    
    /**
     * 
     * @return string
     */
    public function getErrorIcon()
    {
        if (null !== ($icon = $this->getOption('errorIcon'))) {
            $this->_errorIcon = $icon;
            $this->removeOption('errorIcon');
        }
        
        return $this->_errorIcon;
    }
}
