<?php

require "../app/Mage.php";
Mage::app('admin')->setUseSessionInUrl(false);

$order = Mage::getModel('sales/order')->load(4940);

$order->addStatusToHistory('ordered_license_1','Order has Licensing items. Setting status to License Ordered.')->save();