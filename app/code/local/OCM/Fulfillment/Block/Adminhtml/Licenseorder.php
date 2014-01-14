<?php
class OCM_Fulfillment_Block_Adminhtml_Licenseorder extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->_controller = 'adminhtml_sales_licenseorder';
        $this->_blockGroup = 'ocm_fulfillment';
        $this->_headerText = Mage::helper('ocm_fulfillment')->__('Manage License Order');
        $this->_removeButton('add');
    }
}