<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("
	    INSERT INTO  `{$this->getTable('sales/order_status')}` (`status` ,`label`)
	    VALUES ('awaiting_licensing','Awaiting Licensing')
	    ,('awaiting_warehouse','Awaiting Warehouse')
	    ,('awaiting_dropship','Awaiting Dropship');
	    INSERT INTO  `{$this->getTable('sales/order_status_state')}` (`status`,`state`,`is_default`)
	    VALUES ('awaiting_licensing',  'new',  '0')
	    ,('awaiting_warehouse',  'new',  '0')
	    ,('awaiting_dropship',  'new',  '0');
");

$installer->endSetup();