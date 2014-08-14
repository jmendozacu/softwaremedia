<?php
 
  $installer = $this;
 
  $installer->startSetup(); 
  
$installer->run("
 ALTER TABLE `enterprise_banner` 
ADD COLUMN `from_date` DATE NULL DEFAULT NULL AFTER `types`,
ADD COLUMN `to_date` DATE NULL DEFAULT NULL AFTER `from_date`;

  ");

  $installer->endSetup();
  
  ?>