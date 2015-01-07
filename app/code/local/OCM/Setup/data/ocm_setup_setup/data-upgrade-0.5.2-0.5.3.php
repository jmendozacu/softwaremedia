<?php
 
  $installer = $this;
 
  $installer->startSetup(); 
  
$installer->run("
ALTER TABLE `enterprise_rma_status_history` 
ADD COLUMN `admin` VARCHAR(255) NULL DEFAULT NULL AFTER `is_admin`;
  ");

  $installer->endSetup();
  
  ?>