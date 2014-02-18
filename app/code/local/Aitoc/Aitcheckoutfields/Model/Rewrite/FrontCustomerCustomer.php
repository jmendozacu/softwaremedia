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
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */
class Aitoc_Aitcheckoutfields_Model_Rewrite_FrontCustomerCustomer extends Mage_Customer_Model_Customer
{
    protected function _beforeSave()
    {
        $oReq = Mage::app()->getFrontController()->getRequest();
        
        $data = $oReq->getPost('aitreg');
        
        if($data && !Mage::registry('aitoc_customer_saved'))
        {
            $oAttribute = Mage::getModel('aitcheckoutfields/aitcheckoutfields');
            foreach ($data as $sKey => $sVal)
            {
                $oAttribute->setCustomValue($sKey, $sVal, 'register');
            }
            Mage::register('aitoc_customer_to_save', true);
        }
         
        return parent::_beforeSave();
    }
    protected function _afterSave()
    {
        $oAttribute = Mage::getModel('aitcheckoutfields/aitcheckoutfields');
        if(Mage::registry('aitoc_customer_to_save'))
        {
            $customerId = $this->getId();
            
            $oDb = Mage::getSingleton('core/resource')->getConnection('core_write');
            $oDb->delete($oAttribute->getSetting('CustomerAttrTable'), "entity_id = {$customerId}");
            
            $oAttribute->saveCustomerData($customerId);
            $oAttribute->clearCheckoutSession('register');
            Mage::unregister('aitoc_customer_to_save');
            Mage::register('aitoc_customer_saved', true);
        }
        $oAttribute->clearCheckoutSession('register');
        return parent::_afterSave();       
    }
}