<?php

$installer = $this;
$installer->startSetup();

$entityTypeId = $installer->getEntityTypeId('catalog_product');

$arrAttribute = array('platform','product_type');

foreach($arrAttribute as $attribtute){
    $installer->removeAttribute('catalog_product',$attribtute);
}
$installer->addAttribute('catalog_product', 'platform', array(
    'type'              => 'int',
    'backend'           => '',
    'frontend'          => '',
    'label'             => 'Platform',
    'input'             => 'select',
    'class'             => '',
    'source'            => 'eav/entity_attribute_source_table',
    'is_global', Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'visible'           => true,
    'required'          => true,
    'option' => array('value' => array(
        'windows' => array('Windows'),
        'macintosh' => array('Macintosh')
    ,)
    ),
    'user_defined'      => true,
    'searchable'        => false,
    'filterable'        => false,
    'comparable'        => false,
    'visible_on_front'  => false,
    'apply_to'          => 'simple',
    'unique'            => true,
    'is_configurable'   => true
));

$installer->addAttribute('catalog_product', 'product_type', array(
    'type'              => 'int',
    'backend'           => '',
    'frontend'          => '',
    'label'             => 'Product Type',
    'input'             => 'select',
    'class'             => '',
    'source'            => 'eav/entity_attribute_source_table',
    'is_global', Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'visible'           => true,
    'required'          => true,
    'option' => array('value' => array(
        'full' => array('Full'),
        'upgrade' => array('Upgrade')
    ,)
    ),
    'user_defined'      => true,
    'searchable'        => false,
    'filterable'        => false,
    'comparable'        => false,
    'visible_on_front'  => false,
    'unique'            => true,
    'apply_to'          => 'simple',
    'is_configurable'   => true
));

$installer->addAttribute('catalog_product', 'delivery_option', array(
    'type'              => 'int',
    'backend'           => '',
    'frontend'          => '',
    'label'             => 'Delivery Option',
    'input'             => 'select',
    'class'             => '',
    'source'            => 'eav/entity_attribute_source_table',
    'is_global', Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'visible'           => true,
    'required'          => true,
    'option' => array('value' => array(
        'option1' => array('Option1'),
        'option2' => array('Option2')
    ,)
    ),
    'user_defined'      => true,
    'searchable'        => false,
    'filterable'        => false,
    'comparable'        => false,
    'visible_on_front'  => false,
    'unique'            => true,
    'apply_to'          => 'simple',
    'is_configurable'   => true
));

// Mage_Eav_Model_Entity_Setup
$installer = $this;
$installer->startSetup();


$installer->endSetup();