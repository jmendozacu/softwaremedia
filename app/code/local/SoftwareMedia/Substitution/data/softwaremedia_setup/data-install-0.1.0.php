<?php
$installer = $this;
$installer->startSetup();
 
$installer->run("
	INSERT INTO catalog_product_link_type VALUES('6','substitution');
	INSERT INTO catalog_product_link_attribute (link_type_id,product_link_attribute_code,data_type) VALUES('6','qty','decimal');
");

  
$installer->endSetup();

