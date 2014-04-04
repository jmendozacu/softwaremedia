<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
adsa;
$installer->startSetup();

$installer->run("
ALTER TABLE `mage`.`emailhistory` 
CHANGE COLUMN `name` `email_name` VARCHAR(255) NULL DEFAULT NULL ;

");

$installer->endSetup();