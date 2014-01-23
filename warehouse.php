<?php

require "app/Mage.php";
Mage::app('admin')->setUseSessionInUrl(false);

Mage::getModel('ocm_fulfillment/observer')->updatePricesQty();