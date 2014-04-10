<?php

class OCM_ChasePaymentTech_Model_PaymentProcessor {

	protected $_txRequest;
	protected $_logger;

	const AUTHORIZE_METHOD = 'newOrder';
	const AUTHORIZE_TRANSTYPE = 'A';
	const AUTHORIZE_APPROVE_CODE = '00';
	const CAPTURE_METHOD = 'markForCapture';
	const SALE_TRANSTYPE = 'AC';
	const SALE_METHOD = 'newOrder';
	const VOID_METHOD = 'reversal';
	const REFUND_METHOD = 'newOrder';
	const REFUND_TRANSTYPE = 'R';
	const PROFILE_METHOD = 'profileAdd';

	public function __construct() {
		$this->_logger = Mage::getModel('chasePaymentTech/logger');
		$this->_logger->debug('In OCM_ChasePaymentTech_Model_PaymentProcessor');
	}

	public function buildProfileAddRequest(Varien_Object $payment) {
		$order = $payment->getOrder();
		$billing = $order->getBillingAddress();

		$address = $billing->getStreet();
		$region = Mage::getModel('directory/region')->load($billing->getRegionId());

		$txRequest = new StdClass();
		$txRequest->profileAddRequest = new StdClass();
		$txRequest->profileAddRequest->orbitalConnectionUsername = Mage::getStoreConfig('payment/chasePaymentTech/username', Mage::app()->getStore());
		$txRequest->profileAddRequest->orbitalConnectionPassword = Mage::getStoreConfig('payment/chasePaymentTech/password', Mage::app()->getStore());
		$txRequest->profileAddRequest->bin = Mage::getStoreConfig('payment/chasePaymentTech/bin', Mage::app()->getStore());
		$txRequest->profileAddRequest->merchantID = Mage::getStoreConfig('payment/chasePaymentTech/merchant_id', Mage::app()->getStore());
		$txRequest->profileAddRequest->customerName = $billing->getFirstname() . ' ' . $billing->getLastname();
		$txRequest->profileAddRequest->customerRefNum = '';
		$txRequest->profileAddRequest->customerAddress1 = $address[0];
		$txRequest->profileAddRequest->customerAddress2 = (isset($address[1]) ? $address[1] : '');
		$txRequest->profileAddRequest->customerCity = $billing->getCity();
		$txRequest->profileAddRequest->customerState = $region->getCode();
		$txRequest->profileAddRequest->customerZIP = $billing->getPostcode();
		$txRequest->profileAddRequest->customerEmail = $billing->getEmail();
		$txRequest->profileAddRequest->customerPhone = $billing->getTelephone();
		$txRequest->profileAddRequest->customerCountryCode = $billing->getCountryId();

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

		$txRequest->profileAddRequest->ccAccountNum = $payment->getCcNumber();
		$txRequest->profileAddRequest->ccExp = $payment->getCcExpYear() . sprintf('%02d', $payment->getCcExpMonth());

		$this->_txRequest = $txRequest;
	}

	public function buildRequest(Varien_Object $payment, $amount, $customerRefNum = false) {
		$order = $payment->getOrder();
		$billing = $order->getBillingAddress();
		Mage::log('AMOUNT: ' . $amount, null, 'SDB.log');
		$txRequest = new StdClass();
		$txRequest->newOrderRequest = new StdClass();
		$txRequest->newOrderRequest->orbitalConnectionUsername = Mage::getStoreConfig('payment/chasePaymentTech/username', Mage::app()->getStore());
		$txRequest->newOrderRequest->orbitalConnectionPassword = Mage::getStoreConfig('payment/chasePaymentTech/password', Mage::app()->getStore());
		$txRequest->newOrderRequest->version = '2.8';
		$txRequest->newOrderRequest->industryType = 'EC';
		$txRequest->newOrderRequest->bin = Mage::getStoreConfig('payment/chasePaymentTech/bin', Mage::app()->getStore());
		$txRequest->newOrderRequest->merchantID = Mage::getStoreConfig('payment/chasePaymentTech/merchant_id', Mage::app()->getStore());
		$txRequest->newOrderRequest->terminalID = Mage::getStoreConfig('payment/chasePaymentTech/terminal_id', Mage::app()->getStore());

		// Check to see if we are using a profile
		if (empty($customerRefNum)) {
			$txRequest->newOrderRequest->ccAccountNum = $payment->getCcNumber();
			$txRequest->newOrderRequest->ccExp = $payment->getCcExpYear() . sprintf('%02d', $payment->getCcExpMonth());
			if ($payment->getCcType() == 'VI' || $payment->getCcType() == 'MC') {
				$txRequest->newOrderRequest->ccCardVerifyPresenceInd = 1;
			}
			$txRequest->newOrderRequest->ccCardVerifyNum = $payment->getCcCid();
			$txRequest->newOrderRequest->avsZip = $billing->getPostcode();
			$txRequest->newOrderRequest->avsAddress1 = implode(' ', $billing->getStreet());
			$txRequest->newOrderRequest->avsCity = $billing->getCity();
		} else {
			$txRequest->newOrderRequest->ccAccountNum = NULL;
			$txRequest->newOrderRequest->customerRefNum = $customerRefNum;
		}
		
		$txRequest->newOrderRequest->orderID = $order->getIncrementId();
		$txRequest->newOrderRequest->amount = round($amount * 100, 0);
		$txRequest->newOrderRequest->txRefNum = $payment->getParentTransactionId();

		$this->_txRequest = $txRequest;
	}

	public function buildReverseRequest(Varien_Object $payment, $amount) {
		$order = $payment->getOrder();
		$billing = $order->getBillingAddress();
		Mage::log('AMOUNT: ' . $amount, null, 'SDB.log');
		$txRequest = new StdClass();
		$txRequest->reversalRequest = new StdClass();
		$txRequest->reversalRequest->orbitalConnectionUsername = Mage::getStoreConfig('payment/chasePaymentTech/username', Mage::app()->getStore());
		$txRequest->reversalRequest->orbitalConnectionPassword = Mage::getStoreConfig('payment/chasePaymentTech/password', Mage::app()->getStore());
		$txRequest->reversalRequest->version = '2.8';
		$txRequest->reversalRequest->industryType = 'EC';
		$txRequest->reversalRequest->bin = Mage::getStoreConfig('payment/chasePaymentTech/bin', Mage::app()->getStore());
		$txRequest->reversalRequest->merchantID = Mage::getStoreConfig('payment/chasePaymentTech/merchant_id', Mage::app()->getStore());
		$txRequest->reversalRequest->terminalID = Mage::getStoreConfig('payment/chasePaymentTech/terminal_id', Mage::app()->getStore());
		$txRequest->reversalRequest->ccAccountNum = $payment->getCcNumber();
		$txRequest->reversalRequest->ccExp = $payment->getCcExpYear() . sprintf('%02d', $payment->getCcExpMonth());
		$txRequest->reversalRequest->ccCardVerifyNum = $payment->getCcCid();
		$txRequest->reversalRequest->avsZip = $billing->getPostcode();
		$txRequest->reversalRequest->avsAddress1 = implode(' ', $billing->getStreet());
		$txRequest->reversalRequest->avsCity = $billing->getCity();
		/* $txRequest->newOrderRequest->avsState = $billing->getRegion(); */
		$txRequest->reversalRequest->orderID = $order->getIncrementId();
		$txRequest->reversalRequest->amount = round($amount * 100, 0);
		$txRequest->reversalRequest->txRefNum = $payment->getParentTransactionId();

		$this->_txRequest = $txRequest;
	}

	public function buildCaptureRequest(Varien_Object $payment, $amount, $customerRefNum = null) {
		$order = $payment->getOrder();
		$billing = $order->getBillingAddress();
		Mage::log('AMOUNT: ' . $amount, null, 'SDB.log');
		$txRequest = new StdClass();
		$txRequest->markForCaptureRequest = new StdClass();
		$txRequest->markForCaptureRequest->orbitalConnectionUsername = Mage::getStoreConfig('payment/chasePaymentTech/username', Mage::app()->getStore());
		$txRequest->markForCaptureRequest->orbitalConnectionPassword = Mage::getStoreConfig('payment/chasePaymentTech/password', Mage::app()->getStore());
		
		if ($customerRef)
			$txRequest->markForCaptureRequest->customerRefNum = $customerRef;
		
		$txRequest->markForCaptureRequest->version = '2.8';
		$txRequest->markForCaptureRequest->industryType = 'EC';
		$txRequest->markForCaptureRequest->bin = Mage::getStoreConfig('payment/chasePaymentTech/bin', Mage::app()->getStore());
		$txRequest->markForCaptureRequest->merchantID = Mage::getStoreConfig('payment/chasePaymentTech/merchant_id', Mage::app()->getStore());
		$txRequest->markForCaptureRequest->terminalID = Mage::getStoreConfig('payment/chasePaymentTech/terminal_id', Mage::app()->getStore());
		$txRequest->markForCaptureRequest->ccAccountNum = $payment->getCcNumber();
		$txRequest->markForCaptureRequest->ccExp = $payment->getCcExpYear() . sprintf('%02d', $payment->getCcExpMonth());
		$txRequest->markForCaptureRequest->ccCardVerifyNum = $payment->getCcCid();
		$txRequest->markForCaptureRequest->avsZip = $billing->getPostcode();
		$txRequest->markForCaptureRequest->avsAddress1 = implode(' ', $billing->getStreet());
		$txRequest->markForCaptureRequest->avsCity = $billing->getCity();
		/* $txRequest->newOrderRequest->avsState = $billing->getRegion(); */
		$txRequest->markForCaptureRequest->orderID = $order->getIncrementId();
		$txRequest->markForCaptureRequest->amount = round($amount * 100, 0);
		$txRequest->markForCaptureRequest->txRefNum = $payment->getParentTransactionId();

		$this->_txRequest = $txRequest;
	}

	public function sendRequest($method) {

		switch ($method) {
			case 'Authorize':
				$this->_txRequest->newOrderRequest->transType = self::AUTHORIZE_TRANSTYPE;
				$TxResponse = $this->_sendRequest(self::AUTHORIZE_METHOD, $this->_txRequest);
				break;
			case 'Capture':
				$this->_txRequest->markForCaptureRequest->transType = self::SALE_TRANSTYPE;
				$TxResponse = $this->_sendRequest(self::CAPTURE_METHOD, $this->_txRequest);
				break;
			case 'Sale':
				$this->_txRequest->newOrderRequest->transType = self::SALE_TRANSTYPE;
				$TxResponse = $this->_sendRequest(self::SALE_METHOD, $this->_txRequest);
				break;
			case 'Void':
				$TxResponse = $this->_sendRequest(self::VOID_METHOD, $this->_txRequest);
				break;
			case 'Refund':
				$this->_txRequest->newOrderRequest->transType = self::REFUND_TRANSTYPE;
				$TxResponse = $this->_sendRequest(self::REFUND_METHOD, $this->_txRequest);
				break;
			case 'ProfileAdd':
				$TxResponse = $this->_sendRequest(self::PROFILE_METHOD, $this->_txRequest);
				break;
		}

		$TxResponseCode = $this->_parseResponse($TxResponse);
		$TxApprovalCode = $this->_getApprovalStatus($TxResponse);
		$TxProcCode = $this->_getProcStatus($TxResponse);

		//Handle refund requests through Approval Status
		if ($method == "Refund") {
			switch ($TxApprovalCode) {

				case 1:
					$resultArray = array(
						'Response' => "Approved",
						'TransactionId' => $this->_getTransactionId($TxResponse)
					);
					Mage::log('In switch approved', null, 'SDB.log');
					break;
				default:
					$resultArray = array(
						'Response' => "Error",
						'procStatusMessage' => $TxResponse->return->procStatusMessage,
						'ErrorCode' => $TxResponseCode
					);
					Mage::log('In switch default', null, 'SDB.log');
			}
		} elseif ($method == "Capture" || $method == "Void") {
			switch ($TxProcCode) {

				case "0":
					$resultArray = array(
						'Response' => "Approved",
						'TransactionId' => $this->_getTransactionId($TxResponse)
					);
					Mage::log('In switch approved', null, 'SDB.log');
					break;
				default:
					$resultArray = array(
						'Response' => "Error",
						'procStatusMessage' => $TxResponse->return->procStatusMessage,
						'ErrorCode' => $TxResponseCode
					);
					Mage::log('In switch default', null, 'SDB.log');
			}
		} elseif ($method == 'ProfileAdd') {
			switch ($TxProcCode) {
				case '0':
					$resultArray = array(
						'Response' => "Approved",
						'CustomerRefNum' => $TxResponse->return->customerRefNum,
					);
					break;
				default:
					$resultArray = array(
						'Response' => "Error",
						'procStatusMessage' => $TxResponse->return->customerProfileMessage,
						'ErrorCode' => $TxProcCode,
					);
			}
		} else {
			//Handle Capture through Proc Code
			switch ($TxResponseCode) {

				case "00":
					$resultArray = array(
						'Response' => "Approved",
						'TransactionId' => $this->_getTransactionId($TxResponse)
					);
					Mage::log('In switch last', null, 'SDB.log');
					break;
				default:
					$resultArray = array(
						'Response' => "Error",
						'procStatusMessage' => $TxResponse->return->procStatusMessage,
						'ErrorCode' => $TxResponseCode
					);
					Mage::log('In switch last', null, 'SDB.log');
			}
		}
		return $resultArray;
	}

	private function _parseResponse($response) {
		return $response->return->respCode;
	}

	private function _getTransactionId($response) {
		return $response->return->txRefNum;
	}

	private function _getApprovalStatus($response) {
		return $response->return->approvalStatus;
	}

	public function _getProcStatus($response) {
		return $response->return->procStatus;
	}

	private function _getProfileProcStatus($response) {
		return $response->return->profileProcStatus;
	}

	public function _sendRequest($method, $request) {
		$wsdl = Mage::getStoreConfig('payment/chasePaymentTech/url', Mage::app()->getStore());

		try {
			$client = new SoapClient($wsdl, array('trace' => 1));
			$response = $client->$method($request);
			Mage::log($response,NULL,'response.log');
			$this->_logger->debug("\nRequest\n" . $request);
			$this->_logger->debug("\nResponse\n" . $response);

			return $response;
		} catch (SoapFault $fault) {
			var_dump($request);
			echo "<br /><br />";
			var_dump($response);
			$this->_logger->error('In Send Request - Threw a SoapFault\n' . $fault);
			$this->_logger->error("\nRequest\n" . $request);
			$this->_logger->error("\nResponse\n" . $response);
		}
	}

}
