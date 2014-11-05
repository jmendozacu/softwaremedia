<?php

require "../app/Mage.php";

Mage::app('admin')->setUseSessionInUrl(false);
$user = Mage::getModel('admin/user')->load(12);
//echo $user->getId();
//echo $user->getOfficePassword();
$password = $user->_getDecryptedPassword($user->getOfficePassword());

echo $password;