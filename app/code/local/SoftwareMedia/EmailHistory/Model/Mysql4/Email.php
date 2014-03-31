<?php

class SoftwareMedia_EmailHistory_Model_Mysql4_Email extends Mage_Core_Model_Mysql4_Abstract {

	public function _construct()
    {    
        $this->_init('emailhistory/softwaremedia_emailhistory', 'id');
    }
    
}