<?php

require "../app/Mage.php";
Mage::app('admin')->setUseSessionInUrl(false);
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

//echo "test";

$time = time();

$fedEx = Mage::getModel('ocm_fulfillment/fedex');
$fedEx->addRecipient('UT','84095','US');

echo var_dump($fedEx->getEstimate()); 
echo "<br />";
echo Mage::helper('ocm_fulfillment')->estimateDelivery('productmatrix_Free_Budget'); 
//echo time() - $time;