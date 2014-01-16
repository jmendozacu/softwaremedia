<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("
 CREATE TABLE IF NOT EXISTS `licensing_grid` (
  `id` int(11) NOT NULL auto_increment,
  `order_id` int(11) NOT NULL,
  `status` varchar(20) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
");

$installer->endSetup();