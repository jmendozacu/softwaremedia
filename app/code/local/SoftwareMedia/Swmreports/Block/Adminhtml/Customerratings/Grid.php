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
class SoftwareMedia_Swmreports_Block_Adminhtml_Customerratings_Grid extends Mage_Adminhtml_Block_Widget_Grid {

	public function __construct() {
		parent::__construct();
		$this->setId('customerratingsGrid');
		$this->setDefaultSort('created_time');
		$this->setDefaultDir('DESC');
		$this->setSaveParametersInSession(true);
		$this->setSubReportSize(false);
		$this->setCustomHeader('Customer Ratings Overview');
	}

	protected function _prepareCollection() {
		$collection = Mage::getModel('softwaremedia_ratings/rating')->getCollection();
		$collection = $this->addFilters($collection);

		
		$collection->getSelect()->joinRight(
					'admin_user', '`admin_user`.user_id=`main_table`.user_id', array('username')
				);
				
		$collection->getSelect()->columns(
		        array(
		            'sum' => new Zend_Db_Expr('FORMAT(sum(rating) / count(rating),2)'),
		            'ratings' => new Zend_Db_Expr('count(rating)')
		        ));
		
		$collection->getSelect()->group('main_table.user_id');
		/*
		$collection = Mage::getModel('catalog/product')->getCollection()
			->addAttributeToSelect('name')
			->addAttributeToSelect('brand')
			->addAttributeToSelect('package_id')
			->addAttributeToFilter('status', array('eq' => '1'))
			->addAttributeToFilter('sku', array('nlike' => '%HOME'))
			->joinField('manages_stock', 'cataloginventory/stock_item', 'use_config_manage_stock', 'product_id=entity_id', '{{table}}.manage_stock=1 AND {{table}}.use_config_manage_stock=0 AND {{table}}.is_in_stock = 0')
			->joinField('licensing', 'catalog_product_entity_int', 'value', 'entity_id=entity_id', '{{table}}.attribute_id=1455')
		;

		*/
		
		foreach($collection as $col) {
		
			$ratingsCount = Mage::getModel('softwaremedia_ratings/rating')->getCollection();
			$ratingsCount->addFieldToFilter('user_id',$col->getUserId());
			$ratingsCount->addFieldToFilter('source','Chat');
			$ratingsCount = $this->addFilters($ratingsCount);			
			$col->setData('chat',number_format(count($ratingsCount),0));	
			
			$ratingsCount = Mage::getModel('softwaremedia_ratings/rating')->getCollection();
			$ratingsCount->addFieldToFilter('user_id',$col->getUserId());
			$ratingsCount->addFieldToFilter('source','E-Mail');
			$ratingsCount = $this->addFilters($ratingsCount);			
			$col->setData('email',number_format(count($ratingsCount),0));	
			
			$ratingsCount = Mage::getModel('softwaremedia_ratings/rating')->getCollection();
			$ratingsCount->addFieldToFilter('user_id',$col->getUserId());
			$ratingsCount->addFieldToFilter('rating',1);
			$ratingsCount = $this->addFilters($ratingsCount);			
			$col->setData('bad',number_format(count($ratingsCount),0));	
			
			$ratingsCount = Mage::getModel('softwaremedia_ratings/rating')->getCollection();
			$ratingsCount->addFieldToFilter('user_id',$col->getUserId());
			$ratingsCount->addFieldToFilter('rating',5);
			$ratingsCount = $this->addFilters($ratingsCount);			
			$col->setData('good',number_format(count($ratingsCount),0));	
			
			$ratingsCount = Mage::getModel('softwaremedia_ratings/rating')->getCollection();
			$ratingsCount->addFieldToFilter('user_id',$col->getUserId());
			$ratingsCount->addFieldToFilter('rating',3);
			$ratingsCount = $this->addFilters($ratingsCount);			
			$col->setData('neutral',number_format(count($ratingsCount),0));	
			//$col->setData('sum',number_format($ratingsSum / $ratingsCount,2));
		}
		
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}
	
	public function addFilters($col) {
		if ($this->getRequest()->getParam('from'))
			$from = date('Y-m-d 00:00:00',strtotime($this->getRequest()->getParam('from')));
		
		if ($this->getRequest()->getParam('to'))
			$to = date('Y-m-d 23:59:59',strtotime($this->getRequest()->getParam('to')));

		if ($from)
			$col->addFieldToFilter('created_at',array('gt' => $from));
		if ($to)
			$col->addFieldToFilter('created_at',array('lt' => $to));
				
		$col->addFieldToFilter('main_table.user_id',array('notnull' => true));
		
		return $col;
	}

	public function toOptionHash($valueField = 'id', $labelField = 'name') {
		return $this->_toOptionHash($valueField, $labelField);
	}

	protected function _prepareColumns() {
		/*$valuesCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
			->setAttributeFilter(1455)
			->setStoreFilter(0)
			->load();

		$propOptions = array();
		if ($valuesCollection->getSize() > 0) {
			foreach ($valuesCollection as $item) {
				$propOptions[$item->getId()] = $item->getValue();
			}
		}

		$this->addColumn('set_name', array(
			'header' => Mage::helper('catalog')->__('Licensing'),
			'width' => '100px',
			'index' => 'licensing',
			'type' => 'options',
			'options' => $propOptions,
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
			'header' => Mage::helper('outofstock')->__('Product Brand'),
			'index' => 'brand',
			'type' => 'options',
			'options' => $propOptions,
		));
		*/
		
		$adminUserModel = Mage::getModel('admin/user');
		$userCollection = $adminUserModel->getCollection()->addFieldToFilter('is_active',1); 
		
		$referer_arr = array();
		
		foreach($userCollection as $user) {
			$referer_arr[$user->getUsername()] = $user->getUsername();
		}
		
		$this->addColumn('username', array(
			'header' => Mage::helper('sales')->__('Admin User'),
			'index' => 'username',
			'width' => '70px',
			'filter' => false,
			'sortable' => false
		));
		$this->addColumn('ratings', array(
			'header' => Mage::helper('sales')->__('# of Ratings'),
			'index' => 'ratings',
			'filter' => false,
			'sortable' => false
		));
		
		$this->addColumn('chat', array(
			'header' => Mage::helper('sales')->__('From Chat'),
			'index' => 'chat',
			'filter' => false,
			'sortable' => false
		));
		
		$this->addColumn('email', array(
			'header' => Mage::helper('sales')->__('From E-Mail'),
			'index' => 'email',
			'filter' => false,
			'sortable' => false
		));
		
		
		$this->addColumn('bad', array(
			'header' => Mage::helper('sales')->__('# of Bad'),
			'index' => 'bad',
			'filter' => false,
			'sortable' => false
		));
		
		$this->addColumn('neutral', array(
			'header' => Mage::helper('sales')->__('# of Neutral'),
			'index' => 'neutral',
			'filter' => false,
			'sortable' => false
		));
		
		$this->addColumn('good', array(
			'header' => Mage::helper('sales')->__('# of Good'),
			'index' => 'good',
			'filter' => false,
			'sortable' => false
		));
		
		
		$this->addColumn('sum', array(
			'header' => Mage::helper('sales')->__('Avg. Rating'),
			'index' => 'sum',
			'filter' => false,
			'sortable' => false
		));
					
		/*		
		$this->addColumn('last_order', array(
			'header' => Mage::helper('outofstock')->__('Order After Contact'),
			'index' => 'increment_id',
			'filter_index' => 'sales_flat_order.increment_id',
			'type' => 'options',
			'options' => array('Yes' => 'Yes','No' => 'No'),
			'renderer' => 'OCM_Catalog_Block_Widget_Orenderer'
		));
		
		$this->addColumn('increment_id', array(
			'header' => Mage::helper('outofstock')->__('Order ID'),
			'index' => 'increment_id',
			'filter_index' => 'sales_flat_order.increment_id'
		));
		
		$this->addColumn('created_at', array(
			'header' => Mage::helper('outofstock')->__('Order Time'),
			'index' => 'created_at',
			'type'  => 'datetime',
			'filter_index' => 'sales_flat_order.created_at'
		));
		
		$this->addColumn('action', array(
				'header' => Mage::helper('sales')->__('Action'),
				'width' => '50px',
				'type' => 'action',
				'getter' => 'getCustomerId',
				'actions' => array(
					array(
						'caption' => Mage::helper('sales')->__('View'),
						'url' => array('base' => 'adminhtml/customer/edit'),
						'field' => 'id'
					)
				),
				'filter' => false,
				'sortable' => false,
				'index' => 'stores',
				'is_system' => true,
			));
		*/
		/*

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
		*/
		$this->addExportType('*/*/exportCsv', Mage::helper('swmreports')->__('CSV'));
		parent::_prepareColumns();
	}

	public function getRowUrl($item) {
		return false;
	}

}
