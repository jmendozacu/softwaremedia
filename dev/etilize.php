<?php
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);


require "../app/Mage.php";
Mage::app('admin')->setUseSessionInUrl(false);


 Mage::getModel('enterprise_reminder/rule')->sendReminderEmails();