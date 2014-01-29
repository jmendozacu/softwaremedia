<?php

echo 'Start: ' . date('Y-m-d H:i:s') . PHP_EOL;

require '../app/Mage.php';

Mage::app('admin')->setUseSessionInUrl(false);

Mage::getModel('enterprise_pagecache/crawler')->crawl();

echo 'End: ' . date('Y-m-d H:i:s') . PHP_EOL;
