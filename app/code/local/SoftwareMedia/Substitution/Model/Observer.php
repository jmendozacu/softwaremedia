<?php
/**
 * Product price sync observer
 *
 * @category   SoftwareMedia
 * @package    SoftwareMedia_Substition
 * @author     Jeff Losee
 */
 
class SoftwareMedia_Substitution_Model_Observer
{
    public function catalog_product_save_after($observer)
    {
        $product = $observer->getProduct();
       Mage::getModel('ocm_fulfillment/observer')->updateByProduct($product);
        $links = Mage::getResourceModel('catalog/product_link');
        $linkModel = Mage::getModel('catalog/product_link');
        
        //Get collection of all substitution links that use this product
        $dollection = $linkModel->setLinkTypeId(6)->getLinkCollection()->addFieldToFilter('linked_product_id', $product->getId());
        $dollection->joinAttributes();
        
        foreach($dollection as $link) {
	        if ($link->getPriceSync()) {
			$syncProd = Mage::getModel('catalog/product')->load($link->getProductId());
			
			//Only save product if prices are different to prcent potential loops
			if ($product->getPrice() != $syncProd->getPrice() || $product->getCpcPrice() != $syncProd->getCpcPrice() || $product->getMsrp() != $syncProd->getMsrp() || $product->getCost() != $syncProd->getCost()) {
				$syncProd->setPrice($product->getPrice());
				$syncProd->setCpcPrice($product->getCpcPrice());
				$syncProd->setMsrp($product->getMsrp());
				$syncProd->save();
			}
	        }
        }

    }
    public function catalog_product_save_before($observer)
    {
        $product = $observer->getProduct();
        $priceCount = 0;
        $autoCount = 0;
        if (!$product->getSubstitutionLinkData())
        	return $observer;
        	
        foreach($product->getSubstitutionLinkData() as $link) {
	        $priceCount += $link['price_sync'];
	        $autoCount += $link['auto'];
        }
        if ($priceCount > 1) {
	        Mage::throwException(Mage::helper('adminhtml')->__('Please select only one substitution to sync price'));
        }
        if ($autoCount > 1) {
	        Mage::throwException(Mage::helper('adminhtml')->__('Please select only one substitution to auto sub'));
        }
       // die(var_dump($product->getSubstitutionLinkData()));
        
    }
    
    public function sales_order_invoice_save_after($observer) {
    	//Exclude comparisson engines from auto sub
    	$exclude = array(1117,1120,1121);
    	
    	$invoice = $observer->getInvoice();
    	$order = Mage::getModel('sales/order')->load($invoice->getOrderId());

    	$invoiceItems = Mage::getModel('sales/order_invoice_item')->getCollection()->addFieldToFilter('parent_id',$invoice->getId());
    	
    	$linkModel = Mage::getModel('catalog/product_link');
    	
    	foreach($invoiceItems as $invoiceItem) {
    		Mage::log('Invoice Item: ' . $invoiceItem->getId() . ' Product: ' . $invoiceItem->getProductId());
	    	//Get collection of all substitution links that use this product
			$dollection = $linkModel->setLinkTypeId(6)->getLinkCollection()->addFieldToFilter('product_id', $invoiceItem->getProductId());
			$dollection->joinAttributes();
			foreach($dollection as $link) {
				Mage::log('Invoice Link Item: ' . $link->getId() . ' Invoice Product Item: ' . $link->getLinkedProductId());
	       		if ($link->getAuto()) {
	       			if (!in_array($order->getCustomerId(),$exclude))
	       				Mage::helper('substitution')->addSub($invoiceItem->getId(),$link->getLinkedProductId());
			   	}
			}
    	}
    	
        
        
    }
}