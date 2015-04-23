<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
adsa;
$installer->startSetup();

$installer->run("
ALTER TABLE `softwaremedia_wizard_question` 
CHANGE COLUMN `comment` `comment` VARCHAR(1000) NULL DEFAULT NULL ;
");

$installer->endSetup();