<?php

class OCM_ChasePaymentTech_Model_PaymentMethod extends Mage_Payment_Model_Method_Abstract
{
    protected $_formBlockType = 'payment/form_cc';
    protected $_infoBlockType = 'payment/info_cc';
    protected $_parentcode = 'chasePaymentTech';
	protected $_code = 'chasePaymentTech';
	protected $_isGateway               = true;
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = true;
    protected $_customerCode	        = false;
    protected $_canRefund               = true;
    protected $_canRefundInvoicePartial	= true;
    protected $_canVoid                 = true;
    protected $_canUseInternal          = true;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = true;
    protected $_canSaveCc 				= false;
    
    const AUTHORIZE = 'Authorize';
    const CAPTURE = 'Capture';
    const SALE = 'Sale';
    const VOID = 'Void';
    const REFUND = 'Refund';
    
    protected $_paymentProcessor;
    
    public function __construct()
    {
        $this->_paymentProcessor = Mage::getModel('chasePaymentTech/paymentProcessor');
    }

	 

  
	public function authorize(Varien_Object $payment, $amount)
    {    
        $this->_paymentProcessor->buildRequest($payment, $amount);
        $authTxResponse = $this->_paymentProcessor->sendRequest(self::AUTHORIZE);
        
        $this->_processResponse($payment, $authTxResponse,0,0);
        
        return $this;
    }
    
    public function capture(Varien_Object $payment, $amount)
    {
        if ($payment->getParentTransactionId())
        {
            $this->_paymentProcessor->buildCaptureRequest($payment, $amount);
            $captureTxResponse = $this->_paymentProcessor->sendRequest(self::CAPTURE);
        } else 
        {
           $this->_paymentProcessor->buildCaptureRequest($payment, $amount);
           $captureTxResponse = $this->_paymentProcessor->sendRequest(self::SALE);
        }
          
        $this->_processResponse($payment, $captureTxResponse,1,1);
    
    }
    
    public function void(Varien_Object $payment)
    {
        $this->_paymentProcessor->buildReverseRequest($payment, 0);
        $voidTxResponse = $this->_paymentProcessor->sendRequest(self::VOID);
        $this->_processResponse($payment, $voidTxResponse,1,1);
        
        return $this;
    }
    
    public function refund(Varien_Object $payment, $amount)
    {
        $this->_paymentProcessor->buildReverseRequest($payment, $amount);
        $voidTxResponse = $this->_paymentProcessor->sendRequest(self::REFUND);
        $this->_processResponse($payment, $voidTxResponse,1,1);
        
        return $this;
    }

    
    private function _processResponse($payment, $txResponse,$txClose, $txParentClose)
    {

        switch ($txResponse["Response"])
        {
            case "Approved":
                $payment->setTransactionId($txResponse["TransactionId"]);
                $payment->setIsTransactionClosed($txClose);
                $payment->setShouldCloseParentTransaction($txParentClose);
                break;
            case "Declined":
                Mage::throwException(Mage::helper('paygate')->__('The credit card was declined.'));
                break;
            case "Error":
                Mage::throwException(Mage::helper('paygate')->__('Error #'.$txResponse["ErrorCode"]));
        }
    }

	


}