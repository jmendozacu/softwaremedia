<?php

require '../app/Mage.php';

Mage::app('admin')->setUseSessionInUrl(false);

Mage::getModel('enterprise_pagecache/crawler')->crawl();

echo 'crawled' . PHP_EOL;
