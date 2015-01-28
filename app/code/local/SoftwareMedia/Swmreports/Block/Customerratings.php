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
class SoftwareMedia_Swmreports_Block_Customerratings extends Mage_Core_Block_Template {

	public function _prepareLayout() {
		parent::_prepareLayout();
	}

	public function getReports() {
		if (!$this->hasData('ratings_overview_report')) {
			$this->setData('ratings_overview_report', Mage::registry('ratings_overview_report'));
		}
		return $this->getData('ratings_overview_report');
	}

}
