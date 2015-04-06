<?php

class AW_Advancedreports_Block_Report_Sales_Salesoverview extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'advancedreports';
        $this->_controller = 'report_sales_salesoverview';
        parent::__construct();
        $this->_headerText = Mage::helper('reports')->__('Sales Overview');
        $this->_removeButton('add');
    }
}
