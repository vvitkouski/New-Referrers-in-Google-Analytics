<?php

class Application_Model_GanalyticsNewreferrerReport
{
    protected $_accountName;
    protected $_profileName;
    protected $_tableId;
    protected $_minTraffic;
    protected $_downloadPeriod;
    protected $_comparePeriod;
    protected $_createdDate;
    protected $_id;
    protected $_dbTable;
    protected $_referrers;
    protected $_referrersCount;
 
 

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

    public function __construct(array $options = null)
    {
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }
 
    public function __set($name, $value)
    {
        $method = 'set' . $name;
        if (('mapper' == $name) || !method_exists($this, $method)) {
            throw new Exception('Invalid referrer property');
        }
        $this->$method($value);
    }
 
    public function __get($name)
    {
        $method = 'get' . $name;
        if (('mapper' == $name) || !method_exists($this, $method)) {
            throw new Exception('Invalid guestbook property');
        }
        return $this->$method();
    }
 
    public function setOptions(array $options)
    {
        $methods = get_class_methods($this);
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (in_array($method, $methods)) {
                $this->$method($value);
            }
        }
        return $this;
    }
 


   

    public function setAccountName($accountName)
    {
        $this->_accountName = (string) $accountName;
        return $this;
    }
 
    public function getAccountName()
    {
        return $this->_accountName;
    }
 
    public function setProfileName($profileName)
    {
        $this->_profileName = (string) $profileName;
        return $this;
    }
 
    public function getProfileName()
    {
        return $this->_profileName;
    }
 
    public function setCreatedDate($date)
    {
        $this->_createdDate = $date;
        return $this;
    }
 
    public function getCreatedDate()
    {
        return $this->_createdDate;
    }
    
    public function setTableId($id)
    {
        $this->_tableId = $id;
        return $this;
    }
 
    public function getTableId()
    {
        return $this->_tableId;
    }

    public function setMinTraffic($value)
    {
        $this->_minTraffic = (int) $value;
        return $this;
    }
 
    public function getMinTraffic()
    {
        return $this->_minTraffic;
    }
    
    public function setDownloadPeriod($value)
    {
        $this->_downloadPeriod = (int) $value;
        return $this;
    }
 
    public function getDownloadPeriod()
    {
        return $this->_downloadPeriod;
    }

    public function setComparePeriod($value)
    {
        $this->_comparePeriod = (int) $value;
        return $this;
    }
 
    public function getComparePeriod()
    {
        return $this->_comparePeriod;
    }

    public function setId($id)
    {
        $this->_id = (int) $id;
        return $this;
    }
 
    public function getId()
    {
        return $this->_id;
    }
    
    public function getReferrers()
    {
        if (is_null($this->_referrers)) {
            if ($this->_id) {
                $select = $this->getDbTable()->getAdapter()->select();
                $select->from('ga_nr_referrer', array('host', 'CONCAT("<ul class=\'ga-report-list\'><li class=\'ga-report-list-first\'>", GROUP_CONCAT(DISTINCT(page_path) SEPARATOR "</li><li>"), "</li></ul>") AS pages', 'SUM(visits) AS total_visits'))
                        ->where('report_id = ?', $this->_id)
                        ->group('host')
                        ->order('total_visits DESC');
                $stmt = $this->getDbTable()->getAdapter()->query($select);
                $this->_referrers = $stmt->fetchAll(Zend_Db::FETCH_ASSOC);
            } else {
                $this->_referrers = Array();
            }
        }
        return $this->_referrers;
    }

    public function getReferrersCount()
    {
        if (is_null($this->_referrersCount)) {
            if ($this->_id) {
                $select = $this->getDbTable()->getAdapter()->select();
                $select->from('ga_nr_referrer', array('COUNT(DISTINCT host) AS count'))
                        ->where('report_id = ?', $this->_id)
                        ->group('report_id');
                $stmt = $this->getDbTable()->getAdapter()->query($select);
                $this->_referrersCount = $stmt->fetchColumn();
            } else {
                $this->_referrersCount = 0;
            }
        }
        return $this->_referrersCount;
    }
}

