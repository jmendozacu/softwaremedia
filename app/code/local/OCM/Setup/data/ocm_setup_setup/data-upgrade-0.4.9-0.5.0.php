<?php
 
  $installer = $this;
 
  $installer->startSetup(); 
  
$installer->run("
  ALTER TABLE `mage`.`sales_flat_order` ADD COLUMN `purchase_order` VARCHAR(45) NULL DEFAULT NULL;
  ");

  $installer->endSetup();
  
  ?>