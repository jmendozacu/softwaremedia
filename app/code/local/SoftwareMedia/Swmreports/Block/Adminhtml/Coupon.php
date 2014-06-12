<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Reports
 *
 * @author david
 */
class SoftwareMedia_Swmreports_Block_Adminhtml_Coupon extends Mage_Adminhtml_Block_Widget_Grid_Container {

	public function __construct() {
		$this->_controller = 'adminhtml_coupon';
		$this->_blockGroup = 'coupon';
		$this->_headerText = Mage::helper('coupon')->__('Coupon Order Report');
		parent::__construct();
		$this->_removeButton('add');
	}

	public function getFilterUrl() {
		$this->getRequest()->setParam('filter', null);
		return $this->getUrl('*/*/coupons', array('_current' => true));
	}

}
