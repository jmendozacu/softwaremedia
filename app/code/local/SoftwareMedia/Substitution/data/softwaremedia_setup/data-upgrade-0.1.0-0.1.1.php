<?php
$installer = $this;
$installer->startSetup();
 
$installer->run("
	INSERT INTO catalog_product_link_attribute (link_type_id,product_link_attribute_code,data_type) VALUES('6','price_sync','int');
	UPDATE catalog_product_link_attribute SET data_type='int' WHERE product_link_attribute_id='9';
");

  
$installer->endSetup();

