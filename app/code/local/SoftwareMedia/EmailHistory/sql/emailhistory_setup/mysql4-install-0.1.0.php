<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
adsa;
$installer->startSetup();

$installer->run("
CREATE TABLE IF NOT EXISTS `emailhistory` (
  `id` int(11) NOT NULL auto_increment,
  `text` TEXT NULL,
  `order_id` INT(10) UNSIGNED NULL,
  `email` VARCHAR(255) NULL,
  PRIMARY KEY (`id`),
  INDEX `emailhistory_order_idx` (`order_id` ASC),
  CONSTRAINT `emailhistory_order`
    FOREIGN KEY (`order_id`)
    REFERENCES `mage`.`sales_flat_order` (`entity_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE=MyISAM DEFAULT CHARSET=utf8;

");

$installer->endSetup();