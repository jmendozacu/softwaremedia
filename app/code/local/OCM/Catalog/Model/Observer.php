<?php
class OCM_Catalog_Model_Observer
{
    public function catalog_product_save_before($observer)
    {
        $product = $observer->getProduct();
        //$product->getData()
        if ($product->getPackageId() == 1084) {
	        $product->setTaxClassId(5);
	        //die();
        } elseif ($product->getPackageId() == 1085) {
        	$product->setTaxClassId(2);
        }
        // do something here
        
        return $this;
    }
 }