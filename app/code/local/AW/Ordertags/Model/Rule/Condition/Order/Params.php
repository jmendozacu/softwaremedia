<?php

/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento enterprise edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Ordertags
 * @version    1.3.1
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */
class AW_Ordertags_Model_Rule_Condition_Order_Params extends Mage_Rule_Model_Condition_Abstract {

	protected $_defaultOperatorInputByType = null;

	/**
	 * Retrieve attribute object
	 */
	public function __construct() {
		parent::__construct();
		$this->setType('ordertags/rule_condition_order_params')->setValue(null);
	}

	public function loadAttributeOptions() {
		$hlp = Mage::helper('ordertags');
		$this->setAttributeOption(
			array(
				'order_total' => $hlp->__('Order Subtotal'),
				'order_grand_total' => $hlp->__('Order Grand Total'),
				'order_status' => $hlp->__('Order Status'),
				'order_items_total' => $hlp->__('Items Total'),
				'sku' => $hlp->__('SKU'),
				'shipping_country' => $hlp->__('Shipping Country'),
				'billing_country' => $hlp->__('Billing Country'),
				'order_contains_deal' => $hlp->__('Number of deals in order'),
				'order_deal_status' => $hlp->__('Deal Status'),
				'payment_method' => $hlp->__('Payment Method'),
				'shipping_method' => $hlp->__('Shipping Method'),
			)
		);
		return $this;
	}

	public function getValueSelectOptions() {
		if ($this->getAttribute() == 'order_status') {
			$orderStatuses = array();
			$statuses = Mage::getSingleton('sales/order_config')->getStatuses();
			foreach ($statuses as $value => $label) {
				$orderStatuses[] = array(
					'value' => $value,
					'label' => $label,
				);
			}
			$this->setData('value_select_options', $orderStatuses);
		}

		if ($this->getAttribute() == 'payment_method') {
			$paymentMethodList = Mage::helper('payment')->getPaymentMethodList(true, true, true);
			$this->setData('value_select_options', $paymentMethodList);
		}

		if ($this->getAttribute() == 'shipping_country') {
			$countyList = Mage::getModel('adminhtml/system_config_source_country')->toOptionArray();
			$this->setData('value_select_options', $countyList);
		}

		if ($this->getAttribute() == 'billing_country') {
			$countryList = Mage::getModel('adminhtml/system_config_source_country')->toOptionArray();
			$this->setData('value_select_options', $countryList);
		}
		if ($this->getAttribute() == 'order_deal_status') {
			$dealStatusList = Mage::getModel('ordertags/source_dealstatuses')->toMultiOptions();
			$this->setData('value_select_options', $dealStatusList);
		}
		if ($this->getAttribute() == 'shipping_method') {
			$options = array();

			$productmatrix = Mage::getResourceModel('productmatrix_shipping/carrier_productmatrix_collection')->setDistinctDeliveryTypeFilter()->load();
			foreach ($productmatrix->getItems() as $item) {
				$algorithmCol = explode('&', $item['algorithm']);
				$newDelType = preg_replace('/&|;| /', "_", $item['delivery_type']);

				foreach ($algorithmCol as $algorithmRow) {
					if (count($algorithmRow) != 2) {
						continue;
					}
					$algorithm = explode("=", $algorithmRow, 2);

					$algKey = strtolower($algorithm[0]);
					$algValue = $algorithm[1];

					if ($algKey == "c") {
						$newDelType = $algValue;
					}
				}

				if (!array_key_exists($newDelType, $allowedMethods)) {
					$options['productmatrix_' . $newDelType] = array('value' => 'productmatrix_' . $newDelType, 'label' => $item['delivery_type']);
				}
			}
			$this->setData('value_select_options', $options);
		}

		return $this->getData('value_select_options');
	}

	public function getDefaultOperatorInputByType() {
		if (null === $this->_defaultOperatorInputByType) {
			$this->_defaultOperatorInputByType = array(
				'string' => array('==', '!=', '()', '!()', '{}', '!{}'),
				'numeric' => array('==', '!=', '>=', '>', '<=', '<', '()', '!()'),
				'date' => array('==', '>=', '<='),
				'select' => array('==', '!='),
				'multiselect' => array('==', '!=', '{}', '!{}', '()', '!()'),
				'grid' => array('()', '!()'),
			);

			/**
			 * Validation since 1.6.0.0 has been changed.
			 * So that sting operators (== !=) cannot be compared with array values
			 */
			if (version_compare(Mage::getVersion(), '1.6.0.0', '>=')) {
				$this->_defaultOperatorInputByType['multiselect'] = array('{}', '!{}', '()', '!()');
			}
		}

		return $this->_defaultOperatorInputByType;
	}

	public function asHtml() {
		return parent::asHtml();
	}

	public function getAttributeElement() {
		$element = parent::getAttributeElement();
		$element->setShowAsText(true);
		return $element;
	}

	public function getValueElementType() {
		if (
			$this->getAttribute() == 'order_status' || $this->getAttribute() == 'payment_method' || $this->getAttribute() == 'shipping_country' || $this->getAttribute() == 'shipping_method' || $this->getAttribute() == 'billing_country'
		) {
			return 'select';
		}

		if ($this->getAttribute() == 'order_deal_status') {
			return 'multiselect';
		}

		return 'text';
	}

	public function getInputType() {
		switch ($this->getAttribute()) {
			case 'order_total':
			case 'order_grand_total':
			case 'order_items_total':
			case 'order_contains_deal' :
				return 'numeric';

			case 'order_status':
			case 'shipping_country':
			case 'shipping_method':
			case 'payment_method':
			case 'billing_country':
				return 'select';

			case 'order_deal_status':
				return 'multiselect';

			default:
				return 'string';
		}
	}

	public function validate(Varien_Object $object) {
		if ($this->getAttribute() == 'sku') {
			if ($this->getOperator() == '{}' || $this->getOperator() == '!{}') {
				$result = false;
				foreach ($object->getSku() as $sku) {
					if (stripos($sku, $this->getValue()) !== false) {
						$result = true;
						break;
					}
				}
				if ($this->getOperator() == '!{}') {
					$result = !$result;
				}
				return $result;
			}
		}
		return parent::validate($object);
	}

}
