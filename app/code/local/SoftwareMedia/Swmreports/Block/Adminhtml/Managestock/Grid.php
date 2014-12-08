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
class SoftwareMedia_Swmreports_Block_Adminhtml_Managestock_Grid extends Mage_Adminhtml_Block_Widget_Grid {

	public function __construct() {
		parent::__construct();
		$this->setId('managestockGrid');
		$this->setDefaultSort('sku');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
		$this->setSubReportSize(false);
	}

	protected function _prepareCollection() {

		$collection = Mage::getModel('catalog/product')->getCollection()
			->addAttributeToSelect('name')
			->addAttributeToSelect('brand')
			->addAttributeToSelect('license_nonlicense_dropdown')
			->addAttributeToSelect('attribute_set_id')
			->addAttributeToFilter('status', array('eq' => '1'))
			->addAttributeToFilter('sku', array('nlike' => '%FBA%'))
			->addAttributeToFilter('sku', array('nlike' => '%HOME'))
			->joinField('manages_stock', 'cataloginventory/stock_item', 'use_config_manage_stock', 'product_id=entity_id', '{{table}}.use_config_manage_stock=1 or {{table}}.manage_stock=0')
		;

		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	public function toOptionHash($valueField = 'id', $labelField = 'name') {
		return $this->_toOptionHash($valueField, $labelField);
	}

	protected function _prepareColumns() {

		$this->addColumn('sku', array(
			'header' => Mage::helper('managestock')->__('Product SKU'),
			'index' => 'sku'
		));

		$this->addColumn('license_nonlicense_dropdown', array(
			'header' => Mage::helper('catalog')->__('License Product?'),
			'width' => '100px',
			'index' => 'license_nonlicense_dropdown',
			'type' => 'options',
			'options' => array(
				1210 => 'Yes',
				1211 => 'No')
		));

		$valuesCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
			->setAttributeFilter(1031)
			->setStoreFilter(0)
			->load();

		$propOptions = array();
		if ($valuesCollection->getSize() > 0) {
			foreach ($valuesCollection as $item) {
				$propOptions[$item->getId()] = $item->getValue();
			}
		}

		$this->addColumn('brand', array(
			'header' => Mage::helper('managestock')->__('Product Brand'),
			'index' => 'brand',
			'type' => 'options',
			'options' => $propOptions,
		));

		$this->addColumn('name', array('header' => Mage::helper('managestock')->__('Product Name'),
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

//		$this->addExportType('*/*/exportCsv', Mage::helper('swmreports')->__('CSV'));
//		$this->addExportType('*/*/exportXml', Mage::helper('swmreports')->__('XML'));
		parent::_prepareColumns();
	}

	public function getRowUrl($item) {
		return false;
	}

}
