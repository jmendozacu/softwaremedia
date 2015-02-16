<?php

var_dump($_SERVER);

require "../app/Mage.php";
Mage::app('admin')->setUseSessionInUrl(false);


var_dump($_SERVER);