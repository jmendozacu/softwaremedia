<?php
 
  $installer = $this;
 
  $installer->startSetup(); 
  
$installer->run("
ALTER TABLE `sales_flat_order` 
ADD COLUMN `delivery_estimate` DATETIME NULL DEFAULT NULL AFTER `pref_contact`;

");

  $installer->endSetup();
  
  ?>