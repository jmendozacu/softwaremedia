<?php

class AW_Advancedreports_Block_Advanced_Country extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'advancedreports';
        $this->_controller = 'advanced_country';
        $this->_headerText = Mage::helper('advancedreports')->__('Sales by Country');
        parent::__construct();
        $this->_removeButton('add');
    }
}