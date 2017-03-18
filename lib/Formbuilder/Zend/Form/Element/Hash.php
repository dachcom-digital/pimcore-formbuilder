<?php

namespace Formbuilder\Zend\Form\Element;

use \Pimcore\Tool\Session;

class Hash extends \Zend_Form_Element_Hash
{
    /**
     * @var bool
     */
    var $isAjax = FALSE;

    /**
     * Hash constructor.
     *
     * @param array|string|\Zend_Config $spec
     * @param null                      $options
     */
    public function __construct($spec, $options = NULL)
    {
        if (isset($options['ajax']) && $options['ajax'] === TRUE) {
            $this->isAjax = TRUE;
        }

        parent::__construct($spec, $options);
    }

    /**
     * Initialize CSRF token in session
     * @return void
     */
    public function initCsrfToken()
    {
        $session = $this->getSession();

        if (!$this->isAjax) {
            $session->setExpirationHops(1, NULL, TRUE);
            $session->setExpirationSeconds($this->getTimeout());
        }

        $session->hash = $this->getHash();
    }

    /**
     * @return \Zend_Session_Namespace
     */
    public function getSession()
    {
        $this->_session = Session::get($this->getSessionName());
        return $this->_session;
    }
}
