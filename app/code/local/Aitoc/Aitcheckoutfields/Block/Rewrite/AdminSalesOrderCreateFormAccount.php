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
class Aitoc_Aitcheckoutfields_Block_Rewrite_AdminSalesOrderCreateFormAccount  extends Mage_Adminhtml_Block_Sales_Order_Create_Form_Account
{
    protected function _toHtml()
    {
    	$html = parent::_toHtml();
    	$fBlock = $this->getLayout()->createBlock('aitcheckoutfields/ordercreate_form')->toHtml();
    	return $html.$fBlock;
    }
}