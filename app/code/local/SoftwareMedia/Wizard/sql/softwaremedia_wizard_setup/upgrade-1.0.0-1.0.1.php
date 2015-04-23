<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
adsa;
$installer->startSetup();

$installer->run("
ALTER TABLE `mage`.`softwaremedia_wizard_question` 
CHANGE COLUMN `question` `question` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Question' ,
ADD COLUMN `title` VARCHAR(45) NULL AFTER `question`;
");

$installer->endSetup();