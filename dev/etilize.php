<?php

require "../app/Mage.php";
Mage::app('admin')->setUseSessionInUrl(false);

//$file = fopen(Mage::getBaseDir()."/var/synnex_data/520985.ap","r") or die('could not open file');

//OCM_Fulfillment_Model_Warehouse_Ingram
//Mage::getModel('ocm_fulfillment/observer')->updateProductWarehouseData();

//$ingram = Mage::getModel('ocm_fulfillment/warehouse_ingram')->getQty('AD-65158504AD01A00');
//updatePriceQtyFromCsv            
//Mage::getModel('ocm_fulfillment/observer')->updateProductWarehouseData();
Mage::getModel('etilize/etilize')->updateSpex();
