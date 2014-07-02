<?php
 
  $installer = $this;
 
  $installer->startSetup(); 
  
$installer->run("
  UPDATE catalog_eav_attribute SET is_visible = 1 WHERE attribute_id = 958;
UPDATE eav_attribute SET frontend_input='date', frontend_label='Created At' WHERE attribute_id='958';
  ");

  $installer->endSetup();
  
  ?>