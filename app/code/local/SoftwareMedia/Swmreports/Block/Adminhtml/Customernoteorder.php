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
class SoftwareMedia_Swmreports_Block_Adminhtml_Customernoteorder extends Mage_Adminhtml_Block_Widget_Grid_Container {

	public function __construct() {
		$this->_controller = 'adminhtml_customernote';
		$this->_blockGroup = 'customernote';
		$this->_headerText = Mage::helper('outofstock')->__('Customer Notes Report');
		parent::__construct();
		$this->_removeButton('add');
	}

}
