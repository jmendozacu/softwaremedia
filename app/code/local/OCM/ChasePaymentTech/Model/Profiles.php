<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Profiles
 *
 * @author david
 */
class OCM_ChasePaymentTech_Model_Profiles extends Mage_Core_Model_Abstract {

	public function _construct() {
		parent::_construct();
		$this->_init('chasePaymentTech/profiles');
	}
	
	public function getClean() {
		$cc = array (
			'VI' => 'Visa',
			'AE' =>	'American Express',
			'MC' => 'Mastercard',
			'DI' => 'Discover',
			'JCB' => 'JCB'
		);
		
		return $cc[$this->getCardType()] . " (" .$this->getCardNum() . ")";
	}
	
	public function loadByCustomer($customer) {
		$profiles = Mage::getModel('chasePaymentTech/profiles')->getCollection();
		$profiles->addFieldToFilter('customer_id', $customer);
		
		if (count($profiles) == 0)
			return false;
			
		return $profiles;
	}

}
