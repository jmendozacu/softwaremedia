<?php

require "../app/Mage.php";
Mage::app('admin')->setUseSessionInUrl(false);
//$file = fopen(Mage::getBaseDir()."/var/synnex_data/520985.ap","r") or die('could not open file');
//OCM_Fulfillment_Model_Warehouse_Ingram
//Mage::getModel('ocm_fulfillment/observer')->updateProductWarehouseData();

$oOrder = Mage::getModel('sales/order')->load(8367);

$sComment = "We’re sorry. Because we were unable to validate your payment information, our system detected your order as possible fraud.";
					
				$oOrder->sendOrderUpdateEmail(true,$sComment);                