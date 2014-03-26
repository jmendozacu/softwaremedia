<?php

$installer = $this;
$installer->startSetup();

// Adding follow up columns
$this->run("
    ALTER TABLE `{$this->getTable('quoteadv_customer')}` ADD `reminder_sent` TINYINT(1) NULL DEFAULT '0'; 
    ALTER TABLE `{$this->getTable('quoteadv_customer')}` ADD `reminder_sent_2` TINYINT(1) NULL DEFAULT '0' AFTER `reminder_sent`; 
    ALTER TABLE `{$this->getTable('quoteadv_customer')}` ADD `reminder_sent_3` TINYINT(1) NULL DEFAULT '0' AFTER `reminder_sent_2`;
");    
    

$installer->endSetup();
