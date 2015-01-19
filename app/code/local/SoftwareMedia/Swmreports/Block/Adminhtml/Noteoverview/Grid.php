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
class SoftwareMedia_Swmreports_Block_Adminhtml_Noteoverview_Grid extends Mage_Adminhtml_Block_Widget_Grid {

	public function __construct() {
		parent::__construct();
		$this->setId('noteoverviewGrid');
		$this->setDefaultSort('created_time');
		$this->setDefaultDir('DESC');
		$this->setSaveParametersInSession(true);
		$this->setSubReportSize(false);
		$this->setCustomHeader('CSSR Overview Stats');
	}

	protected function _prepareCollection() {

		$collection = Mage::getModel('customernotes/notes')->getCollection();
		$collection = $this->addFilters($collection);
		$collection->getSelect()->joinLeft(
				'customer_entity', '`customer_entity`.entity_id=`main_table`.customer_id', array('email','entity_id')
			);
	
		$collection->getSelect()->joinLeft(
				'sales_flat_order', '`customer_entity`.entity_id=`sales_flat_order`.customer_id AND `sales_flat_order`.created_at > `main_table`.created_time AND (`sales_flat_order`.created_at < `main_table`.update_time OR `main_table`.update_time IS NULL)', array('increment_id','created_at')
			);


		$collection->getSelect()->group('username');

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
			$orderCount = Mage::getModel('customernotes/notes')->getCollection();
			$orderCount->addFieldToFilter('username',$col->getUsername());
			$orderCount = $this->addFilters($orderCount);
			$orderCount->getSelect()->joinLeft(
					'customer_entity', '`customer_entity`.entity_id=`main_table`.customer_id', array('email','entity_id')
				);
		
			$orderCount->getSelect()->joinRight(
					'sales_flat_order', '`customer_entity`.entity_id=`sales_flat_order`.customer_id AND `sales_flat_order`.created_at > `main_table`.created_time AND (`sales_flat_order`.created_at < `main_table`.update_time OR `main_table`.update_time IS NULL)', array('increment_id','created_at')
				);
			$orderCount->getSelect()->group('customer_id');
			
			$col->setData('talked',1);
			$col->setData('orders',count($orderCount));
			$orders = count($orderCount);
						
			//Really inneficient way to get reaches per customer
			$orderCount = Mage::getModel('customernotes/notes')->getCollection();
			$orderCount->addFieldToFilter('username',$col->getUsername());
			$orderCount->getSelect()->columns(
		        array(
		            'customer_count' => new Zend_Db_Expr('count(customer_id)')
		        ));
			$orderCount = $this->addFilters($orderCount);
			$orderCount->getSelect()->group('customer_id');
			
			$avgReach = 0;
			foreach($orderCount as $avg) {
				$avgReach += $avg->getCustomerCount();
			}
			
			$col->setData('reach',number_format($avgReach / count($orderCount),2));
			$col->setData('customers',count($orderCount));
			$customers = count($orderCount);
			
			$col->setData('retention',number_format($orders / $customers * 100,2) . '%');
			
			$count = 0;
			foreach(Mage::helper('customernotes')->getOptions() as $val) {
				$count++;
				if ($count == 1)
					continue; 
					
				//Select Count of all types of contact methods
				$orderCount = Mage::getModel('customernotes/notes')->getCollection();
				$orderCount->addFieldToFilter('username',$col->getUsername());
				$orderCount->addFieldToFilter('contact_method',$val);
				$orderCount = $this->addFilters($orderCount);
				
				$col->setData('stat_'.$count,count($orderCount));
			
			}
		}
		
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}
	
	public function addFilters($col) {
		if ($this->getRequest()->getParam('from'))
			$from = date('Y-m-d 00:00:00',strtotime($this->getRequest()->getParam('from')));
		
		if ($this->getRequest()->getParam('to'))
			$to = date('Y-m-d 23:59:59',strtotime($this->getRequest()->getParam('to')));
			
		$campaign = $this->getRequest()->getParam('campaign_id');
		$step = $this->getRequest()->getParam('step_id');
		
		$col->addFieldToFilter('contact_method',array('neq' => 'N/A'));
		
		if ($campaign)
			$col->addFieldToFilter('campaign_id',$campaign);
		if ($step)
			$col->addFieldToFilter('step_id',$step);
		if ($from)
			$col->addFieldToFilter('created_time',array('gt' => $from));
		if ($to)
			$col->addFieldToFilter('created_time',array('lt' => $to));
				
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
		
		$count = 0;
		foreach(Mage::helper('customernotes')->getOptions() as $val) {
			$count++;
			if ($count == 1)
				continue; 
						$this->addColumn('stat_'.$count, array(
							'header' => Mage::helper('outofstock')->__($val),
							'index' => 'stat_'.$count,
							'filter' => false,
							'sortable' => false
						));
			}


		$this->addColumn('customers', array(
			'header' => Mage::helper('outofstock')->__('Customers Reached'),
			'index' => 'customers',
			'filter' => false,
			'sortable' => false
		));
		
		$this->addColumn('reach', array(
			'header' => Mage::helper('outofstock')->__('Reaches Per Customer'),
			'index' => 'reach',
			'filter' => false,
			'sortable' => false
		));
		
		
		$this->addColumn('orders', array(
			'header' => Mage::helper('outofstock')->__('# of Customers Placed Order since Contacted'),
			'index' => 'orders',
			'filter' => false,
			'sortable' => false
		));
		
		$this->addColumn('retention', array(
			'header' => Mage::helper('outofstock')->__('Retention Rate'),
			'index' => 'retention',
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
