<?php

require "../app/Mage.php";

$modules = Mage::getConfig()->getNode('modules')->children();

foreach($modules as $module) {
	echo $module . "<br />";
}