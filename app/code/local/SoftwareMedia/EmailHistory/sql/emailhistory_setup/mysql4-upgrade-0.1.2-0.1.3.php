<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
adsa;
$installer->startSetup();

$installer->run("
ALTER TABLE `mage`.`emailhistory` 
ADD COLUMN `name` VARCHAR(255) NULL;
");

$installer->endSetup();