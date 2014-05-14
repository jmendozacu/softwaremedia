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
class SoftwareMedia_Swmreports_Block_Peachtree extends Mage_Core_Block_Template {

	public function _prepareLayout() {
		parent::_prepareLayout();
	}

	public function getReports() {
		if (!$this->hasData('peachtree_report')) {
			$this->setData('peachtree_report', Mage::registry('peachtree_report'));
		}
		return $this->getData('peachtree_report');
	}

}
