<?php
/* DO NOT MODIFY THIS FILE! THIS IS TEMPORARY FILE AND WILL BE RE-GENERATED AS SOON AS CACHE CLEARED. */

/**
 * Created by JetBrains PhpStorm.
 * User: Nguyen Sy Doan
 * Date: 2/8/13
 * Time: 1:56 PM
 * To change this template use File | Settings | File Templates.
 */ 
class Aitoc_Aitcheckoutfields_Model_Rewrite_FrontCheckoutTypeOnepage extends Mage_Checkout_Model_Type_Onepage {
    public $_newCustomer = array();
    public function saveBilling($data, $customerAddressId)
    {
        $address = $this->getQuote()->getBillingAddress();
        /* @var $addressForm Mage_Customer_Model_Form */
        $addressForm = Mage::getModel('customer/form');
        $addressForm->setFormCode('customer_address_edit')
            ->setEntityType('customer_address')
            ->setIsAjaxRequest(Mage::app()->getRequest()->isAjax());
            
        $dataForm = Mage::app()->getRequest()->getPost();
        
        if (!empty($customerAddressId)) {
            $customerAddress = Mage::getModel('customer/address')->load($customerAddressId);
            if ($customerAddress->getId()) {
                if ($customerAddress->getCustomerId() != $this->getQuote()->getCustomerId()) {
                    return array('error' => 1,
                        'message' => Mage::helper('checkout')->__('Customer Address is not valid.')
                    );
                }

                $address->importCustomerAddress($customerAddress)->setSaveInAddressBook(0);
                $addressForm->setEntity($address);
                $addressErrors  = $addressForm->validateData($address->getData());
                if ($addressErrors !== true) {
                    return array('error' => 1, 'message' => $addressErrors);
                }
            }
        } else {
            $dataForm = Mage::app()->getRequest()->getPost();
            if($dataForm['create_new_account']==1){
                $email = $dataForm['billing']['email'];
                $customer = Mage::getModel('customer/customer');
                $customer->setWebsiteId(Mage::app()->getWebsite()->getId());
                $customer->loadByEmail($email);
                //Zend_Debug::dump($customer->debug()); exit;
                if(!$customer->getId()) {
                    $customer->setEmail($email);
                    $customer->setFirstname($dataForm['billing']['firstname']);
                    $customer->setLastname($dataForm['billing']['lastname']);
                    $customer->setPassword($customer->generatePassword(6));
                    try{
                        //the save the data and send the new account email.
                        $customer->save();
                        $customer->setConfirmation(null);
                        $customer->save();
                        $customer->sendNewAccountEmail();
                        Mage::getSingleton('customer/session')->loginById($customer->getId());
                    }
                    catch(Exception $ex){
                    }
                }
            }
            $addressForm->setEntity($address);
            // emulate request object
            $addressData    = $addressForm->extractData($addressForm->prepareRequest($data));
            $addressErrors  = $addressForm->validateData($addressData);
            if ($addressErrors !== true) {
                return array('error' => 1, 'message' => array_values($addressErrors));
            }
            $addressForm->compactData($addressData);
            //unset billing address attributes which were not shown in form
            foreach ($addressForm->getAttributes() as $attribute) {
                if (!isset($data[$attribute->getAttributeCode()])) {
                    $address->setData($attribute->getAttributeCode(), NULL);
                }
            }
            $address->setCustomerAddressId(null);
            // Additional form data, not fetched by extractData (as it fetches only attributes)
            $address->setSaveInAddressBook(empty($data['save_in_address_book']) ? 0 : 1);
        }

        // validate billing address
        if (($validateRes = $address->validate()) !== true) {
            return array('error' => 1, 'message' => $validateRes);
        }

        $address->implodeStreetAddress();

        if (true !== ($result = $this->_validateCustomerData($data))) {
            return $result;
        }

        if (!$this->getQuote()->getCustomerId() && self::METHOD_REGISTER == $this->getQuote()->getCheckoutMethod()) {
            if ($this->_customerEmailExists($address->getEmail(), Mage::app()->getWebsite()->getId())) {
                return array('error' => 1, 'message' => $this->_customerEmailExistsMessage);
            }
        }

        if (!$this->getQuote()->isVirtual()) {
            /**
             * Billing address using otions
             */
            $usingCase = isset($data['use_for_shipping']) ? (int)$data['use_for_shipping'] : 0;

            switch ($usingCase) {
                case 0:
                    $shipping = $this->getQuote()->getShippingAddress();
                    $shipping->setSameAsBilling(0);
                    break;
                case 1:
                    $billing = clone $address;
                    $billing->unsAddressId()->unsAddressType();
                    $shipping = $this->getQuote()->getShippingAddress();
                    $shippingMethod = $shipping->getShippingMethod();

                    // Billing address properties that must be always copied to shipping address
                    $requiredBillingAttributes = array('customer_address_id');

                    // don't reset original shipping data, if it was not changed by customer
                    foreach ($shipping->getData() as $shippingKey => $shippingValue) {
                        if (!is_null($shippingValue) && !is_null($billing->getData($shippingKey))
                            && !isset($data[$shippingKey]) && !in_array($shippingKey, $requiredBillingAttributes)
                        ) {
                            $billing->unsetData($shippingKey);
                        }
                    }
                    $shipping->addData($billing->getData())
                        ->setSameAsBilling(1)
                        ->setSaveInAddressBook(0)
                        ->setShippingMethod($shippingMethod)
                        ->setCollectShippingRates(true);
                    $this->getCheckout()->setStepData('shipping', 'complete', true);
                    break;
            }
        }

        $this->getQuote()->collectTotals();
        $this->getQuote()->save();

        if (!$this->getQuote()->isVirtual() && $this->getCheckout()->getStepData('shipping', 'complete') == true) {
            //Recollect Shipping rates for shipping methods
            $this->getQuote()->getShippingAddress()->setCollectShippingRates(true);
        }

        $this->getCheckout()
            ->setStepData('billing', 'allow', true)
            ->setStepData('billing', 'complete', true)
            ->setStepData('shipping', 'allow', true);

        return array();
    }
    /**
     * Specify quote payment method
     *
     * @param   array $data
     * @return  array
     */
    public function savePayment($data)
    {
    	Mage::log('savePayment 1: ',NULL,'cc.log');
        if (empty($data)) {
            return array('error' => -1, 'message' => Mage::helper('checkout')->__('Invalid data.'));
        }
        $quote = $this->getQuote();
        if ($quote->isVirtual()) {
            $quote->getBillingAddress()->setPaymentMethod(isset($data['method']) ? $data['method'] : null);
        } else {
            $quote->getShippingAddress()->setPaymentMethod(isset($data['method']) ? $data['method'] : null);
        }

        // shipping totals may be affected by payment method
        if (!$quote->isVirtual() && $quote->getShippingAddress()) {
            $quote->getShippingAddress()->setCollectShippingRates(true);
        }
			
			
        $data['checks'] = Mage_Payment_Model_Method_Abstract::CHECK_USE_CHECKOUT
            | Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_COUNTRY
            | Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_CURRENCY
            | Mage_Payment_Model_Method_Abstract::CHECK_ORDER_TOTAL_MIN_MAX
            | Mage_Payment_Model_Method_Abstract::CHECK_ZERO_TOTAL;
		
		
        $payment = $quote->getPayment();
        $payment->importData($data);

        $quote->save();

        $this->getCheckout()
            ->setStepData('payment', 'complete', true)
            ->setStepData('review', 'allow', true);

        return array();
    }
    
}


/**
 * Checkout Fields Manager
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitcheckoutfields
 * @version      10.5.3
 * @license:     x8JlL6PzUPBtvXBsIIWQy9KjFdhME32yIbvID6DGHQ
 * @copyright:   Copyright (c) 2014 AITOC, Inc. (http://www.aitoc.com)
 */
class OCM_Checkout_Model_Checkout_Type_Onepage extends Aitoc_Aitcheckoutfields_Model_Rewrite_FrontCheckoutTypeOnepage
{
    // overwrite parent
    public function saveBilling($data, $customerAddressId)
    {
        if ($data)
        {
            $oAttribute = Mage::getModel('aitcheckoutfields/aitcheckoutfields');
            
            foreach ($data as $sKey => $sVal)
            {
                $oAttribute->setCustomValue($sKey, $sVal, 'onepage');
            }
        }

        return parent::saveBilling($data, $customerAddressId);
    }

    // overwrite parent
    public function saveShipping($data, $customerAddressId)
    {
        $canSave = true;
        if ($this->getAitcheckoutfieldsHelper()->checkIfAitocAitcheckoutIsActive())
        {
            $billing = Mage::app()->getRequest()->getPost('billing', array());
            $canSave = empty($billing['use_for_shipping']);
            
        }
        if ($data)
        {
            $oAttribute = Mage::getModel('aitcheckoutfields/aitcheckoutfields');
            
            foreach ($data as $sKey => $sVal)
            {
                $oAttribute->setCustomValue($sKey, $sVal, 'onepage');
            }
        }

        return ($canSave ? parent::saveShipping($data, $customerAddressId) : array());
    }

    // overwrite parent
    public function saveShippingMethod($shippingMethod)
    {
        $oReq = Mage::app()->getFrontController()->getRequest();
        
        $data = $oReq->getPost('shippmethod');
        
        if ($data)
        {
            $oAttribute = Mage::getModel('aitcheckoutfields/aitcheckoutfields');
            
            foreach ($data as $sKey => $sVal)
            {
                $oAttribute->setCustomValue($sKey, $sVal, 'onepage');
            }
        }
        
    /************** AITOC DELIVERY DATE COMPATIBILITY MODE: START ********************/
        
        $val = Mage::getConfig()->getNode('modules/AdjustWare_Deliverydate/active');
        if ((string)$val == 'true')
        {
            $errors = Mage::getModel('adjdeliverydate/step')->process('shippingMethod');
            if ($errors)
                return $errors;
        }
    
    /************** AITOC DELIVERY DATE COMPATIBILITY MODE: FINISH ********************/

        return parent::saveShippingMethod($shippingMethod);
    }
    
    // overwrite parent
    public function savePayment($data)
    {
        $return = parent::savePayment($data);
        
        if ($data)
        {
            $oAttribute = Mage::getModel('aitcheckoutfields/aitcheckoutfields');
            
            foreach ($data as $sKey => $sVal)
            {
                $oAttribute->setCustomValue($sKey, $sVal, 'onepage');
            }
        }

        return $return;
    }

    // overwrite parent
    public function saveOrder()
    {
        // set review attributes data
        
        $oReq = Mage::app()->getFrontController()->getRequest();
        foreach ($oReq->getParams() as $_param)
        {
            if(is_array($_param) && Mage::helper('aitcheckoutfields')->checkIfAitocAitcheckoutIsActive())
            {
                Mage::helper('aitcheckout/aitcheckoutfields')->saveCustomData($_param); 
            }
        }
        $data = $oReq->getPost('customreview');
        
        if ($data)
        {
            $oAttribute = Mage::getModel('aitcheckoutfields/aitcheckoutfields');
            
            foreach ($data as $sKey => $sVal)
            {
                $oAttribute->setCustomValue($sKey, $sVal, 'onepage');
            }
        }
       
        $oResult = parent::saveOrder();

        // save attribute data to DB
        
        $order = Mage::getModel('sales/order');
        $order->load($this->getCheckout()->getLastOrderId());
        
        $iOrderId = $this->getCheckout()->getLastOrderId();
        
        if ($iOrderId)
        {
            $oAttribute = Mage::getModel('aitcheckoutfields/aitcheckoutfields');

            $oAttribute->saveCustomOrderData($iOrderId, 'onepage');
            $oAttribute->clearCheckoutSession('onepage');
        }
        
        return $oResult;
    }
    
    // overwrite parent
    protected function _involveNewCustomer()
    {
        parent::_involveNewCustomer();
        
        $customerId = $this->getQuote()->getCustomer()->getId();
        Mage::getModel('aitcheckoutfields/aitcheckoutfields')->saveCustomerData($customerId, true);
    }
    
    /**
     *
     * @return Aitoc_Aitcheckoutfields_Helper_Data
     */
    public function getAitcheckoutfieldsHelper()
    {
        return Mage::helper('aitcheckoutfields');
    }
}

