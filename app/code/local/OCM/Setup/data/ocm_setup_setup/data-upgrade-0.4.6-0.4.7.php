<?php
 
  $installer = $this;
 
  $installer->startSetup();
 
  $installer->run("
  UPDATE `eav_attribute` SET `default_value` = '1' WHERE `eav_attribute`.`attribute_code`='status';  
  UPDATE `eav_attribute` SET `default_value` = '2' WHERE `eav_attribute`.`attribute_code`='tax_class_id';
  ");
 
  $installer->endSetup();
  
  ?>