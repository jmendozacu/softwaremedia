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
class SoftwareMedia_Swmreports_Block_Adminhtml_Peachtree_Amazon_Grid extends Mage_Adminhtml_Block_Widget_Grid {

	public function __construct() {
		parent::__construct();
		$this->setId('peachtreeGrid');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
		$this->setSubReportSize(false);
		$this->setCountTotals();
	}

	public function getTotals() {
		$totals = new Varien_Object();
		$fields = array(
			'qty_invoiced' => 0, //actual column index, see _prepareColumns()
			'base_row_invoiced' => 0,
			'base_cost' => 0,
		);

		foreach ($this->getCollection() as $item) {
			foreach ($fields as $field => $value) {
				$fields[$field]+=$item->getData($field);
			}
		}

		//First column in the grid
		$fields['customer_firstname'] = 'Totals';
		$totals->setData($fields);
		return $totals;
	}

	protected function _prepareCollection() {

		$collection = Mage::getModel('sales/order_item')->getCollection()
			->addAttributeToSelect('qty_invoiced')
			->addAttributeToSelect('sku')
			->addAttributeToSelect('base_row_invoiced')
			->addAttributeToSelect('base_cost')
			->addAttributeToSelect('created_at')
			->join('sales/order', 'entity_id=order_id', array('customer_firstname' => 'customer_firstname', 'customer_lastname' => 'customer_lastname', 'customer_email' => 'customer_email'), null, 'left')
			->addAttributeToFilter('customer_email', array('eq' => 'amazon@softwaremedia.com'))
			->addAttributeToFilter('qty_invoiced', array('gt' => 0))
		;

//		foreach ($collection as $key => $item) {
//			die(var_dump($item));
//		}

		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	public function toOptionHash($valueField = 'id', $labelField = 'name') {
		return $this->_toOptionHash($valueField, $labelField);
	}

	protected function _prepareColumns() {

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
			'type' => 'currency'
		));

		// TODO: Multiply cost with qty
		$this->addColumn('Cost', array(
			'header' => Mage::helper('peachtree')->__('Cost'),
			'index' => 'base_cost',
			'type' => 'currency'
		));

		$this->addColumn('Created_Date', array(
			'header' => Mage::helper('peachtree')->__('Created Date'),
			'index' => 'created_at',
			'type' => 'date'
		));

		parent::_prepareColumns();
	}

	public function getRowUrl($item) {
		return false;
	}

}
