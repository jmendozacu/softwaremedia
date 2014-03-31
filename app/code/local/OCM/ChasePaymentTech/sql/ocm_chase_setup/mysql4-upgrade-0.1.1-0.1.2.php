<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$model = Mage::getModel('eav/entity_setup', 'core_setup');
$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('cm_chase_profiles')};

CREATE TABLE {$this->getTable('cm_chase_profiles')} (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `customer_id` INT UNSIGNED NOT NULL ,
  `card_type` VARCHAR(45) NULL ,
  `last_4` VARCHAR(4) NOT NULL ,
  `exp_month` VARCHAR(2) NOT NULL ,
  `exp_year` VARCHAR(4) NOT NULL ,
  `customer_reference_number` VARCHAR(22) NOT NULL ,
  `active` TINYINT(1) NOT NULL DEFAULT '1' ,
  PRIMARY KEY (`id`) );

");

$installer->endSetup();
