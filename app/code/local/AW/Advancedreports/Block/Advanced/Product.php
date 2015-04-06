<?php

class AW_Advancedreports_Block_Advanced_Product extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'advancedreports';
        $this->_controller = 'advanced_product';
        $this->_headerText = Mage::helper('advancedreports')->__('Sales by Product');
        parent::__construct();
        $this->_removeButton('add');
    }
}