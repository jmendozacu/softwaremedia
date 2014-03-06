<?php

$installer = $this;
$installer->startSetup();

// Adding follow up columns
$this->run("
    ALTER TABLE `{$this->getTable('quoteadv_customer')}` ADD `notify_admin` tinyint(1) AFTER `no_reminder_3`;
");    
    

$installer->endSetup();
