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
 * Декоратор значков, помещаемых во внутрь строчных элементов
 * 
 * @category Forms
 * @package Twitter_Bootstrap3_Form
 * @subpackage Decorator
 */
class Twitter_Bootstrap3_Form_Decorator_Feedback extends Zend_Form_Decorator_HtmlTag
{
    /**
     * Placement; default to surround content
     * @var string
     */
    protected $_placement = self::APPEND;

    /**
     * HTML tag to use
     * @var string
     */
    protected $_tag = 'span';
    
    /**
     * Render feedback wrapped
     *
     * @param  string $content
     * @return string
     */
    public function render($content)
    {
        $attribs = $this->getOptions();
        
        if (array_key_exists('class', $attribs)) {
            $classes = explode(' ', $attribs['class']);
            if (!in_array('form-control-feedback', $classes)) {
                array_push($classes, 'form-control-feedback');
            }
            $this->setOption('class', implode(' ', $classes));
        } else {
            $this->setOption('class', 'form-control-feedback');
        }
        
        if (!array_key_exists('aria-hidden', $attribs)) {
            $this->setOption('aria-hidden', 'true');
        }
        
        $element = $this->getElement();
        $container = $element->getDecorator('Container');
        if (!empty($container)) {
            $classes = explode(' ', $container->getOption('class'));
            if (!in_array('has-feedback', $classes)) {
                array_push($classes, 'has-feedback');
            }
            $container->setOption('class', implode(' ', $classes));
        }
        
        return parent::render($content);
    }
}
