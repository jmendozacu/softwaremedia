<?php

require "../app/Mage.php";
Mage::app('admin')->setUseSessionInUrl(false);
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

//echo "test";


Mage::getModel('ubervisibility/observer')->updateProduct();
