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
		$fileName = 'pt_compare.csv';
		$content = '"Amazon Orders"' . PHP_EOL;
		$content .= $this->getLayout()->createBlock('swmreports/adminhtml_peachtree_amazon_grid')->getCsv();
		$content .= PHP_EOL . PHP_EOL;
		$content .= '"Buy.com Orders"' . PHP_EOL;
		$content .= $this->getLayout()->createBlock('swmreports/adminhtml_peachtree_buy_grid')->getCsv();
		$content .= PHP_EOL . PHP_EOL;
		$content .= '"BestBuy Orders"' . PHP_EOL;
		$content .= $this->getLayout()->createBlock('swmreports/adminhtml_peachtree_bestbuy_grid')->getCsv();
		$content .= PHP_EOL . PHP_EOL;
		$content .= '"Online Orders"' . PHP_EOL;
		$content .= $this->getLayout()->createBlock('swmreports/adminhtml_peachtree_online_grid')->getCsv();
		$content .= PHP_EOL . PHP_EOL;
		$content .= '"Pending Orders"' . PHP_EOL;
		$content .= $this->getLayout()->createBlock('swmreports/adminhtml_peachtree_pending_grid')->getCsv();
		$content .= PHP_EOL . PHP_EOL;
		$content .= '"Extra Orders"' . PHP_EOL;
		$content .= $this->getLayout()->createBlock('swmreports/adminhtml_peachtree_extra_grid')->getCsv();
		$content .= PHP_EOL . PHP_EOL;
		$content .= '"Discount Orders"' . PHP_EOL;
		$content .= $this->getLayout()->createBlock('swmreports/adminhtml_peachtree_discount_grid')->getCsv();
		$content .= PHP_EOL . PHP_EOL;
		$content .= '"Canceled/Closed Orders"' . PHP_EOL;
		$content .= $this->getLayout()->createBlock('swmreports/adminhtml_peachtree_closed_grid')->getCsv();
		$content .= PHP_EOL . PHP_EOL;
		$content .= '"Total Orders"' . PHP_EOL;
		$content .= $this->getLayout()->createBlock('swmreports/adminhtml_peachtree_total_grid')->getCsv();

		$this->_prepareDownloadResponse($fileName, $this->getLayout()->createBlock('swmreports/adminhtml_peachtree_grid')->getCsvFileFromContent($content));
	}

}
