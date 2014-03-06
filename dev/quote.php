<?php

require "../app/Mage.php";
Mage::app('admin')->setUseSessionInUrl(false);


		
$quote = Mage::getModel('qquoteadv/qqadvcustomer');
$quote->send3rdReminderEmail();
