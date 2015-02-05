<?php
 
  $installer = $this;
 
  $installer->startSetup(); 
  
$installer->run("
ALTER TABLE `sales_flat_order_address` 
ADD COLUMN `residential` VARCHAR(45) NULL DEFAULT NULL AFTER `vat_request_success`;
");

  $installer->endSetup();
  
  ?>