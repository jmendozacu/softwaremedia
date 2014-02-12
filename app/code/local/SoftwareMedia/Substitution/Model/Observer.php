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
        
        $links = Mage::getResourceModel('catalog/product_link');
        $linkModel = Mage::getModel('catalog/product_link');
        
        $linkList = $links->getParentIdsByChild($product->getId(),6);
        
        //Get collection of all substitution links that use this product
        $dollection = $linkModel->setLinkTypeId(6)->getLinkCollection()->addFieldToFilter('linked_product_id', $product->getId());
        $dollection->joinAttributes();
        
        foreach($dollection as $link) {
	        if ($link->getPriceSync()) {
			$syncProd = Mage::getModel('catalog/product')->load($link->getProductId());
			
			//Only save product if prices are different to prcent potential loops
			if ($product->getPrice() != $syncProd->getPrice() || $product->getCpcPrice() != $syncProd->getCpcPrice() || $product->getMsrp() != $syncProd->getMsrp()) {
				$syncProd->setPrice($product->getPrice());
				$syncProd->setCpcPrice($product->getCpcPrice());
				$syncProd->setMsrp($product->getMsrp());
				$syncProd->save();
			}
	        }
        }

    }
}