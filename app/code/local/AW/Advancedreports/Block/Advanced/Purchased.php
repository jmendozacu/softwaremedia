<?php

class AW_Advancedreports_Block_Advanced_Purchased extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'advancedreports';
        $this->_controller = 'advanced_purchased';
        $this->_headerText = Mage::helper('advancedreports')->__('Products by Customer');
        parent::__construct();
        $this->_removeButton('add');
    }
}