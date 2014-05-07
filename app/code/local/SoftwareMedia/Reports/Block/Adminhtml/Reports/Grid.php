<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Grid
 *
 * @author david
 */
class SoftwareMedia_Reports_Block_Adminhtml_Reports_Grid extends Mage_Adminhtml_Block_Report_Grid {

	public function __construct() {
		parent::__construct();
		$this->setId('reportsGrid');
		$this->setDefaultSort('sku');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
		$this->setSubReportSize(false);
	}

	protected function _prepareCollection() {
		parent::_prepareCollection();
		$this->getCollection()->initReport('SoftwareMedia/reports');
		return $this;
	}

	protected function _prepareColumns() {
		$this->addColumn('SKU', array(
			'header' => Mage::helper('reports')->__('SKU'),
			'index' => 'sku',
		));
		$this->addColumn('SKU', array(
			'header' => Mage::helper('reports')->__('SKU'),
			'index' => 'sku',
		));
		$this->addColumn('SKU', array(
			'header' => Mage::helper('reports')->__('SKU'),
			'index' => 'sku',
		));
		$this->addExportType('*/*/exportCsv', Mage::helper('reports')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('reports')->__('XML'));
		parent::_prepareColumns();
	}

	public function getRowUrl($item) {
		return false;
	}

}
