<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("
 UPDATE eav_attribute SET backend_type = 'datetime' WHERE attribute_code = 'peachtree_updated';
");

$installer->endSetup();