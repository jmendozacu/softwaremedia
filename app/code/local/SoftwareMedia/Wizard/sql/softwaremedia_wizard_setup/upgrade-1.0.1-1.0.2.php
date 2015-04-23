<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
adsa;
$installer->startSetup();

$installer->run("
ALTER TABLE `mage`.`softwaremedia_wizard_question` 
ADD COLUMN `comment` VARCHAR(255) NULL AFTER `title`
");

$installer->endSetup();