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

class Aitoc_Aitcheckoutfields_Block_Rewrite_FrontCheckoutMultishippingShipping extends Mage_Checkout_Block_Multishipping_Shipping
{
    
    protected function _construct()
    {
        parent::_construct();
    }
    
    public function getFieldHtml($aField)
    {
        $sSetName = 'multi';
        
        return Mage::getModel('aitcheckoutfields/aitcheckoutfields')->getAttributeHtml($aField, $sSetName, 'multishipping',0,false,true);
    }
    
    public function getCustomFieldList($iTplPlaceId)
    {
        $iStepId = Mage::helper('aitcheckoutfields')->getStepId('mult_shippinfo');
        
        if (!$iStepId) return false;

        return Mage::getModel('aitcheckoutfields/aitcheckoutfields')->getCheckoutAttributeList($iStepId, $iTplPlaceId, 'multishipping');
    } 
}