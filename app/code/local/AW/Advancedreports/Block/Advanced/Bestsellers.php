<?php

class AW_Advancedreports_Block_Advanced_Bestsellers extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'advancedreports';
        $this->_controller = 'advanced_bestsellers';
        $this->_headerText = Mage::helper('advancedreports')->__('Bestsellers');
        parent::__construct();
        $this->_removeButton('add');
    }
}