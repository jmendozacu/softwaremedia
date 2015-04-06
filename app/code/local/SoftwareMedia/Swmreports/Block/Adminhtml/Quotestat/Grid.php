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
class SoftwareMedia_Swmreports_Block_Adminhtml_Quotestat_Grid extends Mage_Adminhtml_Block_Widget_Grid {

	public function __construct() {
		parent::__construct();
		$this->setId('quotesGrid');
//		$this->setUseAjax(true);
		$this->setSaveParametersInSession(true);
		$this->setSubReportSize(false);
		$this->setCountTotals(false);
		$this->setDefaultLimit(10000);
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
                              ->addFieldToFilter('main_table.status',array('neq'=>'21'))
                              ->addFieldToFilter('main_table.customer_id',array('gt' =>'0'));

		$collection = $this->addFilters($collection);
		
		$collection->getSelect()->joinLeft(
			'admin_user', '`main_table`.user_id=`admin_user`.user_id', array('username')
		);

		$collection->getSelect()->columns(array('count' => 'COUNT(quote_id)'));
		$collection->getSelect()->group('main_table.user_id');
		
		//echo $collection->getSelect();

		
		foreach($collection as $col) {
			$newCol = Mage::getModel('qquoteadv/qqadvcustomer')->getCollection()
                              ->addFieldToFilter('main_table.is_quote','1')
                              ->addFieldToFilter('main_table.status',71)
                              ->addFieldToFilter('main_table.user_id',$col->getUserId())
                              ->addFieldToFilter('main_table.customer_id',array('gt' =>'0'));
			$newCol = $this->addFilters($newCol);
			
			$col->setData('ordered',$newCol->getSize());
			$col->setData('conversion',number_format($newCol->getSize() / $col->getCount() * 100,2) . '%');
		}
	
		
		//echo $collection->getSelect();
		$this->setCollection($collection);

		return parent::_prepareCollection();
	}


	protected function _prepareColumns() {

		if (!$this->getRequest()->getParam('from'))
			return parent::_prepareColumns();
			


		$this->addColumn('username', array(
            'header'    => Mage::helper('qquoteadv')->__('Sales Rep'),
            'index'     => 'username',
            'filter' => false,
        ));
        
        		
		$this->addColumn('count', array(
			'header' => Mage::helper('coupon')->__('# Proposals Sent'),
			'sortable' => false,
			'index' => 'count',
			'filter' => false,
			'sortable' => false
		));
		
		$this->addColumn('ordered', array(
			'header' => Mage::helper('coupon')->__('# Proposals Ordered'),
			'sortable' => false,
			'index' => 'ordered',
			'filter' => false,
			'sortable' => false
		));
		
		 $this->addColumn('conversion', array(
			'header' => Mage::helper('coupon')->__('Conversion Rate'),
			'sortable' => false,
			'index' => 'conversion',
			'filter' => false,
			'sortable' => false
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
