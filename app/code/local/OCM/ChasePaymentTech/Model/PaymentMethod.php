<?php

class OCM_ChasePaymentTech_Model_PaymentMethod extends Mage_Payment_Model_Method_Abstract {

	protected $_formBlockType = 'payment/form_cc';
	protected $_infoBlockType = 'payment/info_cc';
	protected $_parentcode = 'chasePaymentTech';
	protected $_code = 'chasePaymentTech';
	protected $_isGateway = true;
	protected $_canAuthorize = true;
	protected $_canCapture = true;
	protected $_canCapturePartial = true;
	protected $_customerCode = false;
	protected $_canRefund = true;
	protected $_canRefundInvoicePartial = true;
	protected $_canVoid = true;
	protected $_canUseInternal = true;
	protected $_canUseCheckout = true;
	protected $_canUseForMultishipping = true;
	protected $_canSaveCc = false;

	const AUTHORIZE = 'Authorize';
	const CAPTURE = 'Capture';
	const SALE = 'Sale';
	const VOID = 'Void';
	const REFUND = 'Refund';
	const PROFILE = 'ProfileAdd';

	protected $_paymentProcessor;

	public function __construct() {
		$this->_paymentProcessor = Mage::getModel('chasePaymentTech/paymentProcessor');
	}

	public function authorize(Varien_Object $payment, $amount) {
		//Get admin Payment of frontend Payment
		if (Mage::app()->getRequest()->getControllerName() == 'sales_order_create') 
			$onePage = Mage::getSingleton('adminhtml/sales_order_create')->getQuote()->getPayment();
		else
			$onePage = Mage::getSingleton('checkout/type_onepage')->getQuote()->getPayment();
			
			
		$helper = Mage::helper('chasePaymentTech');
		$customer = $payment->getOrder()->getCustomer();
		
		//die(var_dump($this->getAllProfiles($payment->getOrder()->getCustomer())));
		$profiles_enabled = Mage::getStoreConfig('payment/chasePaymentTech/use_profiles', Mage::app()->getStore());
		$customer_profile = null;
		
		Mage::log("Profiles enabled and customer" . $onePage['save_cc'] . "enabled: " . $profiles_enabled . ' customer: ' . $hasCustomer,NULL,'profile.log');

		if ($profiles_enabled && $customer && $onePage['save_cc']) {
			$cardNum = substr($payment->getCcNumber(),-4);
			Mage::log("Profiles enabled and customer" ,NULL,'profile.log');
			//If profile doesn't exist for card and customer, create it
			$hasProfile = $helper->hasProfile($customer->getId(),$cardNum);
			Mage::log("Has Profile: " . $helper->hasProfile($customer->getId(),$cardNum),NULL,'profile.log');
			if (!$hasProfile) {		
				Mage::log("In Profile",NULL,'profile.log');
				$this->_paymentProcessor->buildProfileAddRequest($payment);
				$profile = $this->_paymentProcessor->sendRequest(self::PROFILE);
				Mage::log($profile,NULL,'profile.log');
				
				// Verify that we were able to create a customer profile
				if (isset($profile['CustomerRefNum'])) {
					Mage::log("Saving Profile",NULL,'profile.log');
					$customer_profile = $profile['CustomerRefNum'];
					$profile = Mage::getModel('chasePaymentTech/profiles');
					$profile->setCustomerId($customer->getId());
					$profile->setCustomerReferenceNumber($customer_profile);
					$profile->setCardType($payment->getCcType());
					$profile->setExpMonth($payment->getCcExpMonth());
					$profile->setExpYear($payment->getCcExpYear());
					$profile->setCardNum($cardNum);
					$profile->setActive(0);
					$profile->save();

					Mage::log(get_class($profile),NULL,'profile.log');
				}
			} else {
					Mage::log("Profile Exists",NULL,'profile.log');
			}
		}

		$refNum = null;
		
		//Get profile information
		if ($onePage['cc_saved']) {
			$profile = Mage::getModel('chasePaymentTech/profiles')->load($onePage['cc_saved']);
			$refNum = $profile->getCustomerReferenceNumber();
		}

		$this->_paymentProcessor->buildRequest($payment, $amount, $refNum);
		$captureTxResponse = $this->_paymentProcessor->sendRequest(self::AUTHORIZE);

		$this->_processResponse($payment, $captureTxResponse, 0, 0);
				return $this;
	}

	public function capture(Varien_Object $payment, $amount) {
	
		//Get admin Payment of frontend Payment
		if (Mage::app()->getRequest()->getControllerName() == 'sales_order_create') 
			$onePage = Mage::getSingleton('adminhtml/sales_order_create')->getQuote()->getPayment();
		else
			$onePage = Mage::getSingleton('checkout/type_onepage')->getQuote()->getPayment();
			
			
		$helper = Mage::helper('chasePaymentTech');
		$customer = $payment->getOrder()->getCustomer();
		
		//die(var_dump($this->getAllProfiles($payment->getOrder()->getCustomer())));
		$profiles_enabled = Mage::getStoreConfig('payment/chasePaymentTech/use_profiles', Mage::app()->getStore());
		$customer_profile = null;
		
		Mage::log("Profiles enabled and customer" . $onePage['save_cc'] . "enabled: " . $profiles_enabled . ' customer: ' . $hasCustomer,NULL,'profile.log');

		if ($profiles_enabled && $customer && $onePage['save_cc']) {
			$cardNum = substr($payment->getCcNumber(),-4);
			Mage::log("Profiles enabled and customer" ,NULL,'profile.log');
			//If profile doesn't exist for card and customer, create it
			$hasProfile = $helper->hasProfile($customer->getId(),$cardNum);
			Mage::log("Has Profile: " . $helper->hasProfile($customer->getId(),$cardNum),NULL,'profile.log');
			if (!$hasProfile) {		
				Mage::log("In Profile",NULL,'profile.log');
				$this->_paymentProcessor->buildProfileAddRequest($payment);
				$profile = $this->_paymentProcessor->sendRequest(self::PROFILE);
				Mage::log($profile,NULL,'profile.log');
				
				// Verify that we were able to create a customer profile
				if (isset($profile['CustomerRefNum'])) {
					Mage::log("Saving Profile",NULL,'profile.log');
					$customer_profile = $profile['CustomerRefNum'];
					$profile = Mage::getModel('chasePaymentTech/profiles');
					$profile->setCustomerId($customer->getId());
					$profile->setCustomerReferenceNumber($customer_profile);
					$profile->setCardType($payment->getCcType());
					$profile->setExpMonth($payment->getCcExpMonth());
					$profile->setExpYear($payment->getCcExpYear());
					$profile->setCardNum($cardNum);
					$profile->setActive(0);
					$profile->save();

					Mage::log(get_class($profile),NULL,'profile.log');
				}
			} else {
					Mage::log("Profile Exists",NULL,'profile.log');
			}
		}

		$refNum = null;
		
		//Get profile information
		if ($onePage['cc_saved']) {
			$profile = Mage::getModel('chasePaymentTech/profiles')->load($onePage['cc_saved']);
			$refNum = $profile->getCustomerReferenceNumber();
		}

		if ($payment->getParentTransactionId()) {
			$this->_paymentProcessor->buildCaptureRequest($payment, $amount);
			$captureTxResponse = $this->_paymentProcessor->sendRequest(self::CAPTURE);
		} else {
			$this->_paymentProcessor->buildRequest($payment, $amount, $refNum);
			$captureTxResponse = $this->_paymentProcessor->sendRequest(self::SALE);
		}

		$this->_processResponse($payment, $captureTxResponse, 1, 1);
	}

	public function void(Varien_Object $payment) {
		$this->_paymentProcessor->buildReverseRequest($payment, 0);
		$voidTxResponse = $this->_paymentProcessor->sendRequest(self::VOID);
		$this->_processResponse($payment, $voidTxResponse, 1, 1);

		return $this;
	}

	public function refund(Varien_Object $payment, $amount) {
		$this->_paymentProcessor->buildRequest($payment, $amount);
		$voidTxResponse = $this->_paymentProcessor->sendRequest(self::REFUND);
		$this->_processResponse($payment, $voidTxResponse, 1, 1);

		return $this;
	}

	public function getAllProfiles(Varien_Object $customer) {
		$collection = Mage::getModel('chasePaymentTech/profiles')->getCollection()->addFieldToFilter('customer_id',$customer->getId());
		return $collection;
	}

	private function _processResponse($payment, $txResponse, $txClose, $txParentClose) {

		switch ($txResponse["Response"]) {
			case "Approved":
				$payment->setTransactionId($txResponse["TransactionId"]);
				$payment->setIsTransactionClosed($txClose);
				$payment->setShouldCloseParentTransaction($txParentClose);
				break;
			case "Declined":
				Mage::throwException(Mage::helper('paygate')->__('The credit card was declined.'));
				break;
			case "Error":
				$code = $txResponse["ErrorCode"];
				$arr_groups = array(
					'Call' => array("38", "58", "A4", "L7", "L8", "L9"),
					'Customer' => array("04", "05", "06", "07", "09", "12", "20", "21", "22", "23", "33", "41", "42", "43", "44", "45", "50", "52", "56", "59", "60", "61", "62", "63", "64", "65", "74", "89", "B2", "B7", "B8", "B9",
						"BA", "BB", "BC", "BD", "BE", "BF", "BG", "BH", "BI", "BJ", "BK", "BL", "BM", "BN", "BO", "BQ", "BS", "C1", "C3", "C4", "C5", "C6", "C7", "C9", "D3", "D4", "D5", "D7", "F3",
						"F5", "F6", "F7", "F8", "F9", "G4", "G5", "H3", "H9", "I3", "I4", "I5", "J3", "J6", "J7", "J8", "J9", "K1", "K2", "K5", "K6", "L6", "M1", "M2", "ND", "PB", "PC", "PD", "R1", "R2", "R3", "R4"),
					'Fix' => array("03", "13", "14", "30", "35", "36", "37", "39", "40", "46", "66", "68", "69", "71", "72", "73", "75", "77", "78", "79", "80", "85", "87", "88", "95", "96", "97", "A1", "A2", "A5", "A6", "A8", "A9", "B1", "B3", "B5", "BP",
						"BR", "BT", "C2", "D1", "D2", "D6", "D8", "D9", "E3", "E4", "E5", "E6", "E8", "E9", "F1", "F2", "F4", "G1", "G2", "G3", "G6", "G7", "G8", "H6", "H7", "H8", "I1", "I2", "I6", "I7", "I8", "I9", "J1", "J2", "J4", "J5", "K9",
						"L3", "L4", "L5", "PP", "PQ", "PR"),
					'None' => array("00", "08", "11", "24", "26", "27", "28", "29", "31", "32", "34", "91", "92", "93", "94", "E7"),
					'Resend' => array("19", "98", "99", "L2"),
					'Voice' => array("01", "02", "10", "15", "16", "17", "18", "81", "82", "83", "84"),
					'Wait' => array("86", "A3", "L1")
				);
				$action = '';
				$out = '';
				$message = $txResponse['procStatusMessage'];
				foreach ($arr_groups as $key => $codes) {
					if (in_array($code, $codes)) {
						$action = $key;
					}
				}

				if ($message == "Merchant Override Decline") {
					$message = "CVV or AVV error";
				}

				switch ($action) {
					case 'Call': $out = "Call your Chase Paymentech Customer Service representative for assistance - {$code}";
						break;
					case 'Customer': $out = "Decline Card. Please obtain alternate payment method - {$message}";
						break;
					case 'Fix': $code = implode(',', $code);
						$out = "Contact the Developers - {$code}";
						break;
					case 'None': $out = (!empty($message)) ? $message : "Approved";
						break;
					case 'Resend': $out = "Try again";
						break;
					case 'Voice': $out = "Perform a voice authorization per instructions provided by Chase Paymentech - {$code}";
						break;
					case 'Wait': $out = "Wait 2-3 days before resending or try to resolve with the customer - {$code}";
						break;
					default: $code = implode(',', $code);
						$out = "Contact the Developers - {$code}";
						break;
				}

				Mage::throwException(Mage::helper('paygate')->__($out));
		}
		return $this;
	}

}
