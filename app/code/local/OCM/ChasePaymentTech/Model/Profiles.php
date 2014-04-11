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

	public $ccNames = array(
		'MC' => 'Mastercard',
		'VI' => 'Visa',
		'AE' =>	'American Express',
		'DI' => 'Discover'
	);
	
	public function _construct() {
		parent::_construct();
		$this->_init('chasePaymentTech/profiles');
	}
	
	public function getReadableType() {
		return $this->ccNames[$this->getCardType()];
	}
	
	public function addProfile() {
		$processor = Mage::getModel('chasePaymentTech/PaymentProcessor');
		$request = $this->buildProfileRequest();
		
		$wsdl = Mage::getStoreConfig('payment/chasePaymentTech/url', Mage::app()->getStore());

		try {
			$client = new SoapClient($wsdl, array('trace' => 1));
			$response = $client->profileAdd($request);
			Mage::log($response,NULL,'profiles.log');
		} catch (SoapFault $fault) {
			//echo $fault;
			Mage::getSingleton('adminhtml/session')->addError($fault);
			Mage::log('In Send Request - Threw a SoapFault\n' . $fault,null,'profiles.log');
			return false;
		}
		
		//$response = $processor->_sendRequest('profileAdd',$request);
		
		$TxProcCode = $processor->_getProcStatus($response);
		switch ($TxProcCode) {
				case '0':
					$this->setCustomerReferenceNumber($response->return->customerRefNum);
					$this->setData('card_num',substr($this->getData('card_num'),-4));
					return $response->return->customerRefNum;
					break;
				default:
					return false;
			}
	}
	
	protected function buildProfileRequest() {

		$txRequest = new StdClass();
		$txRequest->profileAddRequest = new StdClass();
		$txRequest->profileAddRequest->orbitalConnectionUsername = Mage::getStoreConfig('payment/chasePaymentTech/username', Mage::app()->getStore());
		$txRequest->profileAddRequest->orbitalConnectionPassword = Mage::getStoreConfig('payment/chasePaymentTech/password', Mage::app()->getStore());
		$txRequest->profileAddRequest->bin = Mage::getStoreConfig('payment/chasePaymentTech/bin', Mage::app()->getStore());
		$txRequest->profileAddRequest->merchantID = Mage::getStoreConfig('payment/chasePaymentTech/merchant_id', Mage::app()->getStore());
		$txRequest->profileAddRequest->customerRefNum = '';

		// What are we doing: CRUD
		$txRequest->profileAddRequest->customerProfileAction = 'C';

		// Where can we get pre-populated data
		// NO: No mapping to order data
		// OI: Use CustomerRefNum for OrderID
		// OD: Use CustomerRefNum for Comments
		// OA: Use CustomerRefNum for OrderID and Comments
		$txRequest->profileAddRequest->customerProfileOrderOverideInd = 'OI';

		// Tell Orbital to autogenerate the profile number
		$txRequest->profileAddRequest->customerProfileFromOrderInd = 'A';

		// Customer's Payment Type to save in the profile
		$txRequest->profileAddRequest->customerAccountType = 'CC';

		// Profile Status Flag
		// A: Active
		// I: Inactive
		// MS: Manual Suspen
		$txRequest->profileAddRequest->status = 'A';

		$txRequest->profileAddRequest->ccAccountNum = $this->getData('card_num');
		$txRequest->profileAddRequest->ccExp = $this->getData('exp_year') . sprintf('%02d', $this->getData('exp_month'));

		return $txRequest;
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
