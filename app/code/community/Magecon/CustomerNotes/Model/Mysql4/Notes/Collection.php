<?php

/**
 * Open Biz Ltd
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file OPEN-BIZ-LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://mageconsult.net/terms-and-conditions
 *
 * @category   Magecon
 * @package    Magecon_CustomerNotes
 * @version    2.0.0
 * @copyright  Copyright (c) 2012 Open Biz Ltd (http://www.mageconsult.net)
 * @license    http://mageconsult.net/terms-and-conditions
 */
class Magecon_CustomerNotes_Model_Mysql4_Notes_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
    
    public function getSize()
    {
    	$newCol = clone $this;
        if (is_null($this->_totalRecords)) {
            $sql = $this->getSelectCountSql();
            $this->_totalRecords = $this->getConnection()->fetchOne($sql, $this->_bindParams);
        }
        
        $resource = Mage::getSingleton('core/resource');
	     
	    /**
	     * Retrieve the read connection
	     */
	    $readConnection = $resource->getConnection('core_read');
	    
	    $countSelect = clone $this->getSelect();
	    $countSelect->reset(Zend_Db_Select::ORDER);
	    $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
	    $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
	    
		$results = $readConnection->fetchAll($countSelect);
		return count($results);
		
        return intval($this->_totalRecords);
    }
    
    	public function getSelectCountSql()
	{   
	    $this->_renderFilters();
	    $countSelect = clone $this->getSelect();
	    $countSelect->reset(Zend_Db_Select::ORDER);
	    $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
	    $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
	    $countSelect->reset(Zend_Db_Select::COLUMNS);
	
	    // Count doesn't work with group by columns keep the group by 
	    if(count($this->getSelect()->getPart(Zend_Db_Select::GROUP)) > 0) {
	        $countSelect->reset(Zend_Db_Select::GROUP);
	        $countSelect->distinct(true);
	        $group = $this->getSelect()->getPart(Zend_Db_Select::GROUP);
	        $countSelect->columns("COUNT(DISTINCT ".implode(", ", $group).")");
	    } else {
	        $countSelect->columns('COUNT(*)');
	    }
	   // echo $this->getSelect();
	   
	   

	    return $countSelect;
	}
    
    protected function _construct() {
        $this->_init('customernotes/notes');
    }

}