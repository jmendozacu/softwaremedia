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
class SoftwareMedia_Swmreports_Block_Adminhtml_Customernoteorder_Grid extends Mage_Adminhtml_Block_Widget_Grid {

	public function __construct() {
		parent::__construct();
		$this->setId('customernotesGrid');
		$this->setDefaultSort('created_time');
		$this->setDefaultDir('DESC');
		$this->setSaveParametersInSession(true);
		$this->setSubReportSize(false);
		$this->setCustomHeader('CSSR Detailed Order');
	}

	protected function _prepareCollection() {
		$collection = Mage::getModel('sales/order')->getCollection();
		$collection->getSelect()->joinLeft(
				'customer_entity', '`customer_entity`.entity_id=`main_table`.customer_id', array('email')
			);
		$collection->getSelect()->joinInner('magecon_customer_notes','`magecon_customer_notes`.customer_id=`main_table`.customer_id AND `main_table`.created_at > `magecon_customer_notes`.created_time AND (`main_table`.created_at < `magecon_customer_notes`.update_time OR `magecon_customer_notes`.update_time IS NULL)',array('note_id','user_id','update_time','created_time','customer_id','username','contact_method','campaign_id','step_id'));

		
		
	
		$collection->getSelect()->group('main_table.entity_id');
		
		//echo $collection->getSelect();
		//die();
		//echo $collection->getSelect();
		
		
		//die();
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
		$this->setCollection($collection);
		return parent::_prepareCollection();
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
		$this->addColumn('created_time', array(
			'header' => Mage::helper('coupon')->__('Contact Date'),
			'sortable' => true,
			'type' => 'datetime',
			'index' => 'created_time',
		));		
		
		$adminUserModel = Mage::getModel('admin/user');
		$userCollection = $adminUserModel->getCollection()->addFieldToFilter('is_active',1); 
		
		$referer_arr = array();
		
		foreach($userCollection as $user) {
			$referer_arr[$user->getUsername()] = $user->getUsername();
		}
		$this->addColumn('increment_id', array(
			'header' => Mage::helper('outofstock')->__('Order ID'),
			'index' => 'increment_id',
			'filter_index' => 'increment_id'
		));
		$this->addColumn('created_at', array(
			'header' => Mage::helper('outofstock')->__('Order Time'),
			'index' => 'created_at',
			'type'  => 'datetime',
			'filter_index' => 'sales_flat_order.created_at'
		));
		$this->addColumn('note_id', array(
			'header' => Mage::helper('outofstock')->__('Note ID'),
			'index' => 'note_id',
			'filter_index' => 'note_id'
		));
		
		$this->addColumn('username', array(
			'header' => Mage::helper('sales')->__('Admin User'),
			'index' => 'username',
			'type' => 'options',
			'width' => '70px',
			'options' => $referer_arr,
		));

		$this->addColumn('customer_id', array(
			'header' => Mage::helper('outofstock')->__('Customer ID'),
			'index' => 'customer_id',
			'filter_index' => 'magecon_customer_notes.customer_id'
		));
		$this->addColumn('email', array(
			'header' => Mage::helper('outofstock')->__('Customer E-Mail'),
			'index' => 'email',
			'filter_index' => 'customer_entity.email'
		));
		$this->addColumn('contact_method', array(
			'header' => Mage::helper('outofstock')->__('Contact Method'),
			'type' => 'options',
			'options' => Mage::helper('customernotes')->getOptions(),
			'index' => 'contact_method'
		));
		
		$this->addColumn('campaign_id', array(
			'header' => Mage::helper('outofstock')->__('Campaign'),
			'type' => 'options',
			'options' => Mage::helper('softwaremedia_campaign')->getCampaignOptions(),
			'index' => 'campaign_id'
		));
		
		$this->addColumn('step_id', array(
			'header' => Mage::helper('outofstock')->__('Step'),
			'type' => 'options',
			'options' => Mage::helper('softwaremedia_campaign')->getStepOptions(),
			'index' => 'step_id'
		));
		
		$this->addColumn('base_grand_total', array(
			'header' => Mage::helper('outofstock')->__('Revenue'),
			'index' => 'base_grand_total',
			'type' => 'number',
			'filter_index' => 'base_grand_total'
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
	
	protected function _filterHasUrlConditionCallback($collection, $column)
	{
    if (!$value = $column->getFilter()->getValue()) {
        return $this;
    }
  
    if ($value == "No") {
        $this->getCollection()->getSelect()->having('COUNT(sales_flat_order.increment_id) < 1');
        //echo  $this->getCollection()->getSelect();
        //die();

    }
    else {
       // $this->getCollection()->getSelect()->having("cc > 0");
       $this->getCollection()->getSelect()->having('COUNT(sales_flat_order.increment_id) > 0');
             
       //echo  $this->getCollection()->getSelect();
    }
    
    //echo $this->getCollection()->getSize();

    return $this;
}

}
