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
class SoftwareMedia_Swmreports_Block_Adminhtml_Coupon_Grid extends Mage_Adminhtml_Block_Widget_Grid {

	public function __construct() {
		parent::__construct();
		$this->setId('couponGrid');
//		$this->setUseAjax(true);
		$this->setSaveParametersInSession(true);
		$this->setSubReportSize(false);
		$this->setDefaultFilter(array('status' => 'complete'));
		$this->setDefaultLimit(200);
	}

	protected function _addColumnFilterToCollection($column) {
		if ($this->getCollection()) {
			$field = ( $column->getFilterIndex() ) ? $column->getFilterIndex() : $column->getIndex();
			if ($column->getFilterConditionCallback()) {
				call_user_func($column->getFilterConditionCallback(), $this->getCollection(), $column);
			} else {
				$cond = $column->getFilter()->getCondition();
				if ($field && isset($cond)) {
					if ($field == 'discount_amount') {
						$to = (!empty($cond['to']) ? $cond['to'] : 0);
						$from = (!empty($cond['from']) ? $cond['from'] : 0);
						unset($cond['from']);
						unset($cond['to']);

						if (!empty($from)) {
							$cond['to'] = $from * -1;
						}
						if (!empty($to)) {
							$cond['from'] = $to * -1;
						}
						$this->getCollection()->addFieldToFilter('main_table.discount_amount', $cond);
					} else {
						$this->getCollection()->addFieldToFilter($field, $cond);
					}
				}
			}
		}
		return $this;
	}

	protected function _prepareCollection() {
		/**
		 * @var Mage_SalesRule_Model_Resource_Coupon_Collection $collection
		 */
		$collection = Mage::getModel('sales/order')->getCollection()
			->addAttributeToSelect('entity_id')
			->addAttributeToSelect('increment_id')
			->addAttributeToSelect('coupon_code')
			->addAttributeToSelect('coupon_rule_name')
			->addAttributeToSelect('created_at')
			->addAttributeToSelect('status')
			->addAttributeToFilter('coupon_code', array('notnull' => true));

		$collection->getSelect()->columns(
			array(
				'discount_amount' => '(main_table.discount_amount * -1)',
			)
		);
		$this->setCollection($collection);

		return parent::_prepareCollection();
	}

	protected function _statusFilter($collection, $column) {
		$filter = $column->getFilter()->getValue();
		if (!$filter) {
			return $this;
		} else {
			$this->getCollection()->getSelect()->where('main_table.status = ?', $filter);
		}

		return $this;
	}

	protected function _prepareColumns() {

		$this->addColumn('Order ID', array(
			'header' => Mage::helper('coupon')->__('Order ID'),
			'index' => 'increment_id',
			'width' => '100px',
		));

		$arr = Mage::getSingleton('sales/order_config')->getStatuses();

		$this->addColumn('status', array(
			'header' => Mage::helper('coupon')->__('Status'),
			'index' => 'status',
			'type' => 'options',
			'options' => $arr,
			'filter_index' => 'status',
			'filter_condition_callback' => array($this, '_statusFilter'),
		));

		$this->addColumn('coupon_rule_name', array(
			'header' => Mage::helper('coupon')->__('Coupon Code'),
			'index' => 'coupon_rule_name'
		));

		$this->addColumn('discount_amount', array(
			'header' => Mage::helper('coupon')->__('Sales Discount Amount'),
			'sortable' => false,
			'type' => 'currency',
			'index' => 'discount_amount',
			'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
		));

		$this->addColumn('created_at', array(
			'header' => Mage::helper('coupon')->__('Created Date'),
			'sortable' => true,
			'type' => 'date',
			'index' => 'created_at',
		));

		if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
			$this->addColumn('action', array(
				'header' => Mage::helper('sales')->__('Action'),
				'width' => '50px',
				'type' => 'action',
				'getter' => 'getId',
				'actions' => array(
					array(
						'caption' => Mage::helper('sales')->__('View'),
						'url' => array('base' => 'adminhtml/sales_order/view'),
						'field' => 'order_id'
					)
				),
				'filter' => false,
				'sortable' => false,
				'index' => 'stores',
				'is_system' => true,
			));
		}

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

}
