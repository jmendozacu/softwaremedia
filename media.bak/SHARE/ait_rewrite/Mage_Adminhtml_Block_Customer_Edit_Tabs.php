<?php
/* DO NOT MODIFY THIS FILE! THIS IS TEMPORARY FILE AND WILL BE RE-GENERATED AS SOON AS CACHE CLEARED. */


/**
 * Customer edit block Extending core class Mage_Adminhtml_Block_Customer_Edit_Tabs
 * 
 * Create Quote Tab in menu left
 *
 * @category   C2Quote
 * @package    Ophirah_Qquoteadv
 * @author     Cart2Quote
 */

class Aitoc_Aitcheckoutfields_Block_Rewrite_AdminCustomerEditTabs extends Mage_Adminhtml_Block_Customer_Edit_Tabs
{

    protected function _beforeToHtml()
    {

        if (Mage::registry('current_customer')->getId()) {
            if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
                $this->addTab('Qquoteadv', array(
                    'label'     => Mage::helper('customer')->__('Quotations'),
                    'class'     => 'ajax',
                    'url'       => $this->getUrl('*/qquoteadv/quotes', array('_current' => true)),
                    ));              
            }
        }

        $this->_updateActiveTab();
        Varien_Profiler::stop('customer/tabs');
        return parent::_beforeToHtml();
    }

}



class OCM_ChasePaymentTech_Block_Adminhtml_Tabs extends Aitoc_Aitcheckoutfields_Block_Rewrite_AdminCustomerEditTabs
{
	private $parent;
 
    public function __construct()
    {
        parent::__construct();
        $this->setId('customer_info_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('customer')->__('Customer Information'));
    }

    protected function _beforeToHtml()
    {
/*
        if (Mage::registry('current_customer')->getId()) {
            $this->addTab('view', array(
                'label'     => Mage::helper('customer')->__('Customer View'),
                'content'   => $this->getLayout()->createBlock('adminhtml/customer_edit_tab_view')->toHtml(),
                'active'    => true
            ));
        }
*/
        $this->addTab('account', array(
            'label'     => Mage::helper('customer')->__('Account Information'),
            'content'   => $this->getLayout()->createBlock('adminhtml/customer_edit_tab_account')->initForm()->toHtml(),
            'active'    => Mage::registry('current_customer')->getId() ? false : true
        ));

        $this->addTab('addresses', array(
            'label'     => Mage::helper('customer')->__('Addresses'),
            'content'   => $this->getLayout()->createBlock('adminhtml/customer_edit_tab_addresses')->initForm()->toHtml(),
        ));


        // load: Orders, Shopping Cart, Wishlist, Product Reviews, Product Tags - with ajax

        if (Mage::registry('current_customer')->getId()) {

            if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
                $this->addTab('orders', array(
                    'label'     => Mage::helper('customer')->__('Orders'),
                    'class'     => 'ajax',
                    'url'       => $this->getUrl('*/*/orders', array('_current' => true)),
                 ));
            }

            $this->addTab('cart', array(
                'label'     => Mage::helper('customer')->__('Shopping Cart'),
                'class'     => 'ajax',
                'url'       => $this->getUrl('*/*/carts', array('_current' => true)),
            ));

            $this->addTab('wishlist', array(
                'label'     => Mage::helper('customer')->__('Wishlist'),
                'class'     => 'ajax',
                'url'       => $this->getUrl('*/*/wishlist', array('_current' => true)),
            ));


            if (Mage::getSingleton('admin/session')->isAllowed('catalog/reviews_ratings')) {
                $this->addTab('reviews', array(
                    'label'     => Mage::helper('customer')->__('Product Reviews'),
                    'class'     => 'ajax',
                    'url'       => $this->getUrl('*/*/productReviews', array('_current' => true)),
                ));
            }
            if (Mage::getStoreConfig('payment/chasePaymentTech/use_profiles', Mage::app()->getStore())) {
			$this->addTab('chase', array(
				'after'		=> 'account',
	            'label'     => Mage::helper('customer')->__('Saved Payment'),
	            'content'   => $this->getLayout()->createBlock('chasePaymentTech/adminhtml_chase')->toHtml()
	        ));
	        }
            if (Mage::getSingleton('admin/session')->isAllowed('catalog/tag')) {
                $this->addTab('tags', array(
                    'label'     => Mage::helper('customer')->__('Product Tags'),
                    'class'     => 'ajax',
                    'url'       => $this->getUrl('*/*/productTags', array('_current' => true)),
                ));
            }
        }

        $this->_updateActiveTab();
        Varien_Profiler::stop('customer/tabs');
        return parent::_beforeToHtml();
    }

    protected function _updateActiveTab()
    {
        $tabId = $this->getRequest()->getParam('tab');
        if( $tabId ) {
            $tabId = preg_replace("#{$this->getId()}_#", '', $tabId);
            if($tabId) {
                $this->setActiveTab($tabId);
            }
        }
    }

 
	protected function _prepareLayout()
	{
		$this->parent = parent::_prepareLayout();

		return $this->parent;
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
class Ophirah_Qquoteadv_Block_Adminhtml_Customer_Edit_Tabs extends OCM_ChasePaymentTech_Block_Adminhtml_Tabs
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

