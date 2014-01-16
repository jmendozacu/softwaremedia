<?php
/**
 * Add attribute manufacturer part number
 */


try {


	$installer = $this;	
	$installer->startSetup();
	
	$installer->addAttribute('catalog_product', 'manufacturer_pn', array(
        'group'             => 'General',
        'type'              => 'text',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Manufacturer Part Number',
        'input'             => 'text',
        'class'             => '',
        'source'            => '',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'visible'           => true,
        'required'          => false,
        'user_defined'      => true,
        'searchable'        => true,
        'filterable'        => false,
        'comparable'        => false,
        'visible_on_front'  => true,
        'unique'            => false,
        'apply_to'          => 'simple,configurable,virtual,bundle,downloadable',
        'is_configurable'   => false
	));

        $installer->endSetup();
}catch(Excpetion $e) {
	Mage::logException($e);
	Mage::log("ERROR IN SETUP ".$e->getMessage());
}
