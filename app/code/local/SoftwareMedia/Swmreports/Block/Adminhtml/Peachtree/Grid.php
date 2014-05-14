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
class SoftwareMedia_Swmreports_Block_Adminhtml_Peachtree_Grid extends Mage_Adminhtml_Block_Widget_Grid {

	public function __construct() {
		parent::__construct();
		$this->setId('peachtreeGrid');
		$this->setSaveParametersInSession(true);
		$this->setSubReportSize(false);
		$this->setCountTotals();
		$this->setDefaultLimit(200);
		$this->setDefaultSort('sku');
		$this->setDefaultDir('asc');
	}

	protected function _addColumnFilterToCollection($column) {
		if ($this->getCollection()) {
			$field = ( $column->getFilterIndex() ) ? $column->getFilterIndex() : $column->getIndex();
			if ($column->getFilterConditionCallback()) {
				call_user_func($column->getFilterConditionCallback(), $this->getCollection(), $column);
			} else {
				$cond = $column->getFilter()->getCondition();
				if ($field && isset($cond)) {
					if ($field == 'created_date') {
						$this->getCollection()->addFieldToFilter('main_table.created_at', $cond);
					} elseif ($field == 'profit') {
						$this->getCollection()->addFieldToFilter('(main_table.base_row_invoiced - (main_table.base_cost * main_table.qty_invoiced))', $cond);
					} elseif ($field == 'total_cost') {
						$this->getCollection()->addFieldToFilter('(main_table.base_cost * main_table.qty_invoiced)', $cond);
					} else {
						$this->getCollection()->addFieldToFilter($field, $cond);
					}
				}
			}
		}
		return $this;
	}

	public function getTotals() {
		$totals = new Varien_Object();
		$orders = array();
		$fields = array(
			'qty_invoiced' => 0, //actual column index, see _prepareColumns()
			'base_row_invoiced' => 0,
			'total_cost' => 0,
			'profit' => 0,
		);

		foreach ($this->getCollection() as $item) {
			foreach ($fields as $field => $value) {
				$fields[$field]+=$item->getData($field);
			}

			$order_id = $item->getData('increment_id');
			if (!in_array($order_id, $orders)) {
				$orders[$order_id] = $order_id;
			}
		}

		//First column in the grid
		$fields['increment_id'] = 'Totals: ' . count($orders);
		$totals->setData($fields);
		return $totals;
	}

	public function toOptionHash($valueField = 'id', $labelField = 'name') {
		return $this->_toOptionHash($valueField, $labelField);
	}

	protected function _prepareCollection() {
		$this->addExportType('*/*/exportCsv', Mage::helper('swmreports')->__('CSV'));
		parent::_prepareCollection();
	}

	public function getCsvFile() {
		$this->_isExport = true;
		$this->_prepareGrid();

		$io = new Varien_Io_File();

		$path = Mage::getBaseDir('var') . DS . 'export' . DS;
		$name = md5(microtime());
		$file = $path . DS . $name . '.csv';

		$io->setAllowCreateFolders(true);
		$io->open(array('path' => $path));
		$io->streamOpen($file, 'w+');
		$io->streamLock(true);
		$io->streamWriteCsv($this->_getExportHeaders());

		$this->_exportIterateCollection('_exportCsvItem', array($io));

		if ($this->getCountTotals()) {
			$io->streamWriteCsv($this->_getExportTotals());
		}

		$io->streamUnlock();
		$io->streamClose();

		return array(
			'type' => 'filename',
			'value' => $file,
			'rm' => true // can delete file after use
		);
	}

	public function getCsvFileFromContent($content) {
		$this->_isExport = true;
		$this->_prepareGrid();

		$io = new Varien_Io_File();

		$path = Mage::getBaseDir('var') . DS . 'export' . DS;
		$name = md5(microtime());
		$file = $path . DS . $name . '.csv';

		$io->setAllowCreateFolders(true);
		$io->open(array('path' => $path));
		$io->streamOpen($file, 'w+');
		$io->streamLock(true);
		$io->streamWrite($content);

//		$this->_exportIterateCollection('_exportCsvItem', array($io));
//
//		if ($this->getCountTotals()) {
//			$io->streamWriteCsv($this->_getExportTotals());
//		}

		$io->streamUnlock();
		$io->streamClose();

		return array(
			'type' => 'filename',
			'value' => $file,
			'rm' => true // can delete file after use
		);
	}

	protected function _prepareColumns() {

		// Order ID
		$this->addColumn('Order_ID', array(
			'header' => Mage::helper('peachtree')->__('Order ID'),
			'index' => 'increment_id',
		));

		// Customer Name
		$this->addColumn('Customer_Name', array(
			'header' => Mage::helper('peachtree')->__('Name'),
			'index' => array('customer_firstname', 'customer_lastname'),
			'type' => 'concat',
			'separator' => ' ',
		));

		$this->addColumn('Item', array(
			'header' => Mage::helper('peachtree')->__('Item ID'),
			'index' => 'sku'
		));

		$this->addColumn('Qty_Invoiced', array(
			'header' => Mage::helper('peachtree')->__('Qty Invoiced'),
			'index' => 'qty_invoiced',
			'type' => 'number'
		));

		$this->addColumn('Amount', array(
			'header' => Mage::helper('peachtree')->__('Amount'),
			'index' => 'base_row_invoiced',
			'type' => 'currency',
			'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
		));

		$this->addColumn('Cost', array(
			'header' => Mage::helper('peachtree')->__('Cost'),
			'index' => 'total_cost',
			'type' => 'currency',
			'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
		));

		$this->addColumn('Profit', array(
			'header' => Mage::helper('peachtree')->__('Profit'),
			'index' => 'profit',
			'type' => 'currency',
			'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
		));

		$this->addColumn('Created_Date', array(
			'header' => Mage::helper('peachtree')->__('Created Date'),
			'index' => 'created_date',
			'type' => 'date'
		));

		parent::_prepareColumns();
	}

	public function getRowUrl($item) {
		return false;
	}

}
