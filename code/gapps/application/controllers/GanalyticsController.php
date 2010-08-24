<?php

class GanalyticsController extends Zend_Controller_Action
{

    public function init()
    {
        $this->_helper->layout()->setLayout('ganalytics');
    }

    public function indexAction()
    {
        
    }

    public function newreferrersAction()
    {
        // Settings Form
        $settingsForm = new Application_Form_GanalyticsNewreferrersSettings();
        // Get reports
        $reportMapper = new Application_Model_GanalyticsNewreferrerReportMapper();
        $reports = $reportMapper->fetchAll();
        // View Vars
        $this->view->settingsForm = $settingsForm;
        $this->view->reports = $reports;
    }

    public function ajaxnrcreatereportAction()
    {
        // disable layout
        $this->_helper->layout->disableLayout();
        // Create report
        $report = new Application_Model_GanalyticsNewreferrerReport();
        $report->setAccountName($this->getRequest()->getParam('account_name'));
        $report->setProfileName($this->getRequest()->getParam('profile_name'));
        $report->setTableId($this->getRequest()->getParam('table_id'));
        $report->setMinTraffic($this->getRequest()->getParam('min_traffic'));
        $report->setDownloadPeriod($this->getRequest()->getParam('download_period'));
        $report->setComparePeriod($this->getRequest()->getParam('compare_period'));
        $reportMapper = new Application_Model_GanalyticsNewreferrerReportMapper();
        $report_id = $reportMapper->save($report);
        $reportMapper->createTempTable('ga_nr_tmp_'.$report_id);
        // JSON DATA
        $this->view->jsonData = array(
            'error' => '0',
            'report_id' => $report_id
        );
        // set json view
        $this->render('json');
    }

    public function ajaxnrsavetmpreferrersAction()
    {
        // disable layout
        $this->_helper->layout->disableLayout();
        $this->getRequest()->getParam('report_id');
        // insert tpm referrers
        $reportMapper = new Application_Model_GanalyticsNewreferrerReportMapper();
        foreach ($_REQUEST['referrers'] AS $referrer) {
            $reportMapper->insertReferers('ga_nr_tmp_'.$_REQUEST['report_id'], $referrer);
        }
        // JSON DATA
        $this->view->jsonData = array(
            'error' => '0'
        );
        // set json view
        $this->render('json');
    }

    public function ajaxnrprocessreferrersAction()
    {
        // disable layout
        $this->_helper->layout->disableLayout();
        // insert tpm referrers
        
        // remove tmp table
        $reportMapper = new Application_Model_GanalyticsNewreferrerReportMapper();
        $reportMapper->processReferrers($_REQUEST['report_id']);
        $reportMapper->removeTempTable('ga_nr_tmp_'.$_REQUEST['report_id']);
        // JSON DATA
        $this->view->jsonData = array(
            'error' => '0'
        );
        // set json view
        $this->render('json');
    }

}

