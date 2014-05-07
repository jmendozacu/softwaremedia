<?php

class SoftwareMedia_Substitution_Block_Adminhtml_Sales_Order_View_Tab_Invoices extends Mage_Adminhtml_Block_Sales_Order_View_Tab_Invoices {

	protected function _prepareCollection() {
		$arrInvoice = array();
		$order = $this->getOrder();
		$invoices = Mage::getResourceModel('sales/order_invoice_collection')
			->addFieldToSelect('entity_id')
			->setOrderFilter($order->getId());
		foreach ($invoices as $invoice) {
			$arrInvoice[] = $invoice->getId();
		}
		parent::_prepareCollection();
		$collection = Mage::getResourceModel('sales/order_invoice_item_collection')
			->addFieldToSelect('entity_id')
			->addFieldToSelect('qty')
			->addFieldToSelect('name')
			->addFieldToSelect('product_id')
			->addFieldToSelect('sku')
			->addFieldToSelect('base_row_total')
			->addFieldToSelect('parent_id')
			->addFieldToFilter('parent_id', array('in' => $arrInvoice));
		$collection->getSelect()->join(array('grid' => 'sales_flat_invoice_grid'), 'grid.entity_id=main_table.parent_id', array('increment_id', 'state'));
		$this->setCollection($collection);

		return $this;
	}

	protected function _prepareColumns() {
		$this->addColumn('increment_id', array(
			'header' => Mage::helper('sales')->__('Invoice #'),
			'index' => 'increment_id',
			'width' => '120px',
		));

		$this->addColumn('party_name', array(
			'header' => Mage::helper('sales')->__('Party'),
			'filter' => false,
		));

		$this->addColumn('product_sku', array(
			'header' => Mage::helper('sales')->__('Product SKU'),
			'index' => 'sku',
		));

		$this->addColumn('product_name', array(
			'header' => Mage::helper('sales')->__('Product Title'),
			'index' => 'name',
			'filter' => false,
		));

		$this->addColumn('product_qty', array(
			'header' => Mage::helper('sales')->__('Qty'),
			'index' => 'qty',
			'filter' => false,
		));

		$this->addColumn('product_addsub', array(
			'header' => Mage::helper('sales')->__('Add Sub'),
			'filter' => false,
			'renderer' => 'SoftwareMedia_Substitution_Block_Adminhtml_Sales_Order_View_Renderer_Addsub',
			'index' => 'parent_id',
		));

		$this->addColumn('state', array(
			'header' => Mage::helper('sales')->__('Status'),
			'index' => 'state',
			'type' => 'options',
			'options' => Mage::getModel('sales/order_invoice')->getStates(),
		));

		$this->addColumn('base_row_total', array(
			'header' => Mage::helper('customer')->__('Amount'),
			'index' => 'base_row_total',
			'type' => 'currency',
			'currency' => 'base_currency_code',
			'filter' => false,
		));
		$this->addColumn('action', array(
			'header' => Mage::helper('sales')->__('Action'),
			'filter' => false,
			'renderer' => 'SoftwareMedia_Substitution_Block_Adminhtml_Sales_Order_View_Renderer_Action',
			'index' => 'parent_id',
		));

		return $this;
	}

	public function getRowUrl($row) {
		return $this->getUrl('*/sales_order_invoice/view', array(
				'invoice_id' => $row->getParentId(),
				)
		);
	}

	public function getRowId($row) {
		return $row->getParentId();
	}

}
