<?php
/**
 * Checkout Fields Manager
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitcheckoutfields
 * @version      10.5.3
 * @license:     x8JlL6PzUPBtvXBsIIWQy9KjFdhME32yIbvID6DGHQ
 * @copyright:   Copyright (c) 2014 AITOC, Inc. (http://www.aitoc.com)
 */
class Aitoc_Aitcheckoutfields_Model_Rewrite_FrontCheckoutTypeOnepage extends Mage_Checkout_Model_Type_Onepage
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