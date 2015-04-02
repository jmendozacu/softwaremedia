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
class SoftwareMedia_Swmreports_Block_Adminhtml_Quotes_Grid extends Mage_Adminhtml_Block_Widget_Grid {

	public function __construct() {
		parent::__construct();
		$this->setId('quotesGrid');
//		$this->setUseAjax(true);
		$this->setSaveParametersInSession(true);
		$this->setSubReportSize(false);
		$this->setDefaultLimit(200);
	}
	
	public function addFilters($col) {
		if ($this->getRequest()->getParam('from'))
			$from = date('Y-m-d 00:00:00',strtotime($this->getRequest()->getParam('from')));
		
		$now = $to = date('Y-m-d 23:59:59',strtotime( '-1 hours' ));
			
		if ($this->getRequest()->getParam('to'))
			$to = date('Y-m-d 23:59:59',strtotime($this->getRequest()->getParam('to')));
		else 
			$to = $now;
		if ($to>$now)
			$to = $now;
				
		if ($from)
			$col->addFieldToFilter('main_table.created_at',array('gt' => $from));
		if ($to)
			$col->addFieldToFilter('main_table.created_at',array('lt' => $to));
				
		return $col;
	}
	
	protected function _prepareCollection() {
		
		if (!$this->getRequest()->getParam('from'))
			return parent::_prepareColumns();
			
			
		$resource = Mage::getSingleton('core/resource');
	    $readConnection = $resource->getConnection('core_read');

    
		/**
		 * @var Mage_SalesRule_Model_Resource_Coupon_Collection $collection
		 */
		$collection = Mage::getModel('qquoteadv/qqadvcustomer')->getCollection()
                              ->addFieldToFilter('main_table.is_quote','1')
                              ->addFieldToFilter('main_table.status',array('gt'=>'1'))
                              ->addFieldToFilter('main_table.customer_id',array('gt' =>'0'));

		$collection = $this->addFilters($collection);
		
		$collection->getSelect()->joinLeft(
			'quoteadv_request_item', '`main_table`.quote_id=`quoteadv_request_item`.quote_id', array()
		);


		$collection->getSelect()->joinLeft(
			'admin_user', '`main_table`.user_id=`admin_user`.user_id', array('username')
		);


		$collection->getSelect()->joinLeft(
			'catalog_product_entity_decimal', '`quoteadv_request_item`.product_id=`catalog_product_entity_decimal`.entity_id AND catalog_product_entity_decimal.attribute_id = 100', array()
		);
		
		/*

		$collection->getSelect()->joinLeft(
			'catalog_product_entity_int', '`quoteadv_request_item`.product_id=`catalog_product_entity_decimal`.entity_id AND catalog_product_entity_int.attribute_id = 1455 AND catalog_product_entity_int.value=1210', array('license'=>'value')
		);
		*/
				
		$collection->getSelect()->columns(array('sum' => 'SUM(quoteadv_request_item.owner_cur_price * quoteadv_request_item.request_qty)'));
		$collection->getSelect()->columns(array('cost' => 'SUM(catalog_product_entity_decimal.value * quoteadv_request_item.request_qty)'));
		$collection->getSelect()->columns(array('profit' => 'SUM(quoteadv_request_item.owner_cur_price * quoteadv_request_item.request_qty) - SUM(catalog_product_entity_decimal.value * quoteadv_request_item.request_qty)'));
		$collection->getSelect()->group('quoteadv_request_item.quote_id');
		
		//echo $collection->getSelect();

		
		foreach($collection as $col) {
			$cost = NULL;
			$sum = 0;
			$salesItems = Mage::getModel('sales/order')->getCollection()
						->addFieldToFilter('customer_id', $col->getCustomerId())
						->addFieldToFilter('created_at', array('gt'=>$col->getCreated()));

			$col->setData('orders',$salesItems->getSize());
			
			$salesItems = Mage::getModel('sales/order')->getCollection()
						->addFieldToFilter('customer_id', $col->getCustomerId())
						->addFieldToFilter('created_at', array('lt'=>$col->getCreated()));

			$col->setData('previous_orders',$salesItems->getSize());
			
			$query = 'SELECT value FROM catalog_product_entity_int WHERE entity_id IN (SELECT product_id FROM quoteadv_request_item WHERE quote_id = ' . $col->getQuoteId() . ') AND attribute_id = 1455 AND value=1210';
			
			$licenses = $readConnection->fetchCol($query);
			
			$col->setData('license',count($licenses));
		}
	
		
		//echo $collection->getSelect();
		$this->setCollection($collection);

		return parent::_prepareCollection();
	}


	protected function _prepareColumns() {

		if (!$this->getRequest()->getParam('from'))
			return parent::_prepareColumns();
			
		$this->addColumn('Quote ID', array(
			'header' => Mage::helper('coupon')->__('Quote ID'),
			'index' => 'increment_id',
			'filter_index' => 'main_table.increment_id',
			'width' => '100px',
		));

		$arr = Mage::getSingleton('sales/order_config')->getStatuses();

		 $this->addColumn('created_at', array(
            'header'    => Mage::helper('qquoteadv')->__('Created On'),
            'index'     => 'created_at',
            'filter_index' => 'main_table.created_at',
            'type'      => 'datetime',
            'width'     => '100px',
        ));
        
		$this->addColumn('status', array(
            'header'    => Mage::helper('qquoteadv')->__('Status'),
            'align'     => 'left',
            'width'     => '120px',
            'index'     => 'status',
            'type'      => 'options',
            'options'   => Ophirah_Qquoteadv_Model_Status::getGridOptionArray(),
            'renderer'  => new Ophirah_Qquoteadv_Block_Adminhtml_Qquoteadv_Renderer_Status()
        ));
        
        $this->addColumn('firstname', array(
            'header'    => Mage::helper('qquoteadv')->__('First Name'),
            'index'     => 'firstname'
        ));

		$this->addColumn('lastname', array(
            'header'    => Mage::helper('qquoteadv')->__('Last Name'),
            'index'     => 'lastname'
        ));
		$this->addColumn('telephone', array(
            'header'    => Mage::helper('qquoteadv')->__('Telephone'),
            'index'     => 'telephone'
        ));
		$this->addColumn('region', array(
            'header'    => Mage::helper('qquoteadv')->__('Region'),
            'index'     => 'region'
        ));
		$this->addColumn('company', array(
            'header'    => Mage::helper('qquoteadv')->__('Company'),
            'index'     => 'company'
        ));
        
        $this->addColumn('email', array(
            'header'    => Mage::helper('qquoteadv')->__('Email'),
            'index'     => 'email'
        ));

		$this->addColumn('username', array(
            'header'    => Mage::helper('qquoteadv')->__('Sales Rep'),
            'index'     => 'username'
        ));
        
        		
		$this->addColumn('license', array(
			'header' => Mage::helper('coupon')->__('Has License'),
			'sortable' => false,
			'index' => 'license',
			'renderer' => 'OCM_Catalog_Block_Widget_Orenderer',
			'filter' => false,
			'sortable' => false
		));
		
		
		/*
		$this->addColumn('license', array(
            'header'    => Mage::helper('qquoteadv')->__('License'),
            'index'     => 'license'
        ));
        */
		$this->addColumn('orders', array(
			'header' => Mage::helper('coupon')->__('Orders after Quote'),
			'sortable' => false,
			'index' => 'orders',
			'filter' => false,
			'sortable' => false,
			'filterable' => false
		));
		
		$this->addColumn('previous_orders', array(
			'header' => Mage::helper('coupon')->__('Orders before Quote'),
			'sortable' => false,
			'index' => 'previous_orders',
			'filter' => false,
			'sortable' => false,
			'filterable' => false
		));
		
		$this->addColumn('sum', array(
			'header' => Mage::helper('coupon')->__('Quote Rev'),
			'sortable' => false,
			'type' => 'currency',
			'filterable' => false,
			'index' => 'sum',
			'filter' => false,
			'sortable' => false,
			'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
		));

		$this->addColumn('cost', array(
			'header' => Mage::helper('coupon')->__('Quote Cost'),
			'sortable' => false,
			'type' => 'currency',
			'filter' => false,
			'sortable' => false,
			'index' => 'cost',
			'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
		));

        
        $this->addColumn('profit', array(
			'header' => Mage::helper('coupon')->__('Quote Profit'),
			'sortable' => false,
			'type' => 'currency',
			'index' => 'profit',
			'filter' => false,
			'sortable' => false,
			'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
		));
		
        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('qquoteadv')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('qquoteadv')->__('View'),
                        'url'       => array('base'=> 'adminhtml/qquoteadv/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
        
        $this->addExportType('*/*/exportCsv', Mage::helper('swmreports')->__('CSV'));
		parent::_prepareColumns();
	}

	public function getRowUrl($item) {
		return false;
	}

	protected function _addCustomFilter($collection, $filterData) {
		if ($filterData->getPriceRuleType()) {
			$rulesList = $filterData->getData('rules_list');
			if (isset($rulesList[0])) {
				$rulesIds = explode(',', $rulesList[0]);
				$collection->addRuleFilter($rulesIds);
			}
		}

		return parent::_addCustomFilter($filterData, $collection);
	}
	
	protected function _filterHasUrlConditionCallback($collection, $column)
	{

    if (!$value = $column->getFilter()->getValue()) {
        return $this;
    }
	var_dump($column->getFilter()->getValue());
	die();
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
