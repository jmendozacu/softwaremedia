<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
adsa;
$installer->startSetup();

$installer->run("
ALTER TABLE `emailhistory`
ADD COLUMN `is_read` TINYINT(1) NULL DEFAULT '0' AFTER `email_name`;
");

$installer->endSetup();
