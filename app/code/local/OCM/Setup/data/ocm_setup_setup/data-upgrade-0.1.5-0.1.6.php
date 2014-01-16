<?php

$installer = $this;
$installer->startSetup();

// Add attribute brand 
$installer->addAttribute('catalog_product', 'brand', array(
		'group'             => 'General',
		'type'              => 'int',
		'backend'           => '',
		'frontend'          => '',
		'label'             => 'Brand',
		'input'             => 'select',
		'class'             => '',
		'source'            => '',
		'is_global', Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
		'visible'           => true,
		'required'          => false,
		'user_defined'      => false,
		'default'           => '0',
		'searchable'        => false,
		'filterable'        => false,
		'comparable'        => false,
		'visible_on_front'  => false,
		'unique'            => false,
		'apply_to'          => 'simple,configurable,virtual,bundle,downloadable',
		'is_configurable'   => false,
		'used_in_product_listing', '1'
));

$arg_attribute = 'brand';
$attr_model = Mage::getModel('catalog/resource_eav_attribute');
$attr = $attr_model->loadByCode('catalog_product', $arg_attribute);
$attr_id = $attr->getAttributeId();

$manufacturers = array('Adobe','AEC','Autodesk','Apple','BitDefender','Bussiness Objects','Corel','Intui','Kaspersky','Microsoft','Norton','Nuance','Oracle','Quickbooks','Quicken','Sybase','Symantec','TrendMicro','VMware');
foreach ($manufacturers as $manufacturer){
	$option = array();
	$option['attribute_id'] = $attr_id;
	$option['value']['value'][0] = $manufacturer;
	$installer->addAttributeOption($option);
}
$installer->endSetup();