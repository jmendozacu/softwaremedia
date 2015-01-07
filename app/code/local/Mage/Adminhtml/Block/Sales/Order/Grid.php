<?php

/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Adminhtml sales orders grid
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Sales_Order_Grid extends Mage_Adminhtml_Block_Widget_Grid {

	public function __construct() {
		parent::__construct();
		$this->setId('sales_order_grid');
		$this->setUseAjax(true);
		$this->setDefaultSort('created_at');
		$this->setDefaultDir('ASC');
		$this->setDefaultFilter(array('status' => 'main'));
		$this->setSaveParametersInSession(true);
	}

	/**
	 * Retrieve collection class
	 *
	 * @return string
	 */
	protected function _getCollectionClass() {
		return 'sales/order_grid_collection';
	}

	protected function _prepareCollection() {
		$collection = Mage::getResourceModel($this->_getCollectionClass());
		$collection->getSelect()->joinLeft(
				'sales_flat_order_item', '`sales_flat_order_item`.order_id=`main_table`.entity_id', array(
				'sku' => new Zend_Db_Expr('group_concat(DISTINCT CONCAT(`sales_flat_order_item`.sku," (", CAST(`sales_flat_order_item`.qty_ordered AS UNSIGNED),")") SEPARATOR "<br />")')
				)
			)
			->joinLeft('ocm_peachtree_referer', '`ocm_peachtree_referer`.order_id = `main_table`.entity_id', array('referer_id'))
			->joinLeft('aitoc_order_entity_custom', '`aitoc_order_entity_custom`.entity_id = `main_table`.entity_id AND `aitoc_order_entity_custom`.attribute_id = 1393', array('eul_company' => 'value'))
			;

		$collection->addAddressFields();
		$collection->getSelect()->joinLeft(array('sfo' => 'sales_flat_order'), 'sfo.entity_id=main_table.entity_id', array('sfo.customer_email','sfo.x_forwarded_for'));
		
		$collection->getSelect()->joinLeft(array('cust' => 'customer_entity_int'), 'sfo.customer_id=cust.entity_id AND cust.attribute_id=1541', array('cust.value'));
	

		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _statusFilter($collection, $column) {
		$filter = $column->getFilter()->getValue();
		if (!$filter) {
			return $this;
		} else if ($filter == 'main') {
			$this->getCollection()->getSelect()->where('main_table.status NOT IN (? , ? , ? )', array('complete', 'canceled', 'closed', 'paypal_canceled_reversal','purchaseorder_pending_payment'));
		} else {
			$this->getCollection()->getSelect()->where('main_table.status = ?', $filter);
		}

		return $this;
	}

	protected function _productFilter($collection, $column) {
		$filter = $column->getFilter()->getValue();
		if (!$filter) {
			return $this;
		} else {
			$this->getCollection()->getSelect()->where('`sales_flat_order_item`.sku LIKE ?', '%' . $filter . '%');
		}

		return $this;
	}

	protected function _prepareColumns() {

		$this->addColumn('real_order_id', array(
			'header' => Mage::helper('sales')->__('Order #'),
			'width' => '80px',
			'type' => 'text',
			'filter_index' => 'main_table.increment_id',
			'index' => 'increment_id',
		));

		if (!Mage::app()->isSingleStoreMode()) {
			$this->addColumn('store_id', array(
				'header' => Mage::helper('sales')->__('Purchased From (Store)'),
				'index' => 'store_id',
				'type' => 'store',
				'store_view' => true,
				'display_deleted' => true,
			));
		}

		$this->addColumn('created_at', array(
			'header' => Mage::helper('sales')->__('Purchased On'),
			'index' => 'created_at',
			'type' => 'datetime',
			'width' => '100px',
			'filter_index' => 'main_table.created_at',
		));


		$this->addColumn('eul_company', array(
			'header' => Mage::helper('sales')->__('EUL Company'),
			'index' => 'eul_company',
			'filter_index' => 'aitoc_order_entity_custom.value'
		));
		
		$this->addColumn('billing_company', array(
			'header' => Mage::helper('sales')->__('Company'),
			'index' => 'company',
			'filter_index' => 'billing_o_a.company'
		));
		
		$this->addColumn('billing_telephone', array(
			'header' => Mage::helper('sales')->__('Telephone'),
			'index' => 'telephone',
			'filter_index' => 'billing_o_a.telephone'
		));
		
		$this->addColumn('customer_email', array(
			'header' => Mage::helper('sales')->__('E-Mail'),
			'index' => 'customer_email',
			'filter_index' => 'sfo.customer_email'
		));
		$this->addColumn('x_forwarded_for', array(
			'header' => Mage::helper('sales')->__('Remote IP'),
			'index' => 'x_forwarded_for',
			'width' => '100px',
			'filter_index' => 'sfo.x_forwarded_for'
		));
		$this->addColumn('value', array(
			'header' => Mage::helper('sales')->__('Suspicious'),
			'index' => 'value',
			'filter_index' => 'cust.value',
			'type'  => 'options',
		    'options'   =>  array(
		        '1' => 'Yes'
		    )
		));
		$this->addColumn('billing_name', array(
			'header' => Mage::helper('sales')->__('Bill to Name'),
			'index' => 'billing_name',
		));
		/*
		  $this->addColumn('shipping_name', array(
		  'header' => Mage::helper('sales')->__('Ship to Name'),
		  'index' => 'shipping_name',
		  ));


		  $this->addColumn('base_grand_total', array(
		  'header' => Mage::helper('sales')->__('G.T. (Base)'),
		  'index' => 'base_grand_total',
		  'type' => 'currency',
		  'currency' => 'base_currency_code',
		  ));
		 */
		$this->addColumn('sku', array(
			'header' => Mage::helper('Sales')->__('Products'),
			'width' => '250px',
			'index' => 'sku',
			'type' => 'text',
			'filter_condition_callback' => array($this, '_productFilter')
		));


		$this->addColumn('grand_total', array(
			'header' => Mage::helper('sales')->__('G.T. (Purchased)'),
			'index' => 'grand_total',
			'type' => 'currency',
			'filter_index' => 'main_table.grand_total',
			'currency' => 'order_currency_code',
		));

		$arr = Mage::getSingleton('sales/order_config')->getStatuses();
		$arr['main'] = 'Main';

		$this->addColumn('status', array(
			'header' => Mage::helper('sales')->__('Status'),
			'index' => 'status',
			'type' => 'options',
			'width' => '70px',
			'options' => $arr,
			'filter_index' => 'main_table.status',
			'filter_condition_callback' => array($this, '_statusFilter'),
		));

		$referer_arr = OCM_Peachtree_Model_Referer::getReferers();
		$adminUserModel = Mage::getModel('admin/user');
		$userCollection = $adminUserModel->getCollection()->addFieldToFilter('is_active',1); 
		
		foreach($userCollection as $user) {
			$referer_arr[$user->getUsername()] = $user->getFirstname() . " " . $user->getLastname();
		}
		
		$this->addColumn('referer', array(
			'header' => Mage::helper('sales')->__('Referer'),
			'index' => 'referer_id',
			'type' => 'options',
			'width' => '70px',
			'options' => $referer_arr,
			'filter_index' => 'ocm_peachtree_referer.referer_id',
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
						'url' => array('base' => '*/sales_order/view'),
						'field' => 'order_id'
					)
				),
				'filter' => false,
				'sortable' => false,
				'index' => 'stores',
				'is_system' => true,
			));
		}
		$this->addRssList('rss/order/new', Mage::helper('sales')->__('New Order RSS'));

		$this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV'));
		$this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel XML'));

		return parent::_prepareColumns();
	}

	protected function _prepareMassaction() {
		$this->setMassactionIdField('entity_id');
		$this->getMassactionBlock()->setFormFieldName('order_ids');
		$this->getMassactionBlock()->setUseSelectAll(false);

		if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/cancel')) {
			$this->getMassactionBlock()->addItem('cancel_order', array(
				'label' => Mage::helper('sales')->__('Cancel'),
				'url' => $this->getUrl('*/sales_order/massCancel'),
			));
		}

		if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/hold')) {
			$this->getMassactionBlock()->addItem('hold_order', array(
				'label' => Mage::helper('sales')->__('Hold'),
				'url' => $this->getUrl('*/sales_order/massHold'),
			));
		}

		if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/unhold')) {
			$this->getMassactionBlock()->addItem('unhold_order', array(
				'label' => Mage::helper('sales')->__('Unhold'),
				'url' => $this->getUrl('*/sales_order/massUnhold'),
			));
		}

		$this->getMassactionBlock()->addItem('pdfinvoices_order', array(
			'label' => Mage::helper('sales')->__('Print Invoices'),
			'url' => $this->getUrl('*/sales_order/pdfinvoices'),
		));

		$this->getMassactionBlock()->addItem('pdfshipments_order', array(
			'label' => Mage::helper('sales')->__('Print Packingslips'),
			'url' => $this->getUrl('*/sales_order/pdfshipments'),
		));

		$this->getMassactionBlock()->addItem('pdfcreditmemos_order', array(
			'label' => Mage::helper('sales')->__('Print Credit Memos'),
			'url' => $this->getUrl('*/sales_order/pdfcreditmemos'),
		));

		$this->getMassactionBlock()->addItem('pdfdocs_order', array(
			'label' => Mage::helper('sales')->__('Print All'),
			'url' => $this->getUrl('*/sales_order/pdfdocs'),
		));

		$this->getMassactionBlock()->addItem('print_shipping_label', array(
			'label' => Mage::helper('sales')->__('Print Shipping Labels'),
			'url' => $this->getUrl('*/sales_order_shipment/massPrintShippingLabel'),
		));

		return $this;
	}

	public function getRowUrl($row) {
		if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
			return $this->getUrl('*/sales_order/view', array('order_id' => $row->getId()));
		}
		return false;
	}

	public function getGridUrl() {
		return $this->getUrl('*/*/grid', array('_current' => true));
	}

}
