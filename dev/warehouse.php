<?php

require "../app/Mage.php";
Mage::app('admin')->setUseSessionInUrl(false);
//$file = fopen(Mage::getBaseDir()."/var/synnex_data/520985.ap","r") or die('could not open file');

//OCM_Fulfillment_Model_Warehouse_Ingram
//Mage::getModel('ocm_fulfillment/observer')->updateProductWarehouseData();

		
/*	
$product = Mage::getModel('catalog/product')->load(7169);
echo $product->setData('etilize_manufactureid','Test1');
	$product->save();
	
$collection =Mage::getResourceModel('catalog/product_collection')->addAttributeToFilter('sku', array('like'=>array('%KA-KL1843ACCFS%')))->addAttributeToSelect('*');
foreach($collection as $item) {
	//echo $item->setData('etilize_manufactureid','Test');
	//$item->save();
}
die();
$order = Mage::getModel('sales/order')->load(121);

		$result = Mage::helper('ocm_frauddetection')->isViolations($order);
        if($result){
            $order->setState('new','orders_suspect_hold',$result,false)->save();
        } 

die();

/*
$collection = Mage::getModel('catalog/product')->getCollection()
			->addAttributeToSelect('visibility')
            ->addAttributeToSelect('package_id')
            ->addAttributeToSelect('ingram_micro_usa')
            ->addAttributeToFilter('sku','SY-Z0TWWZF0EI1EE');
            */
      

//Mage::getModel('ocm_fulfillment/observer');
//updatePriceQtyFromCsv            


                 
//$model = Mage::getModel('ocm_fulfillment/warehouse_peachtree')->importCsv();       
//$model = Mage::getModel('ocm_fulfillment/warehouse_peachtree')->updatePriceQtyFrom();
/*
$collection = Mage::getModel('catalog/product')->getCollection()
			->addAttributeToFilter('sku','VM-WS9VP1099F')
            ->addAttributeToSelect('*');

            
Mage::getModel('ocm_fulfillment/observer')->updateProductWarehouse($collection);
//Mage::getModel('ocm_fulfillment/warehouse_peachtree')->updatePriceQty($collection);
  */          
$collection = Mage::getModel('catalog/product')->getCollection()
//			->addAttributeToSelect('warehouse_updated_at','left')
//            ->addattributeToFilter('warehouse_updated_at',array(array('lt' => $from),array('null' => true)))
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('sku','AD-65224750BA01A12');
/*          //->addattributeToFilter('ingram_micro_usa',array('notnull'=>true))
            //->addAttributeToSelect('cpc_price')
            //->addattributeToFilter('ingram_micro_usa',array('notnull'=>true))
            //->addAttributeToSelect('price')
            //->addAttributeToSelect('qty')
*/

Mage::getModel('ocm_fulfillment/observer')->updateProductWarehouse($collection);
//Mage::getModel('ocm_fulfillment/warehouse_peachtree')->updatePriceQtyFrom();
