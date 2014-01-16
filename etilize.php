<?php

require "app/Mage.php";
Mage::app('admin')->setUseSessionInUrl(false);
Mage::getModel('etilize/etilize')->updateSpex();