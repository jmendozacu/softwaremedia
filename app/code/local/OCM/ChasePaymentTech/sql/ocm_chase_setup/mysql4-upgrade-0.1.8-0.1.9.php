<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$model = Mage::getModel('eav/entity_setup', 'core_setup');
$installer->startSetup();

$installer->run("
ALTER TABLE `mage`.`cm_chase_profiles` 
CHANGE COLUMN `last_4` `card_num` VARCHAR(4) NOT NULL ;
");

$installer->endSetup();
