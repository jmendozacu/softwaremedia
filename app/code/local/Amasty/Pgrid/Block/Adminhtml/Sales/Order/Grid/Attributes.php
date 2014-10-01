<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2010-2011 Amasty (http://www.amasty.com)
* @package Amasty_Pgrid
*/
class Amasty_Pgrid_Block_Adminhtml_Sales_Order_Grid_Attributes extends Mage_Adminhtml_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('ampgrid/sales_attributes.phtml');
    }
    
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        return $this;
    }
    
    public function getColumns() {
	    $columns = array(
	    	'po_number' => 'Purchase Order #',
	    	'net_terms' => 'Order Net Terms',
	    	'payment_method' => 'Payment Method',
	    	'billing_company' => 'Company Name',
	    	'x_forwarded_for' => 'Remote IP',
	    	'referer' => 'Referer',
	    	'value' => 'Suspicious',
	    	'sku' => 'Products'
	    );
	    
	    return $columns;
    }
    
    public function getAttributes()
    {
        $collection = Mage::getResourceModel('catalog/product_attribute_collection')
                         ->addVisibleFilter();
        $collection->getSelect()->where(
            $collection->getConnection()->quoteInto('main_table.frontend_input IN (?)', array('text', 'select', 'multiselect', 'boolean', 'textarea', 'price', 'weight'))
        );
        $collection->getSelect()->where(
            $collection->getConnection()->quoteInto('main_table.attribute_code NOT IN (?)', Mage::helper('ampgrid')->getDefaultColumns())
        );
        return $this->getColumns();
    }
    
    public function getSelectedAttributes()
    {
        return Mage::helper('ampgrid')->getOrderGridAttributes();
    }
    
    public function getSaveUrl()
    {
        $url = $this->getUrl('ampgrid/adminhtml_attribute/saveOrder');
        if (Mage::getStoreConfig('web/secure/use_in_adminhtml'))
        {
            $url = str_replace(Mage::getStoreConfig('web/unsecure/base_url'), Mage::getStoreConfig('web/secure/base_url'), $url);
        }
        return $url;
    }
    
    public function getBackUrl()
    {
        return $this->helper('core/url')->getCurrentUrl();
    }
}