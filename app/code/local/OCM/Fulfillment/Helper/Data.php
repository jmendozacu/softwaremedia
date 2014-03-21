<?php

class OCM_Fulfillment_Helper_Data extends Mage_Core_Helper_Abstract {

	public function updateStock($product) {
		$price_array = array();
		$all_price = array();
		$qty = 0;
		$subItems = $product->getSubstitutionProducts();
		$stock_model = Mage::getModel('cataloginventory/stock_item');
		
		// Ingram MUST be the end of the array for this to work
		foreach (array('techdata','synnex','ingram') as $warehouse_name) {
			if($product->getData($warehouse_name.'_qty') > 0) {
				$price_array[] = $product->getData($warehouse_name.'_price');
				$qty += $product->getData($warehouse_name.'_qty');
			} else {
				if ($product->getData($warehouse_name.'_price'))
					$all_price[] = $product->getData($warehouse_name.'_price');
			}  
		}
		
		$qty += $product->getData('pt_qty');
		if (!$product->getData('pt_avg_cost')) {
			//If no prices from warehouses with QTY, use all prices
			if (count($price_array) == 0 && count($all_price) > 0)
				$price_array = $all_price;
			asort($price_array);
			$lowest_cost = $price_array[0];
			if ($lowest_cost > 0)
				$product->setData('cost',$lowest_cost);
		} else {
			$product->setData('cost',$product->getData('pt_avg_cost'));
		}
	
		$stock_model->loadByProduct($product->getId());
		
		foreach($subItems as $item) {
			foreach (array('techdata','synnex','ingram') as $warehouse_name) {	
				$prod = Mage::getModel('catalog/product')->load($item->getId());
				$qty+=$prod->getData($warehouse_name.'_qty');
			}
			$qty+=$item->getData('pt_qty');
		}
		
		$stock_model->setData('qty',$qty);
		
		//Additional rules for physical items
		
		if($qty) $stock_model->setData('is_in_stock',1);
		
		try {
			$product->save();
			$stock_model->save();
		} catch (Exception $e) {
			Mage::log($e->getMessage());
		}
	}
}