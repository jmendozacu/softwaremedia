<?php

$installer = Mage::getResourceModel('catalog/setup', 'catalog_setup');

$installer->startSetup();

$installer->addAttribute(
	'catalog_product', 'ubervis_updated', array(
	'label' => 'Ubervisibility Updated',
	'input' => 'date',
	'type' => 'datetime',
	'source' => '',
	'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
	'visible' => 1,
	'required' => 0,
	'searchable' => 0,
	'filterable' => 1,
	'unique' => 0,
	'comparable' => 0,
	'visible_on_front' => 0,
	'is_html_allowed_on_front' => 0,
	'user_defined' => 1,
	'backend' => '',
	'frontend' => '',
	'class' => '',
	'default' => '',
	)
);

$installer->endSetup();
