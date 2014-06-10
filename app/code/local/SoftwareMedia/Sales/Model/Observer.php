<?php

class SoftwareMedia_Sales_Model_Observer {

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
								$order->addStatusToHistory('ordered_license_1','Order has Licensing items. Setting status to License Ordered.')->save();
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