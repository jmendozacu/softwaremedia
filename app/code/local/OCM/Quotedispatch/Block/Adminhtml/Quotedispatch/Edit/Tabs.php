<?php

class OCM_Quotedispatch_Block_Adminhtml_Quotedispatch_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('quotedispatch_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('quotedispatch')->__('Quote Dispatch'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('quotedispatch')->__('General'),
          'title'     => Mage::helper('quotedispatch')->__('General'),
          'content'   => $this->getLayout()->createBlock('quotedispatch/adminhtml_quotedispatch_edit_tab_form')->toHtml(),
      ));

      $this->addTab('form_section_items', array(
          'label'     => Mage::helper('quotedispatch')->__('Items'),
          'title'     => Mage::helper('quotedispatch')->__('Items'),
          //'content'   => $this->getLayout()->createBlock('quotedispatch/adminhtml_quotedispatch_edit_tab_grid')->toHtml(),
          'url'       => $this->getUrl('*/*/quoteitems', array('_current' => true)),
          'class'     => 'ajax',
      ));
      
      $this->addTab('form_section_notes', array(
          'label'     => Mage::helper('quotedispatch')->__('Internal Notes'),
          'title'     => Mage::helper('quotedispatch')->__('Internal Notes'),
          'content'   => $this->getLayout()->createBlock('quotedispatch/adminhtml_quotedispatch_edit_tab_notes')->toHtml(),
      ));

     
      return parent::_beforeToHtml();
  }
  
  
  
  
}