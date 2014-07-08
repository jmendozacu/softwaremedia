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
    
    public function updateNewCPCPrice($observer) {
	    $collection = Mage::getModel('catalog/product')->getCollection()
			->addAttributeToSelect('*')
			->addAttributeToSelect('new_cpc_price')
			->addattributeToFilter('new_cpc_price', array(array('gt' => '0')))
			->setPageSize(100);
			
		foreach($collection as $product) {
			$cpc = $product->getData('new_cpc_price');
			$product->setData('cpc_price',$cpc);
			$product->setData('new_cpc_price',NULL);
			$product->save();
		}

	    return $this;
    }
 }