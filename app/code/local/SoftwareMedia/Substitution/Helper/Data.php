<?php

class SoftwareMedia_Substitution_Helper_Data extends Mage_Core_Helper_Abstract {
	public function isComplete($invoiceId) {
		$invoice =  Mage::getModel('sales/order_invoice')->load($invoiceId);
		$order = $invoice->getOrder();

		if ($order->getStatusLabel() == 'Complete')
			return true;
			
		return false;
	}
	
	public function getInvoiceItemFromOrderItem($orderItemId) {
		$invoiceItem =  Mage::getModel('sales/order_invoice_item')->load($orderItemId,'order_item_id');
		return $invoiceItem;
	}
	
	public function addSub($invoiceId, $productId) {
		$invoiceItem = Mage::getModel('sales/order_invoice_item')->load($invoiceId);
		$productItem = Mage::getModel('catalog/product')->load($productId);
        
        //Load QTY from link collection
        $qty = 1;
		$oldProduct = Mage::getModel('catalog/product')->load($invoiceItem->getProductId());
		$subs = $oldProduct->getSubstitutionLinkCollection();
		foreach($subs as $sub) {
			if ($sub->getLinkedProductId() == $productId) {
				$qty = $sub->getQty();
			}
		}
		
		if ($productItem) {
			//Update Invoice Item
			$invoiceItem->setProductId($productId);
			$invoiceItem->setName($productItem->getName());
			$invoiceItem->setSku($productItem->getSku());
			
			//TODO: Update Order Invoice QTY and Order Invoice Item QTY
			$newQty = $invoiceItem->getQty() * $qty;
			$invoiceItem->setData('qty',$newQty);
			$invoiceItem->save();
			
			//Load order item and update QTY
			$orderItem = Mage::getModel('sales/order_item')->load($invoiceItem->getOrderItemId());
			$orderItem->setQtyInvoiced($orderItem->getQtyInvoiced() * $qty);
			
			//Save Order Item
			$orderItem->save();
			
			if ($orderItem->getParentItemId()) {
				Mage::log('Has Parent Item');
				$parentInvoice = Mage::getModel('sales/order_invoice_item')->load($orderItem->getParentItemId(),'order_item_id');
				if ($parentInvoice) {
					//If product has a parent, substitute parent product instead. 
					$parentInvoice->setProductId($productId);
					$parentInvoice->setName($productItem->getName());
					$parentInvoice->setSku($productItem->getSku());
					$parentInvoice->setData('qty',$newQty);
					$parentInvoice->save();
	
					$invoiceItem->delete();
				}
			}

			//Save Order Invoice Item
			Mage::getSingleton('adminhtml/session')->addSuccess("Substitution Added Successfully"); 
			
			$invoice = Mage::getModel('sales/order_invoice')->load($invoiceItem->getParentId());
			$orderId = $invoice->getOrderId();

			$order = Mage::getModel('sales/order')->load($orderId);
			
			$message = 'Product ' . $oldProduct->getSku() . ' substituted for ' . $productItem->getSku();
			$order->addStatusHistoryComment($message);
			$order->save();

			return true;
		}
		
		return false;
	}
}