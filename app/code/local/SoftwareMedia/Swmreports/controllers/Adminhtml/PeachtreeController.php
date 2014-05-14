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
class SoftwareMedia_Swmreports_Adminhtml_PeachtreeController extends Mage_Adminhtml_Controller_Action {

	protected function _initAction() {
		$this->loadLayout();
		return $this;
	}

	public function indexAction() {
		$this->_initAction()->renderLayout();
	}

	/**
	 * Export order grid to CSV format
	 */
	public function exportCsvAction() {
		$fileName = 'peachtree_report.csv';
		$grid = $this->getLayout()->createBlock('swmreports/adminhtml_peachtree');
		$this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
	}

	public function exportXmlAction() {
		$fileName = 'peachtree_report.xml';
		$grid = $this->getLayout()->createBlock('swmreports/adminhtml_peachtree');
		$this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
	}

}
