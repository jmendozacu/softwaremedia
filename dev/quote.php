<?php

require "../app/Mage.php";
Mage::app('admin')->setUseSessionInUrl(false);


		
$historyEmail = Mage::getModel('emailhistory/email');
			$historyEmail->setOrderId(198);
			$historyEmail->setText('asdasd');
			$historyEmail->setEmail('sadas');
			$historyEmail->save();