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
class SoftwareMedia_Swmreports_Block_Customernoteorder extends Mage_Core_Block_Template {

	public function _prepareLayout() {
		parent::_prepareLayout();
	}

	public function getReports() {
		if (!$this->hasData('customer_notes_report')) {
			$this->setData('customer_notes_report', Mage::registry('customer_notes_report'));
		}
		return $this->getData('customer_notes_report');
	}

}
