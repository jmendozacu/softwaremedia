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
class SoftwareMedia_Swmreports_Block_Adminhtml_Download_Grid extends Mage_Adminhtml_Block_Widget_Grid {

	public function __construct() {
		parent::__construct();
		$this->setId('cdownloadGrid');
		$this->setDefaultDir('DESC');
		$this->setSaveParametersInSession(true);
		$this->setSubReportSize(false);
		$this->setCustomHeader('Download Orders');
	}

	protected function _prepareCollection() {
		if (!$this->getRequest()->getParam('from'))
			return parent::_prepareColumns();
			
		$collection = Mage::getModel('sales/order')->getCollection();
		$collection->getSelect()->joinLeft(
				'customer_entity', '`customer_entity`.entity_id=`main_table`.customer_id', array('email')
			);
			
		$collection->getSelect()->joinLeft(
				'sales_flat_order_payment', '`sales_flat_order_payment`.parent_id=`main_table`.entity_id', array('method' => 'method')
			);
			
		$this->addFilters($collection);
		
		$subquery = new Zend_Db_Expr("(SELECT parent_id, MIN(created_at) download_date FROM mage.sales_flat_order_status_history WHERE status='download' GROUP BY parent_id,status)");

		$collection->getSelect()->joinInner(array('download_table' =>$subquery),'`download_table`.parent_id=`main_table`.entity_id',array('download_date'));
	
		$subquery = new Zend_Db_Expr("(SELECT parent_id, MIN(created_at) completed_date FROM mage.sales_flat_order_status_history WHERE status='complete' GROUP BY parent_id,status)");
			
		$collection->getSelect()->joinInner(array('completed_table' =>$subquery),'`completed_table`.parent_id=`main_table`.entity_id',array('completed_date'));

		$collection->getSelect()->columns(array('download_time' => 'FLOOR(TIME_TO_SEC(TIMEDIFF(completed_date,download_date))/60)'));
		$collection->getSelect()->columns(array('completed_time' => 'FLOOR(TIME_TO_SEC(TIMEDIFF(completed_date,main_table.created_at))/60)'));

		
		$collection->getSelect()->group('main_table.entity_id');
		$collection->addFieldToFilter('sales_flat_order_payment.method',array('neq'=>'purchaseorder'));
		//echo $collection->getSelect();
		//die();
		//echo $collection->getSelect();
		$collection->setPageSize(2000);
	
		count($collection);
		
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
	
	public function addFilters($col) {
		if ($this->getRequest()->getParam('from'))
			$from = date('Y-m-d 00:00:00',strtotime($this->getRequest()->getParam('from')));
			
		if ($this->getRequest()->getParam('to'))
			$to = date('Y-m-d 23:59:59',strtotime($this->getRequest()->getParam('to')));
			
				
		if ($from)
			$col->addFieldToFilter('main_table.created_at',array('gt' => $from));
		if ($to)
			$col->addFieldToFilter('main_table.created_at',array('lt' => $to));
				
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
		
		$this->addColumn('increment_id', array(
			'header' => Mage::helper('coupon')->__('Order ID'),
			'sortable' => false,
			'filter' => false,
			'index' => 'increment_id',
		));		
		
		$this->addColumn('created_at', array(
			'header' => Mage::helper('coupon')->__('Order Date'),
			'sortable' => false,
			'filter' => false,
			'type' => 'datetime',
			'index' => 'created_at',
		));		
		
		$this->addColumn('download_time', array(
			'header' => Mage::helper('coupon')->__('Download -> Complete'),
			'sortable' => false,
			 'filter' => false,
			'index' => 'download_time',
		));		
		
		$this->addColumn('completed_time', array(
			'header' => Mage::helper('coupon')->__('Ordered -> Complete'),
			'sortable' => false,
			'filter' => false,
			'index' => 'completed_time',
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
