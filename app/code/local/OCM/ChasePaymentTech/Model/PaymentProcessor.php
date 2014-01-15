<?php

class OCM_ChasePaymentTech_Model_PaymentProcessor
{ 
  
    protected $_txRequest;
    protected $_logger;
    
    const AUTHORIZE_METHOD = 'newOrder';
    const AUTHORIZE_TRANSTYPE = 'A';
    const AUTHORIZE_APPROVE_CODE = '00';
    const CAPTURE_METHOD = 'newOrder';
    const SALE_TRANSTYPE = 'AC';
    const SALE_METHOD = 'newOrder';
    const VOID_METHOD = 'CreditCardVoid';
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
	
	
	
	
	public function sendRequest($method)
	{
	
	    switch ($method)
	    {
	        case 'Authorize':
	            $this->_txRequest->newOrderRequest->transType = self::AUTHORIZE_TRANSTYPE;
    	        $TxResponse = $this->_sendRequest(self::AUTHORIZE_METHOD, $this->_txRequest);
    	        break;
            case 'Capture':
            	$this->_txRequest->newOrderRequest->transType = self::SALE_TRANSTYPE;
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
        } else {
        	//Handle Auth and Capture through Response Code	       	
	    	switch ($TxResponseCode)
	        {
	        	
	            case self::AUTHORIZE_APPROVE_CODE:
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
