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

/**
 * @copyright  Copyright (c) 2009 AITOC, Inc.
 */
class Aitoc_Aitcheckoutfields_Model_Rewrite_AdminSalesOrderCreate extends Mage_Adminhtml_Model_Sales_Order_Create {

	// overwrite parent
	public function createOrder() {
		$mainModel = Mage::getModel('aitcheckoutfields/aitcheckoutfields');
		$oldOrderId = '';

		/* {#AITOC_COMMENT_END#}
		  if(isset($_SESSION['adminhtml_quote']['order_id'])||isset($_SESSION['adminhtml_quote']['reordered']))
		  {
		  $oldOrderId=isset($_SESSION['adminhtml_quote']['order_id'])?$_SESSION['adminhtml_quote']['order_id']:$_SESSION['adminhtml_quote']['reordered'];
		  $oldOrder = Mage::getModel('sales/order')->load($oldOrderId);
		  $storeId = $oldOrder->getStoreId();
		  $websiteId = $oldOrder->getStore()->getWebsiteId();
		  }else{
		  $quote = $this->getQuote();
		  $storeId = $quote->getStoreId();
		  $websiteId = $quote->getStore()->getWebsiteId();
		  }

		  $performer = Aitoc_Aitsys_Abstract_Service::get()->platform()->getModule('Aitoc_Aitcheckoutfields')->getLicense()->getPerformer();
		  $ruler = $performer->getRuler();
		  if (!($ruler->checkRule('store',$storeId,'store') || $ruler->checkRule('store',$websiteId,'website')))
		  {
		  if($oldOrderId)
		  {
		  $oldData = $mainModel->getOrderCustomData($oldOrderId, $storeId, true);
		  foreach ($oldData as $oldAttr){
		  if(in_array($oldAttr['type'],array('checkbox','radio','select','multiselect')))
		  {
		  $oldAttr['rawval'] = explode(',',$oldAttr['rawval']);
		  }
		  $_SESSION['aitoc_checkout_used']['adminorderfields'][$oldAttr['id']]=$oldAttr['rawval'];
		  }
		  }
		  }
		  else
		  {
		  {#AITOC_COMMENT_START#} */
		$attributeCollection = $mainModel->getAttributeCollecton();
		$data = Mage::app()->getRequest()->getPost('aitoc_checkout_fields');

		foreach ($attributeCollection as $attribute) {
			if (isset($data[$attribute->getAttributeCode()])) {
				if ($attribute->getFrontend()->getInputType() !== 'static') {
					$_SESSION['aitoc_checkout_used']['adminorderfields'][$attribute->getId()] = $data[$attribute->getAttributeCode()];
				}
			}
		}
		/* {#AITOC_COMMENT_END#}
		  }
		  {#AITOC_COMMENT_START#} */

		/*		 * ************ AITOC DELIVERY DATE COMPATIBILITY MODE: START ******************* */

		$val = Mage::getConfig()->getNode('modules/AdjustWare_Deliverydate/active');
		if ((string) $val == 'true') {
			// START AITOC DELIVERY DATE
			$errors = Mage::getModel('adjdeliverydate/step')->process('shippingMethod');

			if (!empty($errors)) {
				foreach ($errors as $error) {
					$this->getSession()->addError($error);
				}
				Mage::throwException('');
			}

			// FINISH AITOC DELIVERY DATE

			$this->_validate();

			if (!$this->getQuote()->getCustomerIsGuest()) {
				$this->_putCustomerIntoQuote();
			}

			$quoteConvert = Mage::getModel('sales/convert_quote');

			/* @var $quoteConvert Mage_Sales_Model_Convert_Quote */

			$quote = $this->getQuote();
			if (!$this->getSession()->getOrder()->getId()) {
				$quote->reserveOrderId();
			}

			if ($this->getQuote()->getIsVirtual()) {
				$order = $quoteConvert->addressToOrder($quote->getBillingAddress());
			} else {
				$order = $quoteConvert->addressToOrder($quote->getShippingAddress());
			}
			$order->setBillingAddress($quoteConvert->addressToOrderAddress($quote->getBillingAddress()))
				->setPayment($quoteConvert->paymentToOrderPayment($quote->getPayment()));
			if (!$this->getQuote()->getIsVirtual()) {
				$order->setShippingAddress($quoteConvert->addressToOrderAddress($quote->getShippingAddress()));
			}

			if (!$this->getQuote()->getIsVirtual()) {
				foreach ($quote->getShippingAddress()->getAllItems() as $item) {
					/* @var $item Mage_Sales_Model_Quote_Item */
					$orderItem = $quoteConvert->itemToOrderItem($item);
					$options = array();
					if ($productOptions = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct())) {
						$productOptions['info_buyRequest']['options'] = $this->_prepareOptionsForRequest($item);
						$options = $productOptions;
					}
					if ($addOptions = $item->getOptionByCode('additional_options')) {
						$options['additional_options'] = unserialize($addOptions->getValue());
					}
					if ($options) {
						$orderItem->setProductOptions($options);
					}

					if ($item->getParentItem()) {
						$orderItem->setParentItem($order->getItemByQuoteItemId($item->getParentItem()->getId()));
					}

					$order->addItem($orderItem);
				}
			}
			if ($this->getQuote()->hasVirtualItems()) {
				foreach ($quote->getBillingAddress()->getAllItems() as $item) {
					/* @var $item Mage_Sales_Model_Quote_Item */
					$orderItem = $quoteConvert->itemToOrderItem($item);
					$options = array();
					if ($productOptions = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct())) {
						$productOptions['info_buyRequest']['options'] = $this->_prepareOptionsForRequest($item);
						$options = $productOptions;
					}
					if ($addOptions = $item->getOptionByCode('additional_options')) {
						$options['additional_options'] = unserialize($addOptions->getValue());
					}
					if ($options) {
						$orderItem->setProductOptions($options);
					}

					if ($item->getParentItem()) {
						$orderItem->setParentItem($order->getItemByQuoteItemId($item->getParentItem()->getId()));
					}

					$order->addItem($orderItem);
				}
			}

			if ($this->getSendConfirmation()) {
				$order->setEmailSent(true);
			}

			if ($this->getSession()->getOrder()->getId()) {
				$oldOrder = $this->getSession()->getOrder();

				$originalId = $oldOrder->getOriginalIncrementId() ? $oldOrder->getOriginalIncrementId() : $oldOrder->getIncrementId();
				$order->setOriginalIncrementId($originalId);
				$order->setRelationParentId($oldOrder->getId());
				$order->setRelationParentRealId($oldOrder->getIncrementId());
				$order->setEditIncrement($oldOrder->getEditIncrement() + 1);
				$order->setIncrementId($originalId . '-' . $order->getEditIncrement());
			}

			$order->place();
			$this->_saveCustomerAfterOrder($order);
			$order->save();

			if ($this->getSession()->getOrder()->getId()) {
				$oldOrder = $this->getSession()->getOrder();

				$this->getSession()->getOrder()->setRelationChildId($order->getId());
				$this->getSession()->getOrder()->setRelationChildRealId($order->getIncrementId());
				$this->getSession()->getOrder()->cancel()
					->save();
				$order->save();
			}

			// AITOC DELIVERY DATE
			$fields = Mage::getSingleton('adjdeliverydate/session')->getShippingMethod();

			//only one step and only two field for the first module version
			if (is_array($fields)) {

				// fix for delivery time
				if (Mage::getStoreConfig('checkout/adjdeliverydate/show_time') AND !empty($fields['delivery_date']) AND !empty($fields['delivery_time'])) {
					$fields['delivery_date'] .= ' ' . implode(':', $fields['delivery_time']);

					unset($fields['delivery_time']);
				}

				foreach ($fields as $name => $value) {
					$order->setData($name, $value);
					$order->setData($name . '_is_formated', true);
				}
				$order->save();
			}

			Mage::getSingleton('adjdeliverydate/session')->setShippingMethod(null);
			// END AITOC DELIVERY DATE

			if ($this->getSendConfirmation()) {
				$order->sendNewOrderEmail();
			}
		} else {
			$order = parent::createOrder();
		}
		/*		 * ************ AITOC DELIVERY DATE COMPATIBILITY MODE: FINISH ******************* */

		if (isset($_SESSION['aitoc_checkout_used']['new_customer'])) {
			$_SESSION['aitoc_checkout_used']['register'] = $_SESSION['aitoc_checkout_used']['adminorderfields'];
			$customerId = $order->getCustomerId();
			$mainModel->saveCustomerData($customerId);
		}

		$orderId = $order->getId();
		$mainModel->saveCustomOrderData($orderId, 'adminorderfields');
		$mainModel->clearCheckoutSession('adminorderfields');
		Mage::getSingleton('adminhtml/session')->unsetData('aitcheckoutfields_admin_post_data');
		Mage::getSingleton('adminhtml/session')->unsetData('order_purchase_order');

		return $order;
	}

	// overwrite parent
	public function importPostData($data) {
		$toReturn = parent::importPostData($data);

		$data = Mage::app()->getRequest()->getPost('order');
		if ($postData = $data['account']['purchase_order'])
			Mage::getSingleton('adminhtml/session')->addData(array('order_purchase_order' => $postData));

		if ($postData = Mage::app()->getRequest()->getPost('aitoc_checkout_fields')) {
			if (!Mage::getSingleton('adminhtml/session')->hasData('aitcheckoutfields_admin_post_data')) {
				Mage::getSingleton('adminhtml/session')->addData(array('aitcheckoutfields_admin_post_data' => $postData));
			} elseif ($postData != Mage::getSingleton('adminhtml/session')->getData('aitcheckoutfields_admin_post_data')) {
				Mage::getSingleton('adminhtml/session')->addData(array('aitcheckoutfields_admin_post_data' => $postData));
			}
		}


		return $toReturn;
	}

	/**
	 * Prepare quote customer
	 *
	 * @return Mage_Adminhtml_Model_Sales_Order_Create
	 */
	public function _prepareCustomer() {
		/** @var $quote Mage_Sales_Model_Quote */
		$quote = $this->getQuote();
		if ($quote->getCustomerIsGuest()) {
			return $this;
		}

		/** @var $customer Mage_Customer_Model_Customer */
		$customer = $this->getSession()->getCustomer();

		$customer_exists = Mage::getModel('customer/customer')->loadByEmail($this->_getNewCustomerEmail($customer));
		if ($customer_exists->getId()) {
			$customer = $customer_exists;
		}

		/** @var $store Mage_Core_Model_Store */
		$store = $this->getSession()->getStore();

		$customerIsInStore = $this->_customerIsInStore($store);
		$customerBillingAddress = null;
		$customerShippingAddress = null;

		if ($customer->getId()) {
			// Create new customer if customer is not registered in specified store
			if (!$customerIsInStore) {
				$customer->setId(null)
					->setStore($store)
					->setDefaultBilling(null)
					->setDefaultShipping(null)
					->setPassword($customer->generatePassword());
				$this->_setCustomerData($customer);
			}

			if ($this->getBillingAddress()->getSaveInAddressBook()) {
				/** @var $customerBillingAddress Mage_Customer_Model_Address */
				$customerBillingAddress = $this->getBillingAddress()->exportCustomerAddress();
				$customerAddressId = $this->getBillingAddress()->getCustomerAddressId();
				if ($customerAddressId && $customer->getId()) {
					$customer->getAddressItemById($customerAddressId)->addData($customerBillingAddress->getData());
				} else {
					$customer->addAddress($customerBillingAddress);
				}
			}

			if (!$this->getQuote()->isVirtual() && $this->getShippingAddress()->getSaveInAddressBook()) {
				/** @var $customerShippingAddress Mage_Customer_Model_Address */
				$customerShippingAddress = $this->getShippingAddress()->exportCustomerAddress();
				$customerAddressId = $this->getShippingAddress()->getCustomerAddressId();
				if ($customerAddressId && $customer->getId()) {
					$customer->getAddressItemById($customerAddressId)->addData($customerShippingAddress->getData());
				} elseif (!empty($customerAddressId) && $customerBillingAddress !== null && $this->getBillingAddress()->getCustomerAddressId() == $customerAddressId
				) {
					$customerBillingAddress->setIsDefaultShipping(true);
				} else {
					$customer->addAddress($customerShippingAddress);
				}
			}

			if (is_null($customer->getDefaultBilling()) && $customerBillingAddress) {
				$customerBillingAddress->setIsDefaultBilling(true);
			}

			if (is_null($customer->getDefaultShipping())) {
				if ($this->getShippingAddress()->getSameAsBilling() && $customerBillingAddress) {
					$customerBillingAddress->setIsDefaultShipping(true);
				} elseif ($customerShippingAddress) {
					$customerShippingAddress->setIsDefaultShipping(true);
				}
			}
		} else {
			// Prepare new customer
			/** @var $customerBillingAddress Mage_Customer_Model_Address */
			$customerBillingAddress = $this->getBillingAddress()->exportCustomerAddress();
			$customer->addData($customerBillingAddress->getData())
				->setPassword($customer->generatePassword())
				->setStore($store);
			$customer->setEmail($this->_getNewCustomerEmail($customer));
			$this->_setCustomerData($customer);

			if ($this->getBillingAddress()->getSaveInAddressBook()) {
				$customerBillingAddress->setIsDefaultBilling(true);
				$customer->addAddress($customerBillingAddress);
			}

			/** @var $shippingAddress Mage_Sales_Model_Quote_Address */
			$shippingAddress = $this->getShippingAddress();
			if (!$this->getQuote()->isVirtual() && !$shippingAddress->getSameAsBilling() && $shippingAddress->getSaveInAddressBook()
			) {
				/** @var $customerShippingAddress Mage_Customer_Model_Address */
				$customerShippingAddress = $shippingAddress->exportCustomerAddress();
				$customerShippingAddress->setIsDefaultShipping(true);
				$customer->addAddress($customerShippingAddress);
			} else {
				$customerBillingAddress->setIsDefaultShipping(true);
			}
		}

		// Set quote customer data to customer
		$this->_setCustomerData($customer);

		// Set customer to quote and convert customer data to quote
		$quote->setCustomer($customer);

		// Add user defined attributes to quote
		$form = $this->_getCustomerForm()->setEntity($customer);
		foreach ($form->getUserAttributes() as $attribute) {
			$quoteCode = sprintf('customer_%s', $attribute->getAttributeCode());
			$quote->setData($quoteCode, $customer->getData($attribute->getAttributeCode()));
		}

		if ($customer->getId()) {
			// Restore account data for existing customer
			$this->_getCustomerForm()
				->setEntity($customer)
				->resetEntityData();
		} else {
			$quote->setCustomerId(true);
		}

		return $this;
	}

}
