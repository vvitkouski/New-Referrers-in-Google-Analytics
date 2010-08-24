<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        $this->_helper->layout()->setLayout('ganalytics');
    }

    public function indexAction()
    {
        return $this->_helper->redirector('newreferrers', 'ganalytics');
    }


}

