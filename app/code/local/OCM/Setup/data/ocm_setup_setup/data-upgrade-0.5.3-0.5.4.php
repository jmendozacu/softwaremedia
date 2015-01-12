<?php
 
  $installer = $this;
 
  $installer->startSetup(); 
  
$installer->run("
ALTER TABLE `sales_flat_order_status_history` ADD COLUMN `priority` TINYINT NULL DEFAULT NULL AFTER `admin`;
");

  $installer->endSetup();
  
  ?>