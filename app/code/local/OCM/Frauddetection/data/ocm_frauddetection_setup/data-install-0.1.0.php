<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("
	    INSERT INTO  `{$this->getTable('sales/order_status')}` (`status` ,`label`)
	    VALUES ('orders_suspect_hold','Orders Suspect Hold');
	    INSERT INTO  `{$this->getTable('sales/order_status_state')}` (`status`,`state`,`is_default`)
	    VALUES ('orders_suspect_hold',  'new',  '0');
");

$installer->endSetup();