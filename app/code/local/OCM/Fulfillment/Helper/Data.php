<?php

class OCM_Fulfillment_Helper_Data extends Mage_Core_Helper_Abstract {

	public function updateStock($product) {
		$price_array = array();
		$all_price = array();
		$qty = 0;
		$subItems = $product->getSubstitutionProducts();
		$stock_model = Mage::getModel('cataloginventory/stock_item');
		$hasResult = false;

		foreach (array('techdata','synnex','ingram') as $warehouse_name) {
			if (is_numeric($product->getData($warehouse_name.'_qty')) || is_numeric($product->getData($warehouse_name.'_price')))
				$hasResult = true;
				
			if($product->getData($warehouse_name.'_qty') > 0) {
				$price_array[] = $product->getData($warehouse_name.'_price');
				$qty += $product->getData($warehouse_name.'_qty');
			} else {
				if ($product->getData($warehouse_name.'_price'))
					$all_price[] = $product->getData($warehouse_name.'_price');
			}  
		}
		
		/*
		Regarding prods where cost=0 in PT
		If PT qty is greater than or equal to 1, then it should have PT cost = 0
		It PT qty is less than or equal to 0 (i did see a few negative qtys in pt) then it should not be taking PT cost as 0 (should be 		taking ingram/synnex/techdata cost or if all those blank, then subs cost)
		*/
		
		$cost = false;
		
		$qty += $product->getData('pt_qty');
		if ($qty)
			$hasResult = true;
			
		//If no peachtree cost, or pt cost is 0 & no pt qty, use cost from warehouse if available
		if (!is_numeric($product->getData('pt_avg_cost')) || (is_numeric($product->getData('pt_avg_cost')) && $product->getData('pt_avg_cost') == 0 && (!$product->getData('pt_qty') || $product->getData('pt_qty') <= 0))) {
			//If no prices from warehouses with QTY, use all prices
			if (count($price_array) == 0 && count($all_price) > 0)
				$price_array = $all_price;
			asort($price_array);
			$lowest_cost = $price_array[0];
			if ($lowest_cost > 0)
				$cost = $lowest_cost;
		} else {
			//Use PT_avg_cost if not 0, or if 0 and no pt_qty
			//echo $product->getData('pt_avg_cost');
			
			if ((is_numeric($product->getData('pt_qty')) && $product->getData('pt_qty') > 0) || $product->getData('pt_avg_cost') > 0) {
				$cost = $product->getData('pt_avg_cost');
			}
			//die();
		}

		$stock_model->loadByProduct($product->getId());
		
		//Add up QTY 
		foreach($subItems as $item) {
			foreach (array('techdata','synnex','ingram') as $warehouse_name) {	
				if (is_numeric($product->getData($warehouse_name.'_qty')) || is_numeric($product->getData($warehouse_name.'_price')))
					$hasResult = true;
					
				$prod = Mage::getModel('catalog/product')->load($item->getId());
				$qty+=$prod->getData($warehouse_name.'_qty');
				if (!$cost && $prod->getData('cost'))
					$cost = $prod->getData('cost');
			}
			$qty+=$item->getData('pt_qty');
		}
		
		//if ($cost) 
			$product->setData('cost',$cost);
		
		//Additional rules for physical items
		$stock_model->setData('backorders',0);
		if($hasResult && !$qty) {
			$qty = 9999;
			if ($product->getData('package_id')==1085) {
				$stock_model->setData('backorders',1);
				$stock_model->setData('use_config_backorders',0);
			}
		}
		
		if($qty) {
			$stock_model->setData('is_in_stock',1);
		} else {
			$stock_model->setData('is_in_stock',0);
		}
		
		$stock_model->setData('qty',$qty);
		try {
			$product->save();
			$stock_model->save();
		} catch (Exception $e) {
			Mage::log($e->getMessage());
		}
	}
}