<?php

$installer = $this;
$installer->startSetup();

// Adding follow up columns
$this->run("
    ALTER TABLE `{$this->getTable('quoteadv_customer')}` ADD `no_reminder_3` TINYINT(1) NULL DEFAULT '0' AFTER `no_reminder`; 
    ALTER TABLE `{$this->getTable('quoteadv_customer')}` ADD `no_reminder_2` TINYINT(1) NULL DEFAULT '0' AFTER `no_reminder`;
");    
    

$installer->endSetup();
