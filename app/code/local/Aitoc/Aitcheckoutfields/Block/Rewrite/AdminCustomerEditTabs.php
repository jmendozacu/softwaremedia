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
class Aitoc_Aitcheckoutfields_Block_Rewrite_AdminCustomerEditTabs extends Mage_Adminhtml_Block_Customer_Edit_Tabs
{
    protected function _beforeToHtml()
    {
    	$mainModel = Mage::getModel('aitcheckoutfields/aitcheckoutfields');
    
        $this->addTab('account', array(
            'label'     => Mage::helper('customer')->__('Account Information'),
            'content'   => $this->getLayout()->createBlock('adminhtml/customer_edit_tab_account')->initForm()->toHtml(),
            'active'    => Mage::registry('current_customer')->getId() ? false : true
        ));

        $this->addTab('addresses', array(
            'label'     => Mage::helper('customer')->__('Addresses'),
            'content'   => $this->getLayout()->createBlock('adminhtml/customer_edit_tab_addresses')->initForm()->toHtml(),
        ));
        
        if($mainModel->getCustomerAttributeList() && Mage::app()->getRequest()->getParam('id')>0)
        {
            $this->addTab('additional', array(
                'label'     => Mage::helper('aitcheckoutfields')->__('Additional Info'),
                'content'   => $this->getLayout()->createBlock('aitcheckoutfields/customer_edit_tab_additional')->initForm()->toHtml(),
            ));
        }

        $this->_updateActiveTab();
        Varien_Profiler::stop('customer/tabs');
        return parent::_beforeToHtml();
    }
}