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
class SoftwareMedia_Swmreports_Block_Adminhtml_Peachtree_Pending_Grid extends SoftwareMedia_Swmreports_Block_Adminhtml_Peachtree_Grid {

	public function __construct() {
		parent::__construct();

		$this->setCustomHeader('Pending Orders');
	}

	protected function _prepareCollection() {

		$collection = Mage::getModel('sales/order_item')->getCollection()
			->addAttributeToSelect('qty_invoiced')
			->addAttributeToSelect('sku')
			->addAttributeToSelect('base_row_invoiced')
			->addAttributeToSelect('base_cost')
			->addAttributeToSelect('created_at')
			->join('sales/order', 'entity_id=order_id AND status = "pending"', array('increment_id', 'customer_firstname' => 'customer_firstname', 'customer_lastname' => 'customer_lastname', 'customer_email' => 'customer_email'), null, 'left')
			->addAttributeToFilter('customer_email', array('nlike' => '%@softwaremedia.com'))
			->addAttributeToFilter('qty_invoiced', array('gt' => 0))
			->addAttributeToFilter('base_row_invoiced', array('gt' => 0))
			->addAttributeToFilter('sku', array('nlike' => 'FREIGHT%'))
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

}
