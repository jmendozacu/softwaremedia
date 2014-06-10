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
class SoftwareMedia_Swmreports_Block_Outofstock extends Mage_Core_Block_Template {

	public function _prepareLayout() {
		parent::_prepareLayout();
	}

	public function getReports() {
		if (!$this->hasData('out_of_stock_report')) {
			$this->setData('out_of_stock_report', Mage::registry('out_of_stock_report'));
		}
		return $this->getData('out_of_stock_report');
	}

}
