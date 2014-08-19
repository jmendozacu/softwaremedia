<?php

require "../app/Mage.php";
Mage::app('admin')->setUseSessionInUrl(false);
$model = new Mage_Sales_Model_Observer();

$model->aggregateSalesReportOrderData(null);
