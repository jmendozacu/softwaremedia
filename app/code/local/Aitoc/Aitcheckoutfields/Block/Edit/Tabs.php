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
class Aitoc_Aitcheckoutfields_Block_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('aitcheckoutfields_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('catalog')->__('Attribute Information'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('main', array(
            'label'     => Mage::helper('catalog')->__('Properties'),
            'title'     => Mage::helper('catalog')->__('Properties'),
            'content'   => $this->getLayout()->createBlock('aitcheckoutfields/edit_tab_main')->toHtml(),
            'active'    => true
        ));


        $this->addTab('labels', array(
            'label'     => Mage::helper('catalog')->__('Manage Label / Options'),
            'title'     => Mage::helper('catalog')->__('Manage Label / Options'),
            'content'   => $this->getLayout()->createBlock('aitcheckoutfields/edit_tab_options')->toHtml(),
        ));
        
        $this->addTab('websites', array(
            'label'     => Mage::helper('catalog')->__('Websites / Store Views'),
            'title'     => Mage::helper('catalog')->__('Websites / Store Views'),
            'content'   => $this->getLayout()->createBlock('aitcheckoutfields/edit_tab_websites')->toHtml(),
        ));
        
        $this->addTab('customergroups', array(
            'label'     => Mage::helper('catalog')->__('Customer Groups'),
            'title'     => Mage::helper('catalog')->__('Customer Groups'),
            'content'   => $this->getLayout()->createBlock('aitcheckoutfields/edit_tab_customergroups')->toHtml(),
        ));
        
        $this->addTab('categories', array(
            'label'     => Mage::helper('catalog')->__('Categories'),
            'url'       => $this->getUrl('*/*/categories', array('_current' => true)),
            'class'     => 'ajax',
        ));

        $this->addTab('related', array(
            'label'     => Mage::helper('catalog')->__('Related Products'),
            'url'       => $this->getUrl('*/*/related', array('_current' => true)),
            'class'     => 'ajax',
        ));        
        
        
        return parent::_beforeToHtml();
    }

}