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
class SoftwareMedia_Reports_Block_Adminhtml_Reports extends Mage_Adminhtml_Block_Widget_Grid_Container {

	public function __construct() {
		$this->_controller = 'adminhtml_reports';
		$this->_blockGroup = 'reports';
		$this->_headerText = Mage::helper('reports')->__('SoftwareMedia Report');
		parent::__construct();
		$this->_removeButton('add');
	}

}
