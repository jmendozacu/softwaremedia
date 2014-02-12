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

class Aitoc_Aitcheckoutfields_Block_Rewrite_FrontCheckoutOnepagePayment extends Mage_Checkout_Block_Onepage_Payment
{
    
    protected function _construct()
    {
        parent::_construct();
    }
    
    
    public function getFieldHtml($aField)
    {
        $sSetName = 'payment';
        
        return Mage::getModel('aitcheckoutfields/aitcheckoutfields')->getAttributeHtml($aField, $sSetName, 'onepage',0,false,true);
    }
    
    public function getCustomFieldList($iTplPlaceId)
    {
        $iStepId = Mage::helper('aitcheckoutfields')->getStepId('payment');
        
        if (!$iStepId) return false;

        return Mage::getModel('aitcheckoutfields/aitcheckoutfields')->getCheckoutAttributeList($iStepId, $iTplPlaceId, 'onepage');
    }
    
    public function getAttributeEnableHtml($aField)
    {
        $sSetName = 'payment';
        
        return Mage::getModel('aitcheckoutfields/aitcheckoutfields')->getAttributeEnableHtml($aField, $sSetName);
    }
    
     
}