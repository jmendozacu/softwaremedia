<?php
/**
 * Checkout Fields Manager
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitcheckoutfields
 * @version      10.5.7
 * @license:     grDwoQqpctpZdS57isl8WpY91kLDyrRZ7i5S4ZKTe1
 * @copyright:   Copyright (c) 2015 AITOC, Inc. (http://www.aitoc.com)
 */
/**
* @copyright  Copyright (c) 2009 AITOC, Inc. 
*/

$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS `aitoc_recurring_profile_entity_custom`;
CREATE TABLE IF NOT EXISTS `aitoc_recurring_profile_entity_custom` (
  `value_id` int(11) NOT NULL auto_increment,
  `attribute_id` smallint(5) unsigned NOT NULL default '0',
  `entity_id` int(10) unsigned NOT NULL default '0',
  `value` text NOT NULL,
  PRIMARY KEY  (`value_id`),
  UNIQUE KEY `UNQ_AITOC_ENTITY_ATTRIBUTE` (`entity_id`,`attribute_id`),
  KEY `FK_aitoc_recurring_profile_entity_custom_attribute` (`attribute_id`),
  KEY `FK_aitoc_recurring_profile_entity_custom` (`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `aitoc_recurring_profile_entity_custom`
  ADD CONSTRAINT `aitoc_recurring_profile_entity_custom_ibfk_1` FOREIGN KEY (`attribute_id`) REFERENCES `".$installer->getTable('eav_attribute')."` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `aitoc_recurring_profile_entity_custom_ibfk_2` FOREIGN KEY (`entity_id`) REFERENCES `".$installer->getTable('sales_recurring_profile')."` (`profile_id`) ON DELETE CASCADE ON UPDATE CASCADE;
");
    
$installer->endSetup();