<?php

require "../app/Mage.php";
Mage::app('admin')->setUseSessionInUrl(false);


		
$historyEmail = Mage::getModel('emailhistory/email')->getCollection()
->addFieldToFilter('order_id',198);

foreach($historyEmail as $email) {
	echo $email->getEmail();
}