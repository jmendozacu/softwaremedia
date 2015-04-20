<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
adsa;
$installer->startSetup();

$installer->run("
ALTER TABLE `mage`.`softwaremedia_wizard_wizard` 
ADD COLUMN `static_block` VARCHAR(45) NULL DEFAULT NULL AFTER `created_at`;
");

$installer->endSetup();