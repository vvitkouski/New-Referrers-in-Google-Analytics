<?php

class Application_Model_GanalyticsNewreferrerReportMapper
{
    protected $_dbTable;
 
    public function setDbTable($dbTable)
    {
        if (is_string($dbTable)) {
            $dbTable = new $dbTable();
        }
        if (!$dbTable instanceof Zend_Db_Table_Abstract) {
            throw new Exception('Invalid table data gateway provided');
        }
        $this->_dbTable = $dbTable;
        return $this;
    }
 
    public function getDbTable()
    {
        if (null === $this->_dbTable) {
            $this->setDbTable('Application_Model_DbTable_GanalyticsNewreferrerReport');
        }
        return $this->_dbTable;
    }
 
    public function save(Application_Model_GanalyticsNewreferrerReport $newreferrer)
    {
        $data = array(
            'account_name'   => $newreferrer->getAccountName(),
            'profile_name'   => $newreferrer->getProfileName(),
            'table_id'       => $newreferrer->getTableId(),
            'min_traffic'    => $newreferrer->getMinTraffic(),
            'download_period'=> $newreferrer->getDownloadPeriod(),
            'compare_period' => $newreferrer->getComparePeriod(),
            'created_date' => date('Y-m-d H:i:s'),
        );
 
        if (null === ($id = $newreferrer->getId())) {
            unset($data['id']);
            $this->getDbTable()->insert($data);
            return $this->getDbTable()->getAdapter()->lastInsertId();
        } else {
            $this->getDbTable()->update($data, array('id = ?' => $id));
            return $id;
        }
    }
 
    public function find($id, Application_Model_GanalyticsNewreferrerReport $newreferrer)
    {
        $result = $this->getDbTable()->find($id);
        if (0 == count($result)) {
            return;
        }
        $row = $result->current();
        $newreferrer->setId($row->id)
                    ->setAccountName($row->account_name)
                    ->setProfileName($row->profile_name)
                    ->setTableId($row->table_id)
                    ->setMinTraffic($row->min_traffic)
                    ->setDownloadPeriod($row->download_period)
                    ->setComparePeriod($row->compare_period)
                    ->setCreatedDate($row->created_date);
    }
 
    public function fetchAll()
    {
        $resultSet = $this->getDbTable()->fetchAll();
        $entries   = array();
        foreach ($resultSet as $row) {
            $entry = new Application_Model_GanalyticsNewreferrerReport();
            $entry->setId($row->id)
                    ->setAccountName($row->account_name)
                    ->setProfileName($row->profile_name)
                    ->setTableId($row->table_id)
                    ->setMinTraffic($row->min_traffic)
                    ->setDownloadPeriod($row->download_period)
                    ->setComparePeriod($row->compare_period)
                    ->setCreatedDate($row->created_date);
            $entries[] = $entry;
        }
        return $entries;
    }

    public function createTempTable($tableName)
    {
        $this->getDbTable()->getAdapter()->query("
                CREATE TABLE `{$tableName}` (
                    `id`  int(10) unsigned NOT NULL auto_increment,
                    `report_id` int(10) unsigned NOT NULL,
                    `host` varchar(255) NOT NULL default '',
                    `visits` int(10) unsigned NOT NULL,
                    PRIMARY KEY  (`id`)
                )  ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Google Analytics New Referrers Tmp Table';
        ");
    }

    public function removeTempTable($tableName) {
        $this->getDbTable()->getAdapter()->query("
                DROP TABLE `{$tableName}`;
        ");
    }

    public function insertReferers($tableName, $refererrs)
    {
        $this->getDbTable()->getAdapter()->insert($tableName, $refererrs);
    }

    public function processReferrers($reportId)
    {
        $report = new Application_Model_GanalyticsNewreferrerReport();
        $this->find($reportId, $report);
        $minCreatedDate = date("Y-m-d H\\\:i\\\:s", time() - $report->getComparePeriod() * 86400);
        
        $sql = "
            INSERT INTO `ga_nr_referrer` (report_id, host, visits) SELECT report_id, host, visits FROM `ga_nr_tmp_".$reportId."` WHERE host NOT IN (SELECT `host` FROM `ga_nr_referrer` WHERE `report_id` NOT IN (SELECT id FROM ga_nr_report WHERE created_date < ?));
        ";
        $this->getDbTable()->getAdapter()->query($sql, array($minCreatedDate));
    }

    public function getReferrers($reportId)
    {
        $select = $this->getDbTable()->getAdapter()->select();
        $select->from('ga_nr_referrer')->where('where = ?', $reportId);
        $stmt = $db->query($select);
        $result = $stmt->fetchAll();
        return $result;
    }

}

