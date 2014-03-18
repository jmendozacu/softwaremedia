<?php

require "../app/Mage.php";
Mage::app('admin')->setUseSessionInUrl(false);


		
$quote = Mage::getModel('qquoteadv/qqadvcustomer');
$quote->sendReminderEmail(true);
$quote->send2ndReminderEmail(true);
$quote->send3rdReminderEmail(true);