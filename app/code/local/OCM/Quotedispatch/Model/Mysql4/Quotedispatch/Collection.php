<?php

class OCM_Quotedispatch_Model_Mysql4_Quotedispatch_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

	public $reminder_delay = '5 days';
	public $expire_delay = '3 days';

	public function _construct() {
		parent::_construct();
		$this->_init('quotedispatch/quotedispatch');
	}

	public function addQuoteSubtotal() {

		$db = Mage::helper('quotedispatch')->getDb();
		$subtotal = $db->select()
			->from(array('i' => 'ocm_quotedispatch_item'))
			->group('i.quotedispatch_id')
			->columns(
			array('subtotal' => new Zend_Db_Expr('SUM(i.price * i.qty)'))
			)
		;

		$this->getSelect()->joinLeft(
			array('item' => $subtotal), 'main_table.quotedispatch_id = item.quotedispatch_id', array('subtotal')
		);

		return $this;
	}

	public function addAvailableStatusFilter($ignore_time = null) {
		$this->addFieldToFilter('status', array('in' => Mage::getModel('quotedispatch/status')->available_statuses));
		if (!$ignore_time) {
			$this->addFieldToFilter('expire_time', array(
				'from' => now(),
				'date' => true,
			))
			;
		}
		return $this;
	}

	public function addFirstLastNameToSelect() {
		$this->getSelect()->columns(
			array('first_last_name' => new Zend_Db_Expr('CONCAT(firstname," ",lastname)'))
		);
		return $this;
	}

	public function addReminderDelayFilter() {
		$this->addFieldToFilter(array('available_time', 'expire_time'), array(array(
				'from' => strtotime(now(true) . ' - ' . $this->reminder_delay),
				'to' => strtotime(now(true) . ' - ' . $this->reminder_delay . ' +1 day'),
				'date' => true,
			), array(
				'from' => strtotime(now(true) . ' + ' . $this->expire_delay),
				'to' => strtotime(now(true) . ' + ' . $this->expire_delay . ' +1 day'),
				'date' => true,
			))
		)
		;

		return $this;
	}

	public function addExpiredFilter() {
		$this->addFieldToFilter('expire_time', array(
			'to' => now(),
			'date' => true,
		))
		;
		return $this;
	}

}
