<?php

class SoftwareMedia_EmailHistory_Model_Email extends Mage_Core_Model_Abstract {
	/*
	  Status Values:
	  0 => unavalable for purhase
	  1 => available for purchase
	  2 => purchased
	 */

	public function _construct() {
		parent::_construct();
		$this->_init('emailhistory/email');
	}
}