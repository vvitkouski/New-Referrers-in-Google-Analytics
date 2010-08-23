<?php
require_once APPLICATION_PATH."/controllers/Plugin/Head.php";
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    protected $_view;

    public function __construct($application)
    {
        parent::__construct($application);
        $this->bootstrap('view');
        $this->_view = $this->getResource('view');
    }

    protected function _initRequest()
    {
        $this->bootstrap('FrontController');

        $front = $this->getResource('FrontController');
        $request = new Zend_Controller_Request_Http();
        $front->setRequest($request);
        $front->registerPlugin(new HeadPlugin());
    }

    protected function _initMenu()
    {
        $menuConfig = new Zend_Config_Xml(APPLICATION_PATH.'/configs/xml/menu.xml', APPLICATION_ENV);
        // Main menu
        $mainMenu = new Zend_Navigation($menuConfig->mainmenu->item->toArray());
        $this->_view->navigation($mainMenu)->menu()->setUlClass('mainnav');
    }

}

