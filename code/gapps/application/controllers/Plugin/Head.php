<?php
    class HeadPlugin extends Zend_Controller_Plugin_Abstract {
        public function preDispatch(Zend_Controller_Request_Abstract $request)
        {
            $this->_view = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getResource('view');
            $this->_initHead($request);
        }

        protected function _initHead(Zend_Controller_Request_Abstract $request)
        {
            $headConfig = new Zend_Config_Xml(APPLICATION_PATH.'/configs/xml/head.xml', APPLICATION_ENV);
            $controllerName = $request->getControllerName();
            $actionName = $request->getActionName();
            // Global Stylesheets
            if (isset($headConfig->default->css)) {
                $this->_appendStylesheet($headConfig->default->css);
            }
            // Controller Stylesheets
            if (isset($headConfig->$controllerName->default->css)) {
                $this->_appendStylesheet($headConfig->$controllerName->default->css);
            }
            // Action Stylesheets
            if (isset($headConfig->$controllerName->$actionName->css)) {
                $this->_appendStylesheet($headConfig->$controllerName->$actionName->css);
            }
            // Global JavaScript
            if (isset($headConfig->default->js)) {
                $this->_appendJavaScript($headConfig->default->js);
            }
            // Controller JavaScript
            if (isset($headConfig->$controllerName->default->js)) {
                $this->_appendJavaScript($headConfig->$controllerName->default->js);
            }
            // Action JavaScript
            if (isset($headConfig->$controllerName->$actionName->js)) {
                $this->_appendJavaScript($headConfig->$controllerName->$actionName->js);
            }
        }

        protected function _appendStylesheet($cssConfig) {
            if (is_string($cssConfig)) {
                $this->_appendStylesheetLink($cssConfig);
            } else if ($cssConfig instanceof Zend_Config) {
                foreach ($cssConfig AS $link) {
                    $this->_appendStylesheetLink($link);
                }
            }
        }

        protected function _appendJavaScript($jsConfig) {
            if (is_string($jsConfig)) {
                $this->_appendJavaScriptFile($jsConfig);
            } else if ($jsConfig instanceof Zend_Config) {
                foreach ($jsConfig AS $src) {
                    $this->_appendJavaScriptFile($src);
                }
            }
        }

        protected function _appendStylesheetLink($link) {
            if (substr($link, 0, 7) == 'http://' || substr($link, 0, 8) == 'https://') {
                $this->_view->headLink()->appendStylesheet($link);
            } else {
                $this->_view->headLink()->appendStylesheet($this->_view->baseUrl().'/'.$link);
            }
        }
        
        protected function _appendJavaScriptFile($src) {
            if (substr($src, 0, 7) == 'http://' || substr($src, 0, 8) == 'https://') {
                $this->_view->headScript()->appendFile($src);
            } else {
                $this->_view->headScript()->appendFile($this->_view->baseUrl().'/'.$src);
            }
        }
    }
?>