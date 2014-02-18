<?php
$installer = $this;
$installer->startSetup();
 
$installer->run("
	INSERT INTO catalog_product_link_attribute (link_type_id,product_link_attribute_code,data_type) VALUES('6','auto','int');
");

  
$installer->endSetup();

