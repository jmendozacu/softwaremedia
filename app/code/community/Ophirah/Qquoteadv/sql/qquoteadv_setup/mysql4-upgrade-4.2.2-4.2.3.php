<?php

$installer = $this;
$installer->startSetup();

// Adding follow up columns
$this->run("
    ALTER TABLE `{$this->getTable('quoteadv_customer')}` ADD `reminder_3` date AFTER `reminder`; 
    ALTER TABLE `{$this->getTable('quoteadv_customer')}` ADD `reminder_2` date AFTER `reminder`;
");    
    

$installer->endSetup();
