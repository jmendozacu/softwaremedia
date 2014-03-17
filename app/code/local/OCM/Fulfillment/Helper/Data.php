<?php

class OCM_Fulfillment_Helper_Data extends Mage_Core_Helper_Abstract {

	public function updateStock($product) {
		$price_array = array();
	    $qty = 0;
	    $subItems = $product->getSubstitutionProducts();
	     $stock_model = Mage::getModel('cataloginventory/stock_item');
		// Ingram MUST be the end of the array for this to work
	           foreach (array('techdata','synnex','ingram') as $warehouse_name) {
	                   if($product->getData($warehouse_name.'_qty') > 0) {
	                       $price_array[] = $product->getData($warehouse_name.'_price');
	                       $qty += $product->getData($warehouse_name.'_qty');
	                   }	               
	           }
			   
			   $qty += $product->getData('peachtree_qty');
			   if (!$product->getData('pt_avg_cost')) {
               asort($price_array);
               $lowest_cost = $price_array[0];
               if ($lowest_cost > 0)
              	 $product->setData('cost',$lowest_cost);
              	 else
			   	$product->setData('cost',$product->getData('pt_avg_cost'));
           } else {
               $product->setData('cost',$product->getData('pt_avg_cost'));
           }
           
	           
	           
	           $stock_model->loadByProduct($product->getId());
	           
	           foreach($subItems as $item) {
		       		foreach (array('techdata','synnex','ingram') as $warehouse_name) {	
		       			$prod = Mage::getModel('catalog/product')->load($item->getId());
		       			$qty+=$prod->getData($warehouse_name.'_qty');
		       			//Mage::log("QTY " . $prod->getSku() . '-' . $warehouse_name . ": " . $prod->getData($warehouse_name.'_qty'), null, "fullfillment.log");
		       		}
		       		$qty+=$item->getData('pt_qty');
	           }
			   
			   $stock_model->setData('qty',$qty);
			   
			   //Additional rules for physical items
			   
	           if($qty) $stock_model->setData('is_in_stock',1);
           
           try {
               $product->save();
               $stock_model->save();
                Mage::log("SAVE " . $qty, null, "fullfillment.log");
           } catch (Exception $e) {
               Mage::log($e->getMessage());
           }
	}
}