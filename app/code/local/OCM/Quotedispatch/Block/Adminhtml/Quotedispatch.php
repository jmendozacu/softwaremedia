<?php
class OCM_Quotedispatch_Block_Adminhtml_Quotedispatch extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_quotedispatch';
    $this->_blockGroup = 'quotedispatch';
    $this->_headerText = Mage::helper('quotedispatch')->__('Quote Dispatch');
    $this->_addButtonLabel = Mage::helper('quotedispatch')->__('Add Quote');
    parent::__construct();
  }
//  public function _prepareLayout() {
//      $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs')->toHtml();
//      parent::_prepareLayout();
//  }
}