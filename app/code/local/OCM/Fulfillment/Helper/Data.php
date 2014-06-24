<?php

class OCM_Fulfillment_Helper_Data extends Mage_Core_Helper_Abstract {

	public function updateStock($product) {
		$price_array = array();
		$all_price = array();
		$qty = 0;
		
		//Get subtitution links
		$links = $product->getSubstitutionLinkCollection();

		$stock_model = Mage::getModel('cataloginventory/stock_item');
		$hasResult = false;
		$new_stock_model = Mage::getModel('cataloginventory/stock_item');
		
		//Mage::log('Updating Stock: ' . $product->getSku(),null,'stock.log');
		
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
			
		//If no peachtree cost, or no pt qty, use cost from warehouse if available
		if (!is_numeric($product->getData('pt_avg_cost')) || (!$product->getData('pt_qty') || $product->getData('pt_qty') <= 0)) {
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
		
		//In cases where there was pt_avg_cost but no pt_qty update cost to reflect pt_avg cost
		if (!$cost && $product->getData('pt_qty'))
			$cost = $product->getData('pt_avg_cost');
			
		//Use cost_override if available
		if ($product->getData('cost_override'))
			$cost = $product->getData('cost_override');
			
			
		$stock_model->loadByProduct($product->getId());
		
		//Add up QTY 
		foreach($links as $link) {
			$item = Mage::getModel('catalog/product')->load($link->getLinkedProductId());
			$sub_model = Mage::getModel('cataloginventory/stock_item');
			$sub_model->loadByProduct($item->getId());
			$qty += $sub_model->getData('qty');
			
			if ($sub_model->getData('manage_stock') == 0)
				$qty = 9999;
			
			//Mage::log('Manage Stock: ' . $sub_model->getData('manage_stock'),null,'stock.log');
			
			foreach (array('techdata','synnex','ingram') as $warehouse_name) {	
				if (is_numeric($product->getData($warehouse_name.'_qty')) || is_numeric($product->getData($warehouse_name.'_price')))
					$hasResult = true;
					
				$prod = Mage::getModel('catalog/product')->load($item->getId());
				//$qty+=$prod->getData($warehouse_name.'_qty');
				if (!$cost && $prod->getData('cost'))
					$cost = $prod->getData('cost') * $link->getQty();
			}
			$qty+=$item->getData('pt_qty');
		}
		
		//if ($cost) 
			$product->setData('cost',$cost);
		
		//Additional rules for physical items
		$stock_model->setData('backorders',0);
		if($hasResult && (!$qty || $qty < 0)) {
			$qty = 9999;
			
			//If physical set backorder status
			if ($product->getData('package_id')==1085 || $product->getData('package_id')==1216) {
				$stock_model->setData('backorders',1);
				$stock_model->setData('use_config_backorders',0);
				$qty = 0;
			}
		}elseif ($product->getData('package_id')==1084 && $hasResult) {
			$qty = 9999;
		}
				
		//Add stock of simple products
		if ($product->getTypeId() == 'configurable') {
			//echo "config";
			
			$conf = Mage::getModel('catalog/product_type_configurable')->setProduct($product);
			$col = $conf->getUsedProductCollection()->addAttributeToSelect('*')->addFilterByRequiredOptions();
			foreach($col as $simple_product){
				$new_stock_model->loadByProduct($simple_product->getId());
				$qty += $new_stock_model->getData('qty');
			}
		}

		if($qty || $stock_model->getData('backorders') == 1) {
			$stock_model->setData('is_in_stock',1);
			//Mage::log('IN STOCK: ' . $qty,null,'stock.log');
		} else {
			$stock_model->setData('is_in_stock',0);
			//Mage::log('OUT OF STOCK: ' . $qty,null,'stock.log');
		}
		
		$stock_model->setData('qty',$qty);
		try {
			$product->save();
			$stock_model->save();
		} catch (Exception $e) {
			Mage::log($e->getMessage(),null,'fulsave.log');
		}
	}
}