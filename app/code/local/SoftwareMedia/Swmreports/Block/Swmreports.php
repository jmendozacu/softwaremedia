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
class SoftwareMedia_Swmreports_Block_Swmreports extends Mage_Core_Block_Template {

	public function _prepareLayout() {
		parent::_prepareLayout();
	}

	public function getReports() {
		if (!$this->hasData('swmreports')) {
			$this->setData('swmreports', Mage::registry('swmreports'));
		}
		return $this->getData('swmreports');
	}

}
