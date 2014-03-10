<?php
class OCM_Setup_Model_Observer
{
    public function catalog_product_save_before($observer)
    {	

		echo "OBSERVER";    	die();
    
        $product = $observer->getProduct();
        echo "<pre>"; print_r($product->getData()); 
        die();
        exit;
        // do something here
    }
 }