<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("
 CREATE TABLE IF NOT EXISTS {$this->getTable('ocm_peachtree')} (
  `sku` varchar(20) NOT NULL,
  `qty` int(10) NOT NULL,
  `cost` DECIMAL(12,4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();