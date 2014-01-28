<?php

require "app/Mage.php";
Mage::app('admin')->setUseSessionInUrl(false);

//$file = fopen(Mage::getBaseDir()."/var/synnex_data/520985.ap","r") or die('could not open file');

//OCM_Fulfillment_Model_Warehouse_Ingram
//Mage::getModel('ocm_fulfillment/observer')->updateProductWarehouseData();

$time = time();
$lastTime = $time - (3*60*60); // 60*60*24
$from = date('Y-m-d H:i:s', $lastTime);
		
$collection = Mage::getModel('catalog/product')->getCollection()
->addAttributeToSelect('warehouse_updated_at','left')
->addattributeToFilter('warehouse_updated_at',array(array('lt' => $from),array('null' => true),array('eq' => '')));


//$ingram = Mage::getModel('ocm_fulfillment/warehouse_ingram')->loadCollectionArray($collection);
//updatePriceQtyFromCsv            
Mage::getModel('ocm_fulfillment/observer')->updateProductWarehouseData();
//Mage::getModel('ocm_fulfillment/warehouse_peachtree')->updatePriceQtyFromCsv();
