<?php

require "../app/Mage.php";
Mage::app('admin')->setUseSessionInUrl(false);
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

//echo "test";

$user = Mage::getModel('admin/user')->load(22);
echo $user->_getDecryptedPassword($user->getOfficePassword());