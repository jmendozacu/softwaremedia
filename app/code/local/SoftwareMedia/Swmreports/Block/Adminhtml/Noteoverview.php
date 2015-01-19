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
class SoftwareMedia_Swmreports_Block_Adminhtml_Noteoverview extends Mage_Adminhtml_Block_Widget_Grid_Container {

	public function __construct() {
		$this->_controller = 'adminhtml_noteoverview';
		$this->_blockGroup = 'noteoverview';
		$this->_headerText = Mage::helper('outofstock')->__('Customer Notes Overview');
		parent::__construct();
		$this->_removeButton('add');
	}

}
