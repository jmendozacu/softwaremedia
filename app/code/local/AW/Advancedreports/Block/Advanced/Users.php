<?php

class AW_Advancedreports_Block_Advanced_Users extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'advancedreports';
        $this->_controller = 'advanced_users';
        $this->_headerText = Mage::helper('advancedreports')->__('Users Activity Report');
        parent::__construct();
        $this->_removeButton('add');
    }
}