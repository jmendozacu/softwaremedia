<?php
class OCM_Fulfillment_Model_Observer
{
	const FULFILLMENT_PAGE_SIZE     = 50;
	const FULFILLMENT_UPDATE_DELAY     = 24;
	const DEFAULT_ATTRIBUTE_SET_ID = 9;
	const RETAIL_ATTRIBUTE_SET_ID = 81;
	
	const TAG_WAREHOUSE_ID = 1;
	const TAG_LICENSING_ID = 2;
	const TAG_DOWNLOAD_ID = 3;
	const TAG_CS = 4;
	const TAG_SUB = 29;
	
    public function evaluateOrdersDaily()
    {
        $orders = Mage::getModel('sales/order')->getCollection();
        $orders->addFieldToFilter('status','processing');
		//$orders->addFieldToFilter('state','new');
		$tagToOrderResource = Mage::getResourceModel('ordertags/orderidtotagid');
		
		$oAitcheckoutfields  = Mage::getModel('aitcheckoutfields/aitcheckoutfields');
		
		Mage::log('Warehouse 1',null,'sort.log');
		
        foreach($orders as $order){
        
        	$orderHistory = Mage::getModel('sales/order_status_history')->getCollection()
                ->addFieldToFilter('parent_id', $order->getId())
                ->addFieldToFilter('status','complete');
                
            if (count($orderHistory) > 0) {
	            Mage::log($order->getId(),null,'fulfillment_observer.log');
	            continue;
            }
                
            $is_virtual = false;
            $is_physical = false;
            $is_download = false;
            $is_license = false;
            $iStoreId = $order->getStoreId();
            $aCustomAtrrList = $oAitcheckoutfields->getOrderCustomData($order->getId(), $iStoreId, true, true); 
            if ($aCustomAtrrList) {
	            foreach($aCustomAtrrList as $aItem) {
		            if ($aItem['code'] == 'comment' && !empty($aItem['value'])) {
			            $tagToOrderResource->addIntoDB($order->getId(), self::TAG_CS);
			            $order->setState('processing','processing','Order has comments. Tagging Customer Service',FALSE)->save();
		            }
	            }
            }
            
            $shippingMethod = $order->getShippingMethod();
            
            $items = $order->getAllItems();
            foreach($items as $item){
            	if ($item->getHasChildren())
            		continue;
            		
            	$prod = Mage::getModel('catalog/product')->load($item->getProductId());
				$links = $prod->getSubstitutionLinkCollection();
				
                if($prod->getData('package_id')==1084){
                    $is_virtual = true;
                } else {
	                $is_physical = true;
                }
                
                
				if (substr($prod->getSku(),-2) == 'DL') {
					$is_download = true;
				} elseif ($prod->getLicenseNonlicenseDropdown() == 1210)					
					$is_license = true;
				elseif ($shippingMethod == 'productmatrix_Free_Electronic_Delivery') {
					$is_download = true;
					$is_physical = false;
					$is_virtual = true;
				}
					
            }

			if (count($links) > 0)
				$tagToOrderResource->addIntoDB($order->getId(), self::TAG_SUB);
				
            if($is_virtual){ 
            	Mage::log('Warehouse Virtual',null,'sort.log');
            	//Order has both physical and electronic items
            	if ($is_physical) {
            		if ($is_download) {
            			$tagToOrderResource->addIntoDB($order->getId(), self::TAG_DOWNLOAD_ID);
            		}
            		if ($is_license) {
            			$tagToOrderResource->addIntoDB($order->getId(), self::TAG_LICENSING_ID);
            		}
            		$tagToOrderResource->addIntoDB($order->getId(), self::TAG_WAREHOUSE_ID);
	            	$order->setState('processing','multipleproductorder','Order has both physical and electronic items. Setting status to \'Multiple Product Order\'.',FALSE)->save();
	            	continue;
            	}
            	
            	if ($is_license) {
            		if ($is_download) {
            			$tagToOrderResource->addIntoDB($order->getId(), self::TAG_LICENSING_ID);
            			$tagToOrderResource->addIntoDB($order->getId(), self::TAG_DOWNLOAD_ID);
						$order->setState('processing','multipleproductorder','Order contains download and licensing items. Setting status to \'Multiple Product Order\'.',FALSE)->save();
						continue;
					} else {
						$tagToOrderResource->addIntoDB($order->getId(), self::TAG_LICENSING_ID);
						$order->setState('processing','needslicense','Order contains only license products. Setting status to \'Licensing - Needs License\'.',FALSE)->save();
						continue;
					}
				}
				if ($is_download) {
					$tagToOrderResource->addIntoDB($order->getId(), self::TAG_DOWNLOAD_ID);
					$order->setState('processing','download','Order contains only download products. Setting status to \'Download\'.',FALSE)->save();
					continue;
				}
				
				$order->setState('processing','processmanually','Electronic item - does not match download or license rules.', FALSE)->save();
            } else { // order has ANY physical products:
           
				Mage::log('Warehouse Physical',null,'sort.log');
                // Get All warehouse availability 
                $warehouse_model = Mage::getModel('ocm_fulfillment/warehouse');
                $warehouse_model->loadWarehouseData($items);
                
                // decide delivery method
                // check internal stock or if warehouse can complete
                // internal is first, and all others are in order of preference
                $warehousesFulfill = array();
                
                $done = false;
                foreach($warehouse_model->warehouses as $warehouse_name) {
                    if ($warehouse_model->getData($warehouse_name)->getCanFulfill()) {
                    	$warehousesFulfill[$warehouse_name] = $warehouse_model->getData($warehouse_name)->getTotalCost();
                        //fulfill with warehouse here
                        //$warehouse_model->getWarehouseModel($warehouse_name)->fulfill($order , $order->getAllItems());
                        
                        //if internal stock, always use
                        if ($warehouse_name == 'peachtree') {
                        	$tagToOrderResource->addIntoDB($order->getId(), self::TAG_WAREHOUSE_ID);
	                        $order->setState('processing',$warehouse_name,'Product is available in warehouse, use internal stock. Setting status to \'Warehouse\'.',FALSE)->save();
	                        $done = true;
	                        break;
                        }
                        
                    }
                  
                 }
                 if ($done) continue;
                 
                 
                //check if shipping to California, Massachusetts, or Tennessee
                $stop_states = $this->_getStopStates();
                if (in_array($order->getShippingAddress()->getRegionId(),$stop_states)) {
                
                	//If shipping to Tennessee, use Synnex if available
                	if ($order->getShippingAddress()->getRegionId() == $stop_states['TN']) {
	                	if ($warehousesFulfill['synnex']) {
	                		$tagToOrderResource->addIntoDB($order->getId(), self::TAG_WAREHOUSE_ID);
		                	$order->setState('processing','synnex','Shipping to TN. Prioritize Synnex. Setting status to \'Synnex\'.',FALSE)->save();
	                        continue;
	                	}
                	}
                    //set order to ship internal
                    $tagToOrderResource->addIntoDB($order->getId(), self::TAG_WAREHOUSE_ID);
                    $order->setState('processing','processmanually','Shipping to TN, CA, or MA. May need to reship from UT. Setting status to \'Process Manually\'.', FALSE)->save();
                    continue;
                }
                
                 //Sort warehouses by cost
                 asort($warehousesFulfill);
                 $warehouseKeys = array_keys($warehousesFulfill);
                 $warehouseCount = count($warehouseKeys);
                 
                 //If Multiple warehouses, check if cheapest is greater than threshhold
                 if ($warehouseCount > 1) {
                 	$difference = $warehousesFulfill[$warehouseKeys[$warehouseCount - 1]] - $warehousesFulfill[$warehouseKeys[0]];
	                 if ($difference >= 10) 							{
		                 $done = 1;
		                 $tagToOrderResource->addIntoDB($order->getId(), self::TAG_WAREHOUSE_ID);
		                 $order->setState('processing',$warehouseKeys[0],'Order available $' . $difference . ' cheaper at ' . $warehouseKeys[0] . ' vs ' . $warehouseKeys[$warehouseCount - 1] . '. Setting status to \'' . ucfirst($warehouseKeys[0]) . '\'.', FALSE)->save();
		                 $warehouse_model->getWarehouseModel($warehouseKeys[0])->fulfill($order , $order->getAllItems());
		                 continue;
	                 }
                
                 }
                 
                 //If cheapest is not under threshhold, process in order of priority
                 foreach($warehouse_model->warehouses as $warehouse_name) {
                 	if (array_key_exists($warehouse_name, $warehousesFulfill)) {
                 		$warehouse_model->getWarehouseModel($warehouse_name)->fulfill($order , $order->getAllItems());
                        //set order to complete here
                        $tagToOrderResource->addIntoDB($order->getId(), self::TAG_WAREHOUSE_ID);
                        $order->setState('processing',$warehouse_name,'Order available to fulfill at ' . $warehouse_name . '. Setting status to \'' . ucfirst($warehouse_name) . '\'.')->save();
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
                        
                            // $multi_fulfillment[$warehouse_name][$id] = new ien_Object(array(
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
					$tagToOrderResource->addIntoDB($order->getId(), self::TAG_WAREHOUSE_ID);
					$order->setState('processing','processmanually','No single warehouse has stock to fulfill entire order. Setting status to \'Process Manually\'.', FALSE)->save();
				}
            
            }
        }

        return $this;
    }

    protected function _getStopStates() {
        
        //refactor add state codes to constant or better yet system>config
        
        $stop_states = array();
        $model = Mage::getModel('directory/region');
        $stop_states['CA'] = $model->loadByCode('CA', 'US')->getId();
        $stop_states['MA'] = $model->loadByCode('MA', 'US')->getId();
        $stop_states['TN'] = $model->loadByCode('TN', 'US')->getId();
        
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


	public function updateByProduct($product) {
		$time = time();
			$to = date('Y-m-d H:i:s', $time);
			$lastTime = $time - (24*60*60); // 60*60*24
			$from = date('Y-m-d H:i:s', $lastTime);
			
		$collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToSelect('peachtree_updated','left')
	        ->addattributeToFilter('peachtree_updated',array(array('lt' => $from),array('null' => true)))
            ->addAttributeToFilter('sku',$product->getSku())
            ->setPageSize(1);
             $collection->getSelect()
				->joininner(
					array('peach' => 'ocm_peachtree'), 'e.sku=peach.sku', array('peachtree_qty' => 'qty','peachtree_cost' => 'cost')
				);
				
			if (count($collection) == 1)   
            Mage::getModel('ocm_fulfillment/warehouse_peachtree')->updatePriceQty($collection);
            
       $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToSelect('warehouse_updated_at','left')
	        ->addattributeToFilter('warehouse_updated_at',array(array('lt' => $from),array('null' => true)))
            ->addAttributeToFilter('sku',$product->getSku())
            ->setPageSize(1);
       if (count($collection) == 1)       
		$this->updateProductWarehouseData(null,$collection);
	}
	
    protected function _selectCountPage() {
        $catalog_size = Mage::getModel('catalog/product')->getCollection()->getSize();
        $page_size = ceil($catalog_size / 14);
        
        $day = date('N');
        $meridiem = (date('a') == 'am') ? 0 : 7;
        
        $current_page = $day + $meridium;
        
        return array($page_size,$current_page);
    }

    public function updateProductWarehouseData($observer = null, $collection = null) {
    	
    	if (!$collection) {
	    	$time = time();
			$to = date('Y-m-d H:i:s', $time);
			$lastTime = $time - (self::FULFILLMENT_UPDATE_DELAY*60*60); // 60*60*24
			$from = date('Y-m-d H:i:s', $lastTime);
	
			$target = time() - (60 * 60 * 23);
			
			$collection = Mage::getModel('catalog/product')->getCollection()
				->addAttributeToSelect('warehouse_updated_at','left')
	            ->addattributeToFilter('warehouse_updated_at',array(array('lt' => $from),array('null' => true)))
	            ->addAttributeToSelect('*')
	            ->setOrder('warehouse_updated_at','ASC')
	            ->setPageSize(self::FULFILLMENT_PAGE_SIZE);
    	}
   
        $helper = Mage::helper('ocm_fulfillment'); 
        

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
			//continue;
           }
           $product->setData('warehouse_updated_at',now());
           
           $price_array = array();
           $qty = 0;
           
           // Ingram MUST be the end of the array for this to work
           foreach (array('techdata','synnex','ingram') as $warehouse_name) {
           
               if(is_array(${$warehouse_name.'_products'}[ $product->getData(${$warehouse_name.'_sku_attr'}) ])) {
               		if (is_numeric(${$warehouse_name.'_products'}[ $product->getData(${$warehouse_name.'_sku_attr'}) ]['price']) ||is_numeric(${$warehouse_name.'_products'}[ $product->getData(${$warehouse_name.'_sku_attr'}) ]['qty'])) {
	                   $product->setData($warehouse_name.'_price',${$warehouse_name.'_products'}[ $product->getData(${$warehouse_name.'_sku_attr'}) ]['price']);
	                   $product->setData($warehouse_name.'_qty',${$warehouse_name.'_products'}[ $product->getData(${$warehouse_name.'_sku_attr'}) ]['qty']);
	    
	                   if($product->getData($warehouse_name.'_qty') > 0) {
	                       $price_array[] = $product->getData($warehouse_name.'_price');
	                       $qty += $product->getData($warehouse_name.'_qty');
	                   }
	                   //echo ${$warehouse_name.'_products'}[ $product->getData(${$warehouse_name.'_sku_attr'}) ]['price'];
	                   Mage::log('Updated ' . $product->getSku() ." " . $warehouse_name,null,'fulfillment.log');
                   } else {
	                   $sku = $product->getData(${$warehouse_name.'_sku_attr'});
					   if (isset($sku)) {
		           		$product->setData('warehouse_errors','No Warehouse Match for SKU ' . $sku . " -> " . $warehouse_name);
				   		Mage::log('No Warehouse Match for SKU ' . $product->getSku() . " -> " . $sku . " -> " . $warehouse_name,null,'fulfillment.log');
		           	}
                   }
               } else {
               	$product->setData($warehouse_name.'_qty',null);
               	$product->setData($warehouse_name.'_price',null);
               	$sku = $product->getData(${$warehouse_name.'_sku_attr'});
	           	if (isset($sku)) {
	           		$product->setData('warehouse_errors','No Warehouse Match for SKU ' . $sku . " -> " . $warehouse_name);
			   		Mage::log('No Warehouse Match for SKU ' . $product->getSku() . " -> " . $sku . " -> " . $warehouse_name,null,'fulfillment.log');
	           	}
	           	//echo $sku . " SKU"; 
               }
               
           }
           
           $helper->updateStock($product);
           
           
       }
       
       
    }


}
