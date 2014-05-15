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
class SoftwareMedia_Swmreports_Block_Adminhtml_Peachtree_Extra_Grid extends SoftwareMedia_Swmreports_Block_Adminhtml_Peachtree_Grid {

	public function __construct() {
		parent::__construct();

		$this->setId('peachtreeExtraGrid');
		$this->setCustomHeader('Extra Orders');
	}

	public function getTotals() {
		$totals = new Varien_Object();
		$orders = array();
		$fields = array(
			'base_grand_total' => 0,
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

	protected function _prepareCollection() {

		$collection = Mage::getModel('sales/order')->getCollection();
		$collection->join('sales/order_item', 'order_id=entity_id', null, null, 'left');
		$collection->getSelect()->group(array('entity_id'));
		$collection->addAttributeToFilter('status', array('eq' => 'complete'));

		$collection->getSelect()->columns(
			array(
				'extra_total' => 'main_table.base_grand_total - SUM(`sales/order_item`.base_row_invoiced)', // Tax:  - main_table.tax_invoiced
				'total_cost' => '0',
				'profit' => '0',
				'created_date' => 'main_table.created_at',
			)
		);

		$this->setCollection($collection);
		return parent::_prepareCollection();
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

//		$this->addColumn('Item', array(
//			'header' => Mage::helper('peachtree')->__('Item ID'),
//			'index' => 'sku'
//		));
//		$this->addColumn('Qty_Invoiced', array(
//			'header' => Mage::helper('peachtree')->__('Qty Invoiced'),
//			'index' => 'qty_invoiced',
//			'type' => 'number'
//		));

		$this->addColumn('Amount', array(
			'header' => Mage::helper('peachtree')->__('Amount'),
			'index' => 'extra_total',
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

		Mage_Adminhtml_Block_Widget_Grid::_prepareColumns();
	}

}
