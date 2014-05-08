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
class SoftwareMedia_Swmreports_Block_Adminhtml_Swmreports_Grid extends Mage_Adminhtml_Block_Widget_Grid {

	public function __construct() {
		parent::__construct();
		$this->setId('swmreportsGrid');
		$this->setDefaultSort('sku');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
		$this->setSubReportSize(false);
	}

	protected function _prepareCollection() {
		if ($this->getRequest()->getParam('website')) {
			$storeIds = Mage::app()->getWebsite($this->getRequest()->getParam('website'))->getStoreIds();
			$storeId = array_pop($storeIds);
		} else if ($this->getRequest()->getParam('group')) {
			$storeIds = Mage::app()->getGroup($this->getRequest()->getParam('group'))->getStoreIds();
			$storeId = array_pop($storeIds);
		} else if ($this->getRequest()->getParam('store')) {
			$storeId = (int) $this->getRequest()->getParam('store');
		} else {
			$storeId = '';
		}

		$collection = Mage::getModel('catalog/product')->getCollection()
			->addAttributeToSelect('name')
			->addAttributeToFilter('status', array('eq' => '1'))
			->joinField('manages_stock', 'cataloginventory/stock_item', 'use_config_manage_stock', 'product_id=entity_id', '{{table}}.use_config_manage_stock=1 or {{table}}.manage_stock=1')
			->joinAttribute('brand', 'catalog_product/brand', 'entity_id', null, 'left', $storeId)
			->setStoreId($storeId)
			->setOrder('sku', Varien_Data_Collection::SORT_ORDER_ASC)
		;

		if ($storeId) {
			$collection->addStoreFilter($storeId);
		}

		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns() {

		$this->addColumn('sku', array(
			'header' => Mage::helper('swmreports')->__('Product SKU'),
			'index' => 'sku'
		));

		$this->addColumn('brand', array(
			'header' => Mage::helper('swmreports')->__('Product Brand'),
			'index' => 'brand'
		));
		$this->addColumn('name', array('header' => Mage::helper('swmreports')->__('Product Name'),
			'index' => 'name'
		));

		$this->addColumn('action', array('header' => Mage::helper('catalog')->__('Action'),
			'width' => '50px',
			'type' => 'action',
			'getter' => 'getId',
			'actions' => array(
				array(
					'caption' => Mage::helper('catalog')->__('Edit'),
					'url' => array(
						'base' => 'adminhtml/catalog_product/edit',
						'params' => array('id' => $this->getRequest()->getParam('product_id'))
					),
					'field' => 'id'
				)
			),
			'filter' => false,
			'sortable' => false,
			'index' => 'id',
		));

		$this->addExportType('*/*/exportCsv', Mage::helper('swmreports')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('swmreports')->__('XML'));
		parent::_prepareColumns(
		);
	}

	public function getRowUrl($item) {
		return false;
	}

}
