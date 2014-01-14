<?php

$installer = $this;

$installer->startSetup();

$installer->run("
	    INSERT INTO  `{$this->getTable('sales/order_status')}` (`status` ,`label`)
	    VALUES ('needslicense','Needs License'),('processmanually','Process Manually'),('multipleproductorder','Multiple Product Order');
	    INSERT INTO  `{$this->getTable('sales/order_status_state')}` (`status`,`state`,`is_default`)
	    VALUES ('needslicense',  'processing',  '0'),('processmanually',  'processing',  '0'),('multipleproductorder',  'processing',  '0');
");

$installer->endSetup();
