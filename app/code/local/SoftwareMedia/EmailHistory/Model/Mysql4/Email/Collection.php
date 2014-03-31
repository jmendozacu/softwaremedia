<?php

class SoftwareMedia_EmailHistory_Model_Mysql4_Email_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
	public function _construct() {
		parent::_construct();
		$this->_init('emailhistory/email');
	}
}
