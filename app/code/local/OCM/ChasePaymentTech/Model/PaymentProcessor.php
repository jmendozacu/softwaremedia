<?php

class OCM_ChasePaymentTech_Model_PaymentProcessor
{ 
  
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
    
    
    public function __construct()
    {
        $this->_logger = Mage::getModel('chasePaymentTech/logger');
        $this->_logger->debug('In OCM_ChasePaymentTech_Model_PaymentProcessor');
    }
    
    	
    public function buildRequest(Varien_Object $payment, $amount)
	{	
	    $order = $payment->getOrder();
    	$billing = $order->getBillingAddress();
    	Mage::log('AMOUNT: ' . $amount,null,'SDB.log');
    	$txRequest = new StdClass();
    	$txRequest->newOrderRequest = new StdClass();
    	$txRequest->newOrderRequest->orbitalConnectionUsername = Mage::getStoreConfig('payment/chasePaymentTech/username',Mage::app()->getStore());
    	$txRequest->newOrderRequest->orbitalConnectionPassword = Mage::getStoreConfig('payment/chasePaymentTech/password',Mage::app()->getStore());
    	$txRequest->newOrderRequest->industryType = 'EC';
    	$txRequest->newOrderRequest->bin = Mage::getStoreConfig('payment/chasePaymentTech/bin',Mage::app()->getStore());
    	$txRequest->newOrderRequest->merchantID = Mage::getStoreConfig('payment/chasePaymentTech/merchant_id',Mage::app()->getStore());
    	$txRequest->newOrderRequest->terminalID = Mage::getStoreConfig('payment/chasePaymentTech/terminal_id',Mage::app()->getStore());
    	$txRequest->newOrderRequest->ccAccountNum = $payment->getCcNumber();
    	$txRequest->newOrderRequest->ccExp = $payment->getCcExpYear().sprintf('%02d',$payment->getCcExpMonth());
    	$txRequest->newOrderRequest->ccCardVerifyNum = $payment->getCcCid();
    	$txRequest->newOrderRequest->avsZip = $billing->getPostcode();
    	$txRequest->newOrderRequest->avsAddress1 = implode(' ', $billing->getStreet());
    	$txRequest->newOrderRequest->avsCity = $billing->getCity();
    	/*$txRequest->newOrderRequest->avsState = $billing->getRegion();*/
    	$txRequest->newOrderRequest->orderID = $order->getIncrementId();
    	$txRequest->newOrderRequest->amount = round($amount*100,0);
    	$txRequest->newOrderRequest->txRefNum = $payment->getParentTransactionId();

        $this->_txRequest = $txRequest;
	}
	
	public function buildReverseRequest(Varien_Object $payment, $amount)
	{	
	    $order = $payment->getOrder();
    	$billing = $order->getBillingAddress();
    	Mage::log('AMOUNT: ' . $amount,null,'SDB.log');
    	$txRequest = new StdClass();
    	$txRequest->reversalRequest = new StdClass();
    	$txRequest->reversalRequest->orbitalConnectionUsername = Mage::getStoreConfig('payment/chasePaymentTech/username',Mage::app()->getStore());
    	$txRequest->reversalRequest->orbitalConnectionPassword = Mage::getStoreConfig('payment/chasePaymentTech/password',Mage::app()->getStore());
    	$txRequest->reversalRequest->industryType = 'EC';
    	$txRequest->reversalRequest->bin = Mage::getStoreConfig('payment/chasePaymentTech/bin',Mage::app()->getStore());
    	$txRequest->reversalRequest->merchantID = Mage::getStoreConfig('payment/chasePaymentTech/merchant_id',Mage::app()->getStore());
    	$txRequest->reversalRequest->terminalID = Mage::getStoreConfig('payment/chasePaymentTech/terminal_id',Mage::app()->getStore());
    	$txRequest->reversalRequest->ccAccountNum = $payment->getCcNumber();
    	$txRequest->reversalRequest->ccExp = $payment->getCcExpYear().sprintf('%02d',$payment->getCcExpMonth());
    	$txRequest->reversalRequest->ccCardVerifyNum = $payment->getCcCid();
    	$txRequest->reversalRequest->avsZip = $billing->getPostcode();
    	$txRequest->reversalRequest->avsAddress1 = implode(' ', $billing->getStreet());
    	$txRequest->reversalRequest->avsCity = $billing->getCity();
    	/*$txRequest->newOrderRequest->avsState = $billing->getRegion();*/
    	$txRequest->reversalRequest->orderID = $order->getIncrementId();
    	$txRequest->reversalRequest->amount = round($amount*100,0);
    	$txRequest->reversalRequest->txRefNum = $payment->getParentTransactionId();

        $this->_txRequest = $txRequest;
	}
	

    public function buildCaptureRequest(Varien_Object $payment, $amount)
	{	
	    $order = $payment->getOrder();
    	$billing = $order->getBillingAddress();
    	Mage::log('AMOUNT: ' . $amount,null,'SDB.log');
    	$txRequest = new StdClass();
    	$txRequest->markForCaptureRequest = new StdClass();
    	$txRequest->markForCaptureRequest->orbitalConnectionUsername = Mage::getStoreConfig('payment/chasePaymentTech/username',Mage::app()->getStore());
    	$txRequest->markForCaptureRequest->orbitalConnectionPassword = Mage::getStoreConfig('payment/chasePaymentTech/password',Mage::app()->getStore());
    	$txRequest->markForCaptureRequest->industryType = 'EC';
    	$txRequest->markForCaptureRequest->bin = Mage::getStoreConfig('payment/chasePaymentTech/bin',Mage::app()->getStore());
    	$txRequest->markForCaptureRequest->merchantID = Mage::getStoreConfig('payment/chasePaymentTech/merchant_id',Mage::app()->getStore());
    	$txRequest->markForCaptureRequest->terminalID = Mage::getStoreConfig('payment/chasePaymentTech/terminal_id',Mage::app()->getStore());
    	$txRequest->markForCaptureRequest->ccAccountNum = $payment->getCcNumber();
    	$txRequest->markForCaptureRequest->ccExp = $payment->getCcExpYear().sprintf('%02d',$payment->getCcExpMonth());
    	$txRequest->markForCaptureRequest->ccCardVerifyNum = $payment->getCcCid();
    	$txRequest->markForCaptureRequest->avsZip = $billing->getPostcode();
    	$txRequest->markForCaptureRequest->avsAddress1 = implode(' ', $billing->getStreet());
    	$txRequest->markForCaptureRequest->avsCity = $billing->getCity();
    	/*$txRequest->newOrderRequest->avsState = $billing->getRegion();*/
    	$txRequest->markForCaptureRequest->orderID = $order->getIncrementId();
    	$txRequest->markForCaptureRequest->amount = round($amount*100,0);
    	$txRequest->markForCaptureRequest->txRefNum = $payment->getParentTransactionId();

        $this->_txRequest = $txRequest;
	}
	
	
	public function sendRequest($method)
	{
	
	    switch ($method)
	    {
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
	    }
    	
    	$TxResponseCode = $this->_parseResponse($TxResponse);
    	$TxApprovalCode = $this->_getApprovalStatus($TxResponse);
    	$TxProcCode = $this->_getProcStatus($TxResponse);
    	
    	//Handle refund requests through Approval Status
    	if ($method == "Refund") {
	        switch ($TxApprovalCode)
	        {
	        	
	            case 1:
	                $resultArray = array(
	                                'Response' =>"Approved",
	                                'TransactionId' => $this->_getTransactionId($TxResponse)
	                                );
	                Mage::log('In switch approved',null,'SDB.log');
	                break;
	            default:
	                $resultArray = array(
	                                'Response' =>"Error",
	                                'ErrorCode' => $TxResponseCode
	                                );
	                Mage::log('In switch default',null,'SDB.log');
	                
	        }	
        } elseif ($method == "Capture" || $method == "Void") {
        	switch ($TxProcCode)
	        {
	        	
	            case "0":
	                $resultArray = array(
	                                'Response' =>"Approved",
	                                'TransactionId' => $this->_getTransactionId($TxResponse)
	                                );
	                Mage::log('In switch approved',null,'SDB.log');
	                break;
	            default:
	                $resultArray = array(
	                                'Response' =>"Error",
	                                'ErrorCode' => $TxResponseCode
	                                );
	                Mage::log('In switch default',null,'SDB.log');
	        }
        } else {
        	//Handle Capture through Proc Code	       	
	    	switch ($TxResponseCode)
	        {
	        	
	            case "00":
	                $resultArray = array(
	                                'Response' =>"Approved",
	                                'TransactionId' => $this->_getTransactionId($TxResponse)
	                                );
	                Mage::log('In switch last',null,'SDB.log');
	                break;
	            default:
	                $resultArray = array(
	                                'Response' =>"Error",
	                                'ErrorCode' => $TxResponseCode
	                                );
	                Mage::log('In switch last',null,'SDB.log');
	                
	        }
        }
        return $resultArray;
    }
    
	
	private function _parseResponse($response)
	{
    	return $response->return->respCode;
	}
	
	private function _getTransactionId($response)
	{
    	return $response->return->txRefNum;
	}
	
	private function _getApprovalStatus($response)
	{
    	return $response->return->approvalStatus;
	}
	private function _getProcStatus($response)
	{
    	return $response->return->procStatus;
	}
	private function _sendRequest($method, $request)
	{
	    $wsdl = Mage::getStoreConfig('payment/chasePaymentTech/url',Mage::app()->getStore());
	    
    	try 
    	{
            $client = new SoapClient($wsdl, array('trace' => 1));
            $response = $client->$method($request);
            
            $this->_logger->debug("\nRequest\n".$client->__getLastRequest());
            $this->_logger->debug("\nResponse\n".$client->__getLastResponse());

            return $response;
        }
        catch(SoapFault $fault)
    	{
    	    $this->_logger->error('In Send Request - Threw a SoapFault\n'.$fault);
    	    $this->_logger->error("\nRequest\n".$client->__getLastRequest());
            $this->_logger->error("\nResponse\n".$client->__getLastResponse());
    	}
	}

}
