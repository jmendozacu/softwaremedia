<?php

require "/var/www/magento/htdocs/app/Mage.php";
Mage::app('admin')->setUseSessionInUrl(false);

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);
Mage::log('sitemap started',null,'system.log');

Mage::getModel('sitemap/observer')->scheduledGenerateSitemaps();
