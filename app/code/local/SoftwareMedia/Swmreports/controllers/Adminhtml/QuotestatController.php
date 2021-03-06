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
class SoftwareMedia_Swmreports_Adminhtml_QuotestatController extends Mage_Adminhtml_Controller_Action {
		protected function _isAllowed()
    {
        return true;
    }
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
	
		if (!$this->getRequest()->getParam('to'))
			$to = date('Y/m/d');
		else 
			$to = date('Y/m/d',strtotime($this->getRequest()->getParam('to')));
			
		$date = date('Y/m/d',strtotime($this->getRequest()->getParam('from'))) . "-" . $to;
		$fileName = 'quotestat-(' . $date . ').csv';
		if (!$this->getRequest()->getParam('from')) {
			Mage::getSingleton('core/session')->addError('Please enter a from date');
			$this->_redirect('*/*/index');
			return;
		}
			
		$grid = $this->getLayout()->createBlock('swmreports/adminhtml_quotestat_grid');
		
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
