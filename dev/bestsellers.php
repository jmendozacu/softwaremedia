<?php

require "../app/Mage.php";
Mage::app('admin')->setUseSessionInUrl(false);

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

$helper = new Mage_Sales_Model_Resource_Report_Bestsellers();

$helper->aggregate();
