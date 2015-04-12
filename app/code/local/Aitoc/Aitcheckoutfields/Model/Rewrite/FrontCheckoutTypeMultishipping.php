<?php
/**
 * Checkout Fields Manager
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitcheckoutfields
 * @version      10.5.7
 * @license:     grDwoQqpctpZdS57isl8WpY91kLDyrRZ7i5S4ZKTe1
 * @copyright:   Copyright (c) 2015 AITOC, Inc. (http://www.aitoc.com)
 */
/**
 * Magento
 *
 */


/* AITOC static rewrite inserts start */
/* $meta=%default,AdjustWare_Deliverydate% */
if(Mage::helper('core')->isModuleEnabled('AdjustWare_Deliverydate')){
    class Aitoc_Aitcheckoutfields_Model_Rewrite_FrontCheckoutTypeMultishipping_Aittmp extends AdjustWare_Deliverydate_Model_Rewrite_FrontCheckoutTypeMultishipping {} 
 }else{
    /* default extends start */
    class Aitoc_Aitcheckoutfields_Model_Rewrite_FrontCheckoutTypeMultishipping_Aittmp extends Mage_Checkout_Model_Type_Multishipping {}
    /* default extends end */
}

/* AITOC static rewrite inserts end */
class Aitoc_Aitcheckoutfields_Model_Rewrite_FrontCheckoutTypeMultishipping extends Aitoc_Aitcheckoutfields_Model_Rewrite_FrontCheckoutTypeMultishipping_Aittmp
{
    
    public function createOrders()
    {
        $oReq = Mage::app()->getFrontController()->getRequest();
        
        $sKey  = 'multi';
        
        $data = $oReq->getPost($sKey);

        if ($data)
        {
            $oAttribute = Mage::getModel('aitcheckoutfields/aitcheckoutfields');
            
            foreach ($data as $sKey => $sVal)
            {
                $oAttribute->setCustomValue($sKey, $sVal, 'multishipping');
            }
        }
        
        $oResult = parent::createOrders();

        // save attribute data to DB
        
        $aOrderIdHash = $this->getOrderIds(true);

        if ($aOrderIdHash)
        {
            $oAttribute = Mage::getModel('aitcheckoutfields/aitcheckoutfields');
            
            foreach ($aOrderIdHash as $iOrderId => $sVal)
            {
                $oAttribute->saveCustomOrderData($iOrderId, 'multishipping');
            }
            
            $oAttribute->clearCheckoutSession('multishipping');
        }
        
        return $oResult;
    }    
}