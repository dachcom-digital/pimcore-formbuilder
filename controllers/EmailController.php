<?php

use Pimcore\Controller\Action\Frontend;

use Pimcore\Model\Document;

class Formbuilder_EmailController extends Frontend {

    public function defaultAction()
    {
        $this->enableLayout();
    }

}