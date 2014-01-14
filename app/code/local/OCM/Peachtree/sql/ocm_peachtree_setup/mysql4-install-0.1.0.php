<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("
 CREATE TABLE IF NOT EXISTS {$this->getTable('ocm_peachtree_referer')} (
  `id` int(11) NOT NULL auto_increment,
  `order_id` int(10) NOT NULL,
  `referer_id` varchar(20) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `IDX_OCM_PEACHTREE_ORDER_ID` (`order_id`),
  CONSTRAINT `FK_OCM_PEACHTREE_ORDER_ID_SALES_FLAT_ORDER_ID` FOREIGN KEY (`order_id`) REFERENCES `sales_flat_order` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();