<?php
/**
 * Substitution controller
 *
 * @category   SoftwareMedia
 * @package    SoftwareMedia_Substition
 * @author     Jeff Losee
 */


class SoftwareMedia_Substitution_Adminhtml_SubstitutionController extends Mage_Adminhtml_Controller_Action
{
	public function addAction() {
		$invoiceId = $this->getRequest()->getParam('invoiceId');
		$productId = $this->getRequest()->getParam('productId');
		
		$invoiceItem = Mage::getModel('sales/order_invoice_item')->load($invoiceId);
		$productItem = Mage::getModel('catalog/product')->load($productId);
		
		$output = array();
        $output['resp'] = $invoiceId;
        
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
			$invoiceItem->setProductId($productId);
			$invoiceItem->setName($productItem->getName());
			$invoiceItem->setSku($productItem->getSku());
			//TODO: Update Order Invoice QTY and Order Invoice Item QTY
			
			$newQty = $invoiceItem->getQty() * $qty;
			Mage::log("invoice qty: " . $newQty);
			
			$invoiceItem->setData('qty',$newQty);
			
			//Load order item and update QTY
			$orderItem = Mage::getModel('sales/order_item')->load($invoiceItem->getOrderItemId());
			$orderItem->setQtyInvoiced($orderItem->getQtyInvoiced() * $qty);
			
			if ($orderItem->getParentItemId()) {
				Mage::log('Has Parent Item');
				$parentInvoice = Mage::getModel('sales/order_invoice_item')->load($orderItem->getParentItemId(),'order_item_id');
				$parentInvoice->delete();
				//$orderItem->setParentItemId(null);
			}
			//Save Order Item
			$orderItem->save();
			
			//Save Order Invoice Item
			Mage::getSingleton('adminhtml/session')->addSuccess("Substitution Added Successfully"); 
			$invoiceItem->save();
			
			$invoice = Mage::getModel('sales/order_invoice')->load($invoiceItem->getParentId());
			$orderId = $invoice->getOrderId();
			
			$order = Mage::getModel('sales/order')->load($orderId);
			
			$message = 'Product ' . $oldProduct->getId() . ' substituted for ' . $productId;
			$order->addStatusHistoryComment($message);
			$order->save();
			
			$newUrl = $this->getUrl('adminhtml/sales_order/view/order_id/' . $orderId);
			$output['resp'] = $newUrl;
		}

        $json = json_encode($output);

         $this->getResponse()
         ->clearHeaders()
         ->setHeader('Content-Type', 'application/json')
         ->setBody($json);
	}
}