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
class Aitoc_Aitcheckoutfields_Block_Rewrite_FrontCheckoutOnepageBilling extends Mage_Checkout_Block_Onepage_Billing
{
    protected $_mainModel;
    
    protected function _construct()
    {
        parent::_construct();
        $this->_mainModel = Mage::getModel('aitcheckoutfields/aitcheckoutfields');
    }
    
    public function getFieldHtml($aField)
    {
        $sSetName = 'billing';
        
        return $this->_mainModel->getAttributeHtml($aField, $sSetName, 'onepage');
    }
    
    public function getCustomFieldList($iTplPlaceId)
    {
        $iStepId = Mage::helper('aitcheckoutfields')->getStepId('billing');
        
        if (!$iStepId) return false;

        return $this->_mainModel->getCheckoutAttributeList($iStepId, $iTplPlaceId, 'onepage');
    }
    
    public function getRegCustomFieldList()
    {
        $iStepId = Mage::helper('aitcheckoutfields')->getStepId('billing');
        
        if (!$iStepId) return false;
        
        $fields = false;
        $fieldsTmp = $this->_mainModel->getCustomerAttributeList();
        
        if($fieldsTmp)
        {
            $fields = array();
            foreach($fieldsTmp as $placeholder)
            {
                foreach ($placeholder as $id => $data)
                {
                    if(!$data['is_searchable'])
                    {
                        $fields[$id]=$data;
                    }
                }
            }
        }
        return $fields;
    }
    
    public function checkStepHasRequired()
    {
        $iStepId = Mage::helper('aitcheckoutfields')->getStepId('shippinfo');
        
        if (!$iStepId) return false;

        return $this->_mainModel->checkStepHasRequired($iStepId, 'onepage');
    } 
}