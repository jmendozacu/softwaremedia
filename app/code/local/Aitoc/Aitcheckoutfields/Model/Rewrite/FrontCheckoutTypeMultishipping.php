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
/**
 * Magento
 *
 */


class Aitoc_Aitcheckoutfields_Model_Rewrite_FrontCheckoutTypeMultishipping extends Mage_Checkout_Model_Type_Multishipping
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