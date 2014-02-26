<?php

class OCM_Quotedispatch_Model_Observer {

	public function blockQuotedispatchUpdates($observer) {

		$cart = $observer->getCart();
		$quotedispatch_ids = Mage::getSingleton('core/session')->getQuotedispatchIds();
		$error = array();

		if ($quotedispatch_ids) {

			$collection = Mage::getModel('quotedispatch/quotedispatch_items')->getCollection()
				->addFieldToFilter('quotedispatch_id', array('in' => $quotedispatch_ids));

			$quote_item_values = array();
			$quote_id_item_values = array();

			foreach ($collection as $item) {
				$quote_item_values[$item->getProductId()] = $item->getQty();

				if (!isset($quote_id_item_values[$item->getQuotedispatchId()])) {
					$quote_id_item_values[$item->getQuotedispatchId()] = array();
				}
				$quote_id_item_values[$item->getQuotedispatchId()][$item->getProductId()] = $item->getQty();
			}

			$cart_items = array();
			foreach ($cart->getQuote()->getAllItems() as $item) {

				$cart_items[] = $item->getProductId();

				if (isset($quote_item_values[$item->getProductId()]) && $item->getQty() != $quote_item_values[$item->getProductId()]) {

					$item->setQty($quote_item_values[$item->getProductId()]);

					Mage::getSingleton('checkout/session')->addError(Mage::helper('quotedispatch')->__('Item %s cannot be modified it is part of a quote', $item->getName()));
					$error[] = array(
						'id' => $item->getId(),
						'name' => $item->getName(),
					);
				}
			}

			//stop removal of quote items
			foreach ($quote_id_item_values as $quotedispatch_id => $quote_item_values) {

				$diff = array_diff(array_keys($quote_item_values), $cart_items);
				$missing_items = array_intersect($diff, array_keys($quote_item_values));
				$remove_quotedispatch = array_diff(array_keys($quote_item_values), $missing_items);

				if (count($missing_items) && count($remove_quotedispatch)) {

					$collection = Mage::getModel('quotedispatch/quotedispatch_items')->getCollection()
						->addFieldToFilter('quotedispatch_id', $quotedispatch_id)
						->addFieldToFilter('product_id', array('in' => $missing_items))
					;

					foreach ($collection as $item) {

						// TODO : Same code as in index move to helper or model
						$product = Mage::getModel('catalog/product')->load($item->getProductId());
						$request = new Varien_Object(array('qty' => $item->getQty()));
						$quote = Mage::getSingleton('checkout/session')->getQuote();
						$result = $quote->addProduct($product, $request);


						if (is_string($result)) {
							Mage::throwException($result);
						}

						// use quote price for item
						$result->setCustomPrice($item->getPrice());
						$result->setOriginalCustomPrice($item->getPrice());
						$result->getProduct()->setIsSuperMode(true);

						// hack - cart displays empty template if we do not increment
						$items_count = $quote->getItemsCount() + 1;
						$quote->setItemsCount($items_count);

						$quote->save();
						Mage::dispatchEvent('checkout_cart_product_add_after', array('quote_item' => $result, 'product' => $result->getProduct()));

						Mage::getSingleton('checkout/session')->addError(Mage::helper('quotedispatch')->__('You cannot remove individual items from quote', $item->getName()));

						// REFACTOR : this object might be loaded previously if so lets pass it around instead of reloading
						$quotedispatch = Mage::getModel('quotedispatch/quotedispatch')->load($quotedispatch_id);
						$error[] = array(
							'id' => $quotedispatch_id,
							'name' => $quotedispatch->getTitle(),
						);
					}
				} else if (count($missing_items) && !count($remove_quotedispatch)) {
					$quote_model = Mage::getModel('quotedispatch/quotedispatch')->load($quotedispatch_id);
					$quote_name = $quote_model->getTitle();
					$ids = Mage::getSingleton('core/session')->getQuotedispatchIds();
					$ids = array_diff($ids, array($quote_model->getId()));
					Mage::getSingleton('core/session')->setQuotedispatchIds($ids);
					if (!Mage::registry('remove' . md5($quote_name))) {
						Mage::register('remove' . md5($quote_name), true);
						Mage::getSingleton('checkout/session')->addSuccess(Mage::helper('quotedispatch')->__('Quote "%s" has been removed', $quote_name));
					}
				}
			}

			if (count($error)) {
				foreach ($error as $item) {
					Mage::getSingleton('checkout/session')->addError(Mage::helper('quotedispatch')->__('<a href="%s">Click here to remove quote "%s" from cart</a>', Mage::getBaseUrl() . 'quotedispatch/index/removefromcart/?id=' . $item['id'], $item['name']));
				}
			}
		}
		return;
	}

	public function markPurchased() {
		$quotedispatch_ids = Mage::getSingleton('core/session')->getQuotedispatchIds();

		$collection = Mage::getModel('quotedispatch/quotedispatch')->getCollection()
			->addFieldToFilter('quotedispatch_id', array('in' => $quotedispatch_ids));

		foreach ($collection as $quote) {
			$quote->setStatus(OCM_Quotedispatch_Model_Status::$purchased_status);
			$quote->save();
		}
		Mage::getSingleton('core/session')->unsQuotedispatchIds();
		return;
	}

	public function sendReminders() {
		$model = Mage::getModel('quotedispatch/quotedispatch');
		$collection = $model->getCollection()
			->addAvailableStatusFilter()
			->addReminderDelayFilter()
			->addQuoteSubtotal()
		;

		Mage::log('Quote count: ' . count($collection));

		foreach ($collection as $quote) {
			try {
				$sales_rep = null;

				$created_by = $quote->getCreatedBy();
				if (empty($created_by)) {
					$sales_rep = Mage::getModel('admin/user')->loadByUsername('cservice');
				} else {
					$sales_rep = Mage::getModel('admin/user')->loadByUsername($quote->getCreatedBy());
				}
				Mage::helper('quotedispatch')->sendEmail($quote, Mage::helper('quotedispatch')->__('Reminder : Quote is ready for purchase'), $sales_rep);
				$quote->save();
			} catch (Exception $e) {
				Mage::log($quote->getId() . ' failed to remind / update: ' . $e->getMessage(), null, 'quotedispatch.log');
			}
		}
		return $this;
	}

	public function expireQuotes() {
		$model = Mage::getModel('quotedispatch/quotedispatch');
		$collection = $model->getCollection()
			->addAvailableStatusFilter(true)
			->addExpiredFilter()
		;

		foreach ($collection as $quote) {
			try {
				$quote->setAvailableTime(null);
				$quote->setStatus(Mage::getModel('quotedispatch/status')->expired_status);
				$quote->save();
			} catch (Exception $e) {
				Mage::log($quote->getId() . ' failed to expire: ' . $e->getMessage(), null, 'quotedispatch.log');
			}
		}
		return $this;
	}

}
