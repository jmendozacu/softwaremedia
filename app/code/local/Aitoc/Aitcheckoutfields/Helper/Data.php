<?php

/**
 * Checkout Fields Manager
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitcheckoutfields
 * @version      10.5.3
 * @license:     x8JlL6PzUPBtvXBsIIWQy9KjFdhME32yIbvID6DGHQ
 * @copyright:   Copyright (c) 2014 AITOC, Inc. (http://www.aitoc.com)
 */
class Aitoc_Aitcheckoutfields_Helper_Data extends Mage_Core_Helper_Abstract {

	/**
	 * @var Aitoc_Aitcheckoutfields_Model_Aitcheckoutfields
	 */
	protected $_mainModel;

	public function __construct() {
		$this->_mainModel = Mage::getModel('aitcheckoutfields/aitcheckoutfields');
	}

	/**
	 * @param string $sMode [default: 'full'] Return variants: full or hash
	 * @param string $sType Checkout page type: onepage or multipage
	 * @return array | false
	 */
	public function getStepData($sType, $sMode = 'full') {
		if (!$sType)
			return false;

		switch ($sType) {
			case 'onepage':
				$aStepData = array
					(
					array
						(
						'value' => '',
						'label' => $this->__('None')
					),
					array
						(
						'value' => 1,
						'label' => $this->__('1. Billing Info')
					),
					array
						(
						'value' => 2,
						'label' => $this->__('2. Shipping Info')
					),
					array
						(
						'value' => 3,
						'label' => $this->__('3. Shipping Method')
					),
					array
						(
						'value' => 4,
						'label' => $this->__('4. Payment Info')
					),
					array
						(
						'value' => 5,
						'label' => $this->__('5. Order Review')
					),
				);
				break;

			case 'multipage':
				$aStepData = array
					(
					array
						(
						'value' => '',
						'label' => $this->__('None')
					),
					array
						(
						'value' => 1,
						'label' => $this->__('1. Select Addresses')
					),
					array
						(
						'value' => 2,
						'label' => $this->__('2. Shipping Info')
					),
					array
						(
						'value' => 3,
						'label' => $this->__('3. Billing Info')
					),
					array
						(
						'value' => 4,
						'label' => $this->__('4. Place Order')
					),
				);
				break;
		}

		if ($sMode == 'hash') {
			$aStepHash = array();

			foreach ($aStepData as $aItem) {
				if ($aItem['value']) {
					$aStepHash[$aItem['value']] = $aItem['label'];
				}
			}

			$aStepData = $aStepHash;
		}


		return $aStepData;
	}

	/**
	 * @return int
	 */
	public function getStepId($sStepType) {
		if (!$sStepType)
			return false;

		$aStepIdHash = array
			(
			'billing' => '1',
			'shippinfo' => '2',
			'shipping' => '2', //alias
			'shippmethod' => '3',
			'payment' => '4',
			'review' => '5',
			'mult_addresses' => '1',
			'mult_shipping' => '1', //shipping is filled on first page of multishipping process
			'mult_shippinfo' => '2',
			'mult_billing' => '3',
			'mult_overview' => '4',
		);

		if (isset($aStepIdHash[$sStepType])) {
			return $aStepIdHash[$sStepType];
		}

		return 0;
	}

	/**
	 * @return boolean
	 */
	public function checkIfAitocAitcheckoutIsActive() {
		try {
			return (boolean) (Aitoc_Aitsys_Abstract_Service::get()->isModuleActive('Aitoc_Aitcheckout'));
		} catch (Exception $e) {

		}

		return false;
	}

	/**
	 * Return an array of arrtibutes with it's values
	 *
	 * @param string $stepName Name of step to show attribute (billing,payment...)
	 * @param int|string $tplPlaceId Plato where attribute is shown. Int 1 or 0 to top|bottom, string for registration attributes
	 * @param string $type Type of checkout
	 *
	 * @return array | false
	 */
	public function getCustomFieldList($stepName, $tplPlaceId, $type = 'onepage') {
		if (!$stepId = $this->getStepId($stepName)) {
			return false;
		}

		return $this->_mainModel->getCheckoutAttributeList($stepId, $tplPlaceId, $type);
	}

	/**
	 * Return stirng with text of attribute names and inputed values
	 *
	 * @param string $stepName Name of step to show attribute (billing,payment...)
	 * @param int|string $tplPlaceId Plato where attribute is shown. Int 1 or 0 to top|bottom, string for registration attributes
	 * @param string $type Type of checkout
	 * @param bool $show_empty Flag to show empty values
	 *
	 * @return string
	 */
	public function getCustomFieldTextValues($stepName, $tplPlaceId, $type = 'onepage', $show_empty = false) {
		$attributes = $this->getCustomFieldList($stepName, $tplPlaceId, $type);
		$return = '';
		$iStoreId = Mage::app()->getStore()->getId();
		if (is_array($attributes)) {
			foreach ($attributes as $id => $aField) {
				$value = $this->_mainModel->getCustomValue($aField, $type);
				$value = $this->getAttributeText($id, $aField['frontend_input'], $value, $iStoreId);
				if (!$value && false == $show_empty)
					continue;
				$return .= $aField['frontend_label'] . ': ' . $value . '<br />';
			}
		}
		return $return;
	}

	public function getAttributeText($id, $frontendInput, $value = '', $iStoreId = 0) {
		$arrayTypes = array(
			'checkbox',
			'multiselect',
			'radio',
			'select',
			'boolean'
		);
		if (!in_array($frontendInput, $arrayTypes)) {
			return $value;
		}
		/* Some elements may contain Array(0=>'123,124') insted of Array(0=>123,1=>124). This way we will make them all look simmilar */
		$values = $value;
		if (is_array($values)) {
			$values = implode(',', $values);
		}
		$values = explode(',', $values);
		if (sizeof($values) == 0) {
			return $value;
		}
		if (!$iStoreId) {
			$iStoreId = Mage::app()->getStore()->getId();
		}
		if ($frontendInput == 'boolean') {
			$aOptionHash = array();
			$optionArray = Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray();
			foreach ($optionArray as $array) {
				$aOptionHash[$array['value']] = $array['label'];
			}
		} else {
			$aOptionHash = $this->_mainModel->getOptionValues($id, $iStoreId);
		}
		$return = array();
		foreach ($values as $id) {
			if (!empty($aOptionHash[$id]))
				$return[] = $aOptionHash[$id];
		}
		$return = implode(', ', $return);
		return $return;
	}

	/**
	 * @return string
	 */
	public function getFieldHtml($field, $setName, $type = 'onepage') {
		return $this->_mainModel->getAttributeHtml($field, $setName, $type);
	}

	/**
	 * @return string
	 */
	public function getStepFieldsCode($stepName, $tplPlaceId, $type = 'onepage', $setName = '') {
		$setName = $setName ? $setName : $stepName;
		$result = "\n<!--     START AITOC CHECKOUT ATTRIBUTES     -->";
		$display_all = true;
		$license_only_arr = array('company_name', 'address', 'city', 'state', 'zip', 'company_email_address', 'contact_name', 'phone_number', 'prior_license_authorization', 'prior_license_agreement');

		// Check to see if we are on the review page of paypal express
		if ($setName == 'aitpaypalexpress') {
			$display_all = false;
			$checkout = Mage::getSingleton('checkout/session')->getQuote();
			$items = $checkout->getAllItems();

			if (!empty($items)) {
				foreach ($items as $item) {
					$license_option = Mage::getResourceModel('catalog/product')->getAttributeRawValue($item->getProductId(), 'license_nonlicense_dropdown', Mage::app()->getStore()->getStoreId());

					if ($license_option == 1210) {
						$display_all = true;
						break;
					}
				}
			}
		}

		if ($display_all && $setName == 'aitpaypalexpress' && $tplPlaceId == 2) {
			$result .= '		<div id="end_user_info" style="margin:40px;">
			<div id="end_user_form_header">
				<table style="border: 1px solid;">
					<p id ="form_header" style="font-size: 22px; font-weight:bold;">End User Information</p>
					<p id = "form_header_content" style="">There are products on your order that require End User Information. The information you provide below will indicate who the following licenses will be registered to. Click \'Update Order\' after entering your information <p/>
				</table>
			</div>
		</div>';
		
		}
		if ($aFieldList = $this->getCustomFieldList($stepName, $tplPlaceId, $type)) {
			$result .= "\n<fieldset class=\"aitcfm-fieldset aitcfm_" . $stepName . '_' . $tplPlaceId . "\"><ul class=\"form-list\">\n";
			foreach ($aFieldList as $aField) {
				if ($display_all && in_array($aField['attribute_code'], $license_only_arr)) {
					$result .= $this->getFieldHtml($aField, $setName) . "\n";
				}
			}
			$result .= '</ul></fieldset>';
		}
		$result .= "\n<!--     FINISH AITOC CHECKOUT ATTRIBUTES    -->\n";

		return $result;
	}

	/**
	 * @return string
	 */
	public function getStepField($pageType) {
		$stepField = '';
		switch ($pageType) {
			case 'onepage':
				$stepField = 'is_searchable'; // hook for input source (one page)
				break;

			case 'multishipping':
				$stepField = 'is_comparable'; // hook for input source (multi shipping)
				break;

			case 'adminorderfields':
				$stepField = 'attribute_id'; // hook for input source (admin)
				break;
		}

		return $stepField;
	}

	public function getCartItems($quote_id = false, $isAdminhtml = false) {
		if (!isset($this->_cartItems)) {
			$quote = Mage::getSingleton('checkout/session')->getQuote();

			$items = $quote->getAllItems();
			if (!$items) {
				$items = Mage::getSingleton('adminhtml/sales_order_create')->getQuote()->getAllItems();
			}

			$ids = array();
			foreach ($items as $item) {
				$ids[] = $item->getProduct()->getId();
			}

			$this->_cartItems = $ids;
		}
		return $this->_cartItems;
	}

	public function getCartCategories($quote_id = false, $isAdminhtml = false) {
		if (!isset($this->_cartCategories)) {
			$quote = Mage::getSingleton('checkout/session')->getQuote();
			$items = $quote->getAllItems();
			if (!$items) {
				$items = Mage::getSingleton('adminhtml/sales_order_create')->getQuote()->getAllItems();
			}
			$ids = array();
			foreach ($items as $item) {
				$ids = array_merge($ids, $item->getProduct()->getCategoryIds());
			}
			$this->_cartCategories = $ids;
		}
		return $this->_cartCategories;
	}

	public function getPaypalReviewTemplate() {
		if (version_compare(Mage::getVersion(), '1.12.0.0', 'ge')) {
			return 'paypal/express/review.phtml';
		} else {
			return 'paypal/express/review.phtml';
		}
	}

}
