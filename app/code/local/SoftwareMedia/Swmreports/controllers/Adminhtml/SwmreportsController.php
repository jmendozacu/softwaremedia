<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ReportsController
 *
 * @author david
 */
class SoftwareMedia_Swmreports_Adminhtml_SwmreportsController extends Mage_Adminhtml_Controller_Action {

	protected function _initAction() {
		$this->loadLayout();
		return $this;
	}

	public function indexAction() {
		$this->_initAction()->renderLayout();
	}

	public function exportCsvAction() {
		// TODO
	}

	public function exportXmlAction() {
		// TODO
	}

}
