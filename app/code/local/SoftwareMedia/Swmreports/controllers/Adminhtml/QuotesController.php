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
class SoftwareMedia_Swmreports_Adminhtml_QuotesController extends Mage_Adminhtml_Controller_Action {

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
		$fileName = 'quotes.csv';
		if (!$this->getRequest()->getParam('from')) {
			Mage::getSingleton('core/session')->addError('Please enter a from date');
			$this->_redirect('*/*/index');
			return;
		}
			
		$grid = $this->getLayout()->createBlock('swmreports/adminhtml_quotes_grid');
		
		$path = Mage::getBaseDir('var') . DS . 'export' . DS;
		$name = md5(microtime());
		$file = $path . DS . $name . '.csv';
		
		$myfile = fopen($file, "w");
		fwrite($myfile, $grid->getCsv());
		fclose($myfile);
		
		$this->_prepareDownloadResponse($fileName, array(
			'type' => 'filename',
			'value' => $file,
			'rm' => true // can delete file after use
		));
	}

}
