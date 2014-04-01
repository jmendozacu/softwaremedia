<?php

require "../app/Mage.php";
Mage::app('admin')->setUseSessionInUrl(false);


		
Mage::getModel('qquoteadv/qqadvcustomer')->sendReminderEmail(true);
Mage::getModel('qquoteadv/qqadvcustomer')->send2ndReminderEmail(true);
Mage::getModel('qquoteadv/qqadvcustomer')->send3rdReminderEmail(true);
Mage::getModel('qquoteadv/qqadvcustomer')->sendExpireEmail();
