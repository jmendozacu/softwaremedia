<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("
-- DROP TABLE IF EXISTS `{$this->getTable('autocompleteplus_autosuggest/notifications')}`;
CREATE TABLE IF NOT EXISTS `{$installer->getTable('autocompleteplus_autosuggest/notifications')}` (
	`notification_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `type` varchar(32) default NULL,
    `subject` varchar(255) default NULL,
	`message` text,
    `timestamp` varchar(32) default NULL,
    `is_active` tinyint(1) NOT NULL default '1',
	PRIMARY KEY (`notification_id`),
	KEY `IDX_TYPE` (`type`),
	KEY `IDX_IS_ACTIVE` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();