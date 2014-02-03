<?php
class OCM_Fulfillment_Model_Observer
{
	const FULFILLMENT_PAGE_SIZE     = 50;
	const FULFILLMENT_UPDATE_DELAY     = 30;
	
    public function evaluateOrdersDaily()
    {
        $orders = Mage::getModel('sales/order')->getCollection();
        $orders->addFieldToFilter('status','processing');
		//$orders->addFieldToFilter('state','new');

        foreach($orders as $order){
            $is_virtual = false;
            $is_physical = false;
            $items = $order->getAllVisibleItems();
            foreach($items as $item){
            	$prod = Mage::getModel('catalog/product')->load($item->getProductId());
				//echo $item->getProductId();
		
                if($prod->getData('package_id')==1084){
                    $is_virtual = true;
                } else {
	                $is_physical = true;
                }
            }

            if($is_virtual){ 
            
            	//Order has both physical and electronic items
            	if ($is_physical) {
	            	$order->setStatus('multipleproductorder')->save();
	            	continue;
            	}
            	
            	//order has ONLY virtual products
                $model = Mage::getModel('ocm_fulfillment/license')->getCollection()
                    ->addFieldToFilter('order_id',$order->getId())->getFirstItem();
                    
                if(!$model->getId()){
                    $model->setOrderId($order->getId())->setStatus('Not assigned');
                    $model->save();
                }
				$order->setStatus('needslicense')->save();
				continue;
            } else{ // order has ANY physical products:
            
                // check if shipping to California, Massachusetts, or Tennessee
                $stop_states = $this->_getStopStates();
                if (in_array($order->getShippingAddress()->getRegionId(),$stop_states)) {
                    //set order to "Process Manually" here
                    $order->setStatus('processmanually')->save();
                    continue;
                }
			
                // Get All warehouse availability 
                $warehouse_model = Mage::getModel('ocm_fulfillment/warehouse');
                $warehouse_model->loadWarehouseData($items);
                
                // decide delivery method
                // check internal stock or if warehouse can complete
                // internal is first, and all others are in order of preference
                $warehousesFulfill = array();
                
                $done = false;
                foreach($warehouse_model->warehouses as $warehouse_name) {
                	echo $warehouse_name . " - " . $warehouse_model->getData($warehouse_name)->getCanFulfill() . "<br />";
                    if ($warehouse_model->getData($warehouse_name)->getCanFulfill()) {
                    	$warehousesFulfill[$warehouse_name] = $warehouse_model->getData($warehouse_name)->getTotalCost();
                        //fulfill with warehouse here
                        //$warehouse_model->getWarehouseModel($warehouse_name)->fulfill($order , $order->getAllItems());
                        
                        //if internal stock, always use
                        //set order to complete here
                        if ($warehouse_name == 'peachtree') {
	                        $order->setStatus($warehouse_name)->save();
	                        $done = true;
	                        break;
                        }
                        
                    }
                  
                 }
                 if ($done) continue;
                 
                 //Sort warehouses by cost
                 asort($warehousesFulfill);
                 $warehouseKeys = array_keys($warehousesFulfill);
                 $warehouseCount = count($warehouseKeys);
                 
                 //If Multiple warehouses, check if cheapest is greater than threshhold
                 if ($warehouseCount > 1) {
	                 if ($warehousesFulfill[$warehouseKeys[$warehouseCount - 1]] - $warehousesFulfill[$warehouseKeys[0]] >= 10) 							{
		                 $done = 1;
		                 $order->setStatus($warehouseKeys[0])->save();
		                 $warehouse_model->getWarehouseModel($warehouseKeys[0])->fulfill($order , $order->getAllItems());
		                 continue;
	                 }
                
                 }
                 
                 //If cheapest is not under threshhold, process in order of priority
                 foreach($warehouse_model->warehouses as $warehouse_name) {
                 	if (array_key_exists($warehouse_name, $warehousesFulfill)) {
                 		$warehouse_model->getWarehouseModel($warehouse_name)->fulfill($order , $order->getAllItems());
                        //set order to complete here
                        $order->setStatus($warehouse_name)->save();
                        $done = true;
                        break;
                 	}
                 }
                 
	                
                if ($done) continue;
                                
                // attempt to complete with multiple warehouses
              
                // $multi_fulfillment = array();
                // $request_qtys = array();
                // foreach($items as $item) {
                    // $request_qtys[$item->getId()] = $item->getQty();
                // }

                // foreach ($warehouse_model->warehouses as $warehouse_name) {
                
                    // $warehouse = $warehouse_model->getData($warehouse_name);
                    
                    // if(!isset($unfulfilled_items)) $unfulfilled_items = $warehouse->getAllItems();
                    
                    // foreach ($warehouse->getAllItems() as $id => $item) {
                        // if ($item->getQty() > $request_qtys[$id]) {
                        
                            // $multi_fulfillment[$warehouse_name][$id] = new Varien_Object(array(
                                // 'sku' => $item->getSku(),
                                // 'qty' => $request_qtys[$id],
                            // ));
                            // unset($unfulfilled_items[$id]);
                        // }
                    // }
                // }
                
                // if (!count($unfulfilled_items)) {
                    
                    // //process orders at respected warehouses
                    // foreach ($multi_fulfillment as $warehouse_name => $items) {
                        
                        // $warehouse_model->getWarehouseModel($warehouse_name)->fulfill($order , $items);
                        
                    // }
                    // $order->setStatus('complete')->save();
                // }
            
                // default fulfillment method
                
                //set order to "Process Manually" here
				if(!$done){
					$order->setStatus('processmanually')->save();
				}
            
            }
        }
        return $this;
    }

    protected function _getStopStates() {
        
        //refactor add state codes to constant or better yet system>config
        
        $stop_states = array();
        $model = Mage::getModel('directory/region');
        $stop_states[] = $model->loadByCode('CA', 'US')->getId();
        $stop_states[] = $model->loadByCode('MA', 'US')->getId();
        $stop_states[] = $model->loadByCode('TN', 'US')->getId();
        
        return $stop_states;
        
    }


    protected function _prepareShipment($invoice,$savedQtys)
    {
        $shipment = Mage::getModel('sales/service_order', $invoice->getOrder())->prepareShipment($savedQtys);
        if (!$shipment->getTotalQty()) {
            return false;
        }


        $shipment->register();

        return $shipment;
    }


    public function updatePricesQty() {
        try {
            Mage::getModel('ocm_fulfillment/warehouse_synnex')->urlConnect();
        } catch (Exception $e) {
            Mage::log('Synnex Update Failed with : '.$e->getMessage());
        }
        
        return $this;
    }



    protected function _selectCountPage() {
        $catalog_size = Mage::getModel('catalog/product')->getCollection()->getSize();
        $page_size = ceil($catalog_size / 14);
        
        $day = date('N');
        $meridiem = (date('a') == 'am') ? 0 : 7;
        
        $current_page = $day + $meridium;
        
        return array($page_size,$current_page);
    }


    public function updateProductWarehouseData($page_override = false) {
    
        list($page_size,$current_page) = $this->_selectCountPage();
        
        if ($page_override) {
            $current_page = $page_override;
        }
        
        Mage::log('RUNNING',null,'fulfillment.log');
    
		$time = time();
		$to = date('Y-m-d H:i:s', $time);
		$lastTime = $time - (self::FULFILLMENT_UPDATE_DELAY*60*60); // 60*60*24
		$from = date('Y-m-d H:i:s', $lastTime);

		$target = time() - (60 * 60 * 23);
        $collection = Mage::getModel('catalog/product')->getCollection()
			->addAttributeToSelect('warehouse_updated_at','left')
            ->addattributeToFilter('warehouse_updated_at',array(array('lt' => $from),array('null' => true)))
            ->addAttributeToSelect('visibility')
            ->addAttributeToSelect('package_id')
            //->addAttributeToFilter('sku','AD-65096937')
/*          //->addattributeToFilter('ingram_micro_usa',array('notnull'=>true))
            //->addAttributeToSelect('cpc_price')
            //->addattributeToFilter('ingram_micro_usa',array('notnull'=>true))
            //->addAttributeToSelect('price')
            //->addAttributeToSelect('qty')
*/
           
            ->addAttributeToSelect('pt_avg_cost')
            ->addAttributeToSelect('pt_qty')
            ->setPageSize(self::FULFILLMENT_PAGE_SIZE);
            //->setCurPage($current_page);
            
        Mage::log('Loading TechData',null,'fulfillment.log');        
        $techdata = Mage::getModel('ocm_fulfillment/warehouse_techdata')->loadCollectionArray($collection);
        Mage::log('Loading Ingram',null,'fulfillment.log');      
        $ingram = Mage::getModel('ocm_fulfillment/warehouse_ingram')->loadCollectionArray($collection);
        Mage::log('Loading Synnex',null,'fulfillment.log');      
        $synnex = Mage::getModel('ocm_fulfillment/warehouse_synnex')->loadCollectionArray($collection);
        Mage::log('Loading Done',null,'fulfillment.log');      
        
        $techdata_sku_attr = OCM_Fulfillment_Model_Warehouse_Techdata::TECH_DATA_SKU_ATTR;
        $synnex_sku_attr   = OCM_Fulfillment_Model_Warehouse_Synnex::SYNNEX_SKU_ATTR;
        $ingram_sku_attr   = OCM_Fulfillment_Model_Warehouse_Ingram::INGRAM_SKU_ATTR;

        $techdata_products = $techdata->getCollectionArray();
        $synnex_products   = $synnex->getCollectionArray();
        $ingram_products   = $ingram->getCollectionArray();

        $stock_model = Mage::getModel('cataloginventory/stock_item');
       
       foreach($collection as $product) {
       		$product->setData('warehouse_errors',"");
           //skip products not in warehouse system
           if(!$product->getData($techdata_sku_attr) && !$product->getData($synnex_sku_attr) && !$product->getData($ingram_sku_attr)) {	
            	$product->setData('warehouse_errors',"No Warehouse SKUs Available");
            	$product->setData('warehouse_updated_at',now());
            	Mage::log('No Warehouse SKUs Available ' . $product->getSku(),null,'fulfillment.log');

            try {
               $product->save();
			} catch (Exception $e) {
            	   Mage::log($e->getMessage());
			}
			continue;
           }
           $price_array = array();
           $qty = 0;
           
           // Ingram MUST be the end of the array for this to work
           foreach (array('techdata','synnex','ingram') as $warehouse_name) {
           
               if(is_array(${$warehouse_name.'_products'}[ $product->getData(${$warehouse_name.'_sku_attr'}) ])) {
                   $product->setData($warehouse_name.'_price',${$warehouse_name.'_products'}[ $product->getData(${$warehouse_name.'_sku_attr'}) ]['price']);
                   $product->setData($warehouse_name.'_qty',${$warehouse_name.'_products'}[ $product->getData(${$warehouse_name.'_sku_attr'}) ]['qty']);
    
                   if($product->getData($warehouse_name.'_qty') > 0) {
                       $price_array[ $product->getData($warehouse_name.'_price') ] = true;
                       $qty += $product->getData($warehouse_name.'_qty');
                   }
                   Mage::log('Updated ' . $product->getSku() . $warehouse_name,null,'fulfillment.log');
               } else {
               	$sku = $product->getData(${$warehouse_name.'_sku_attr'});
	           	if (isset($sku)) {
	           		$product->setData('warehouse_errors','No Warehouse Match for SKU ' . $sku . " -> " . $warehouse_name);
			   		Mage::log('No Warehouse Match for SKU ' . $product->getSku() . " -> " . $sku . " -> " . $warehouse_name,null,'fulfillment.log');
	           	}
	           	//echo $sku . " SKU"; 
               }
               
           }

           if ($product->getData('pt_qty')<1) {
               ksort($price_array);
               reset($price_array);
               $lowest_cost = key($price_array);
               $product->setData('cost',$lowest_cost);
           } else {
               $product->setData('cost',$product->getData('pt_avg_cost'));
           }
           $product->setData('warehouse_updated_at',now());
           
           $stock_model->loadByProduct($product->getId());
           $stock_model->setData('qty',$qty);
           if($qty) $stock_model->setData('is_in_stock',1);

           
           try {
               $product->save();
               $stock_model->save();
           } catch (Exception $e) {
               Mage::log($e->getMessage());
           }
           
       }
    }


}
