<?php

require "app/Mage.php";
Mage::app('admin')->setUseSessionInUrl(false);

//$file = fopen(Mage::getBaseDir()."/var/synnex_data/520985.ap","r") or die('could not open file');

Mage::getModel('ocm_fulfillment/observer')->updatePricesQty();