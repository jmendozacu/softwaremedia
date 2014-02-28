<?php

class OCM_Quotedispatch_Model_Quotedispatch extends OCM_Quotedispatch_Model_Abstract {
	/*
	  Status Values:
	  0 => unavalable for purhase
	  1 => available for purchase
	  2 => purchased
	 */

	public function _construct() {
		parent::_construct();
		$this->_init('quotedispatch/quotedispatch');
	}

	/**
	 * Get array of all items what can be display directly
	 *
	 * @return array
	 */
	public function getAllVisibleItems() {
		if (!$this->hasData('all_visible_items')) {
			$items = array();
			foreach ($this->getAllItems() as $quote_item) {
				$item = Mage::getModel('catalog/product')->load($quote_item->getProductId());
				$grouped_parent = Mage::getModel('catalog/product_type_grouped')->getParentIdsByChild($item->getId());
				$config_parent = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($item->getId());
				if (!$item->isDeleted() && !$grouped_parent && !$config_parent) {
					$items[] = $quote_item;
				}
			}
			$this->setData('all_visible_items', $items);
		}
		return $this->getData('all_visible_items');
	}

	public function getItemList() {

		if (!$this->hasData('item_list')) {
			$collection = $this->getAllVisibleItems();

			foreach ($collection as $item) {
				$itemList .= $item->getName() . " - (" . $item->getQty() . ")" . "<br />";
			}

			$this->setData('item_list', $itemList);
		}

		return $this->getData('item_list');
	}

	public function getAllItems() {

		if (!$this->hasData('all_items')) {

			$name_attr = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', 'name');

			//die(print_r(array_keys($name_attr->getData())));

			$collection = Mage::getModel('quotedispatch/quotedispatch_items')->getCollection()
				->addFieldToFilter('quotedispatch_id', $this->getId())
			//->addFieldToFilter('email',$this->getEmail())
			;

			$collection->getSelect()
				->joinleft(
					array('e' => 'catalog_product_entity'), 'main_table.product_id = e.entity_id'
				)
				->joinleft(
					array('pv' => 'catalog_product_entity_varchar'), 'pv.entity_id=main_table.product_id', array('name' => 'value')
				)
				->where('pv.attribute_id=' . $name_attr->getAttributeId())
				->columns(array(
					'line_total' => new Zend_Db_Expr('main_table.price * main_table.qty')
					)
				)
			;


			//die($collection->getSelect());

			$this->setData('all_items', $collection);
		}
		return $this->getData('all_items');
	}

	public function loadByMultiple($filters) {
		$collection = $this->getCollection();

		foreach ($filters as $column => $value) {
			$collection->addFieldToFilter('main_table.' . $column, $value);
		}

		$collection->addQuoteSubtotal();

		$item = $collection->getFirstItem();

		if ($item->getId()) {
			$this->setData($item->getData());
			$this->setData('all_items', $item->getAllItems());
		} else {
			$this->setData(array());
		}
		return $this;
	}

	public function getSubtotal() {
		if (!$this->hasData('subtotal')) {
			$subtotal = $this->_getResource()->getSubtotal($this);
			$this->setData('subtotal', $subtotal);
		}
		return $this->getData('subtotal');
	}

}
