<?php
 
  $installer = $this;
 
  $installer->startSetup(); 
  
    $installer->run("
  UPDATE `eav_attribute` SET `source_model` = 'eav/entity_attribute_source_table' WHERE `eav_attribute`.`attribute_code`='feed_category';
  ");
  
  $installer->endSetup();
  
  ?>