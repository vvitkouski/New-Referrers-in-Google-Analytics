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
        // View Vars
        $this->view->settingsForm = $settingsForm;
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
        if (is_array($_REQUEST['referrers'])) {
            $reportMapper->insertReferers('ga_nr_tmp_'.$_REQUEST['report_id'], $_REQUEST['referrers']);
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

    public function ajaxgetreportsAction()
    {
        // disable layout
        $this->_helper->layout->disableLayout();
        // table id
        $tableId = $this->getRequest()->getParam('table_id');
        // save session
        $gaReferrerSession = new Zend_Session_Namespace('gaReferrer');
        $gaReferrerSession->currentTableId = $tableId;
        // Get reports
        $reportMapper = new Application_Model_GanalyticsNewreferrerReportMapper();
        $reports = $reportMapper->findByTableId($tableId);
        // View Vars
        $this->view->reports = $reports;
    }

    public function ajaxreportAction()
    {
        // disable layout
        $this->_helper->layout->disableLayout();
        // table id
        $reportId = $this->getRequest()->getParam('report_id');
        // Get report
        $reportMapper = new Application_Model_GanalyticsNewreferrerReportMapper();
        $report = new Application_Model_GanalyticsNewreferrerReport();
        $reportMapper->find($reportId, $report);
        // View Vars
        $this->view->report = $report;
    }

    public function csvreferrersAction()
    {
        // disable layout
        $this->_helper->layout->disableLayout();
        // table id
        $reportId = $this->getRequest()->getParam('report_id');
        // Get report
        $reportMapper = new Application_Model_GanalyticsNewreferrerReportMapper();
        $report = new Application_Model_GanalyticsNewreferrerReport();
        $reportMapper->find($reportId, $report);
        // CSV Content
        $csvArray = Array();
        if (count($report->getReferrers())) {
            $csvArray[] = array('Host', 'Visits');
            foreach ($report->getReferrers() AS $referrer) {
                $csvArray[] = array($referrer['host'],  $referrer['total_visits']);
            }
        }
        if (count($csvArray)) {
            $content = '';
            $filename = 'NewReferrersReport'.$report->getId().'.csv';
            while (list($key1, $val1) = each($csvArray)) {
                while (list($key, $val) = each($val1)) {
                    $content .= $val.',';
                }
                $content = substr($content, 0, -1);
                $content .= "\n";
            }
            // Send content
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Content-Length: ' . strlen($content));
            header('Content-type: text/x-csv');
            header('Content-Disposition: attachment; filename='.$fileName);
            echo $content;
            exit();
        } else { 
            echo 'Incorrect request';
            exit();
        }
    }

}

