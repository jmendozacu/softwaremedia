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

		$this->setCustomHeader('Extra Orders');
	}

	protected function _prepareCollection() {

		$collection = Mage::getModel('sales/order_item')->getCollection()
			->addAttributeToSelect('qty_invoiced')
			->addAttributeToSelect('sku')
			->addAttributeToSelect('base_row_invoiced')
			->addAttributeToSelect('base_cost')
			->addAttributeToSelect('created_at')
			->join('sales/order', 'entity_id=order_id AND status = "complete"', array('increment_id', 'base_grand_total', 'customer_firstname' => 'customer_firstname', 'customer_lastname' => 'customer_lastname', 'customer_email' => 'customer_email'), null, 'left')
//			->addAttributeToFilter('customer_email', array('nlike' => '%@softwaremedia.com'))
			->addAttributeToFilter('qty_invoiced', array('gt' => 0))
			->addAttributeToFilter('base_row_invoiced', array('gt' => 0))
		;

		$collection->getSelect()->columns(
			array(
				'total_cost' => '(main_table.base_cost * main_table.qty_invoiced)',
				'profit' => '(main_table.base_row_invoiced - (main_table.base_cost * main_table.qty_invoiced))',
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
			'index' => 'base_grand_total',
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

}
