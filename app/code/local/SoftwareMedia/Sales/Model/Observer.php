<?php

class SoftwareMedia_Sales_Model_Observer {

	public function salesNote($observer = null) {
		$order = $observer->getEvent()->getOrder();

		if ($order->getCustomerId() && !$order->getId()) {
			$customerId = $order->getCustomerId();
			$lastNote = Mage::getModel('customernotes/notes')->getCollection()->addFieldToFilter('customer_id',$customerId)->addFieldToFilter('static','1');
			
			foreach($lastNote as $note) {
				$order->addStatusHistoryComment("Static Note (" . $note->getNote())->setAdmin($note->getUsername() . ")");
			}
		}
			
	}
	//Monitor newly completed orders to switch status for licensing orders
	public function evaluateLicenseOrder($observer = null) {
		$order = $observer->getEvent()->getOrder();
		$data = $order->getOrigData();
		$oldOrder = Mage::getModel('sales/order')->load($order->getId());
		
		if ($order->getStatus() == 'complete') {
			$shipments = $order->getShipmentsCollection()->setOrder('created_at','desc');
			$shipment = $shipments->getFirstItem();
			
			//Check if order is newly completed by looking at last shipment date
			if ($shipment) {
				if (time() - strtotime($shipment->getCreatedAt()) < 20) {
					$products = Mage::getResourceModel('sales/order_item_collection')->setOrderFilter($order->getId());
					foreach($products as $product) {
						$product = Mage::getModel('catalog/product')->load($product->getProductId());
						if ($product->getLicenseNonlicenseDropdown() == 1210) {
							try {
								$oldOrder->addStatusToHistory('ordered_license_1','Order has Licensing items. Setting status to License Ordered.')->save();
							} catch (Exception $e) {
							    Mage::log($e->getMessage(),NULL,'license.log');
							}
							continue;
						}
					}

				}
			}
		}

		return $this;
	}
}