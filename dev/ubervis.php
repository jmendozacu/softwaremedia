<?php

require "../app/Mage.php";
Mage::app('admin')->setUseSessionInUrl(false);
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

//echo "test";

echo date('N');
echo "<br />";
echo Mage::helper('ocm_fulfillment')->estimateShipDate('productmatrix_Free_Budget_(5-9_Days)');
echo "<br />";
echo Mage::helper('ocm_fulfillment')->estimateDelivery('productmatrix_Free_Budget_(5-9_Days)');