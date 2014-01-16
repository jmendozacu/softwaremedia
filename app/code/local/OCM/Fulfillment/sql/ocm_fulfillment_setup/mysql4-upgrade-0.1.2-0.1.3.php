<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$attrCode = 'ingram_price';
$attrGroupName = 'Prices';
$attrLabel = 'Ingram Price';
$attrNote = '';


$attr_array = array();
$attr_array[] = array(
    'code'  => 'ingram_price',
    'group' => 'Prices',
    'label' => 'Ingram Price',
    'note'  => ''
);

$attr_array[] = array(
    'code'  => 'techdata_price',
    'group' => 'Prices',
    'label' => 'Techdata Price',
    'note'  => ''
);

$attr_array[] = array(
    'code'  => 'synnex_price',
    'group' => 'Prices',
    'label' => 'Synnex Price',
    'note'  => ''
);

$objCatalogEavSetup = Mage::getResourceModel('catalog/eav_mysql4_setup', 'core_setup');

foreach ($attr_array as $attr) {
    $attrIdTest = $objCatalogEavSetup->getAttributeId(Mage_Catalog_Model_Product::ENTITY, $attr['code']);
    
    if ($attrIdTest === false) {
        $objCatalogEavSetup->addAttribute(Mage_Catalog_Model_Product::ENTITY, $attr['code'], array(
            'group' => $attr['group'],
            'sort_order' => 7,
            'type' => 'varchar',
            'backend' => '',
            'frontend' => '',
            'label' => $attr['label'],
            'note' => $attr['note'],
            'input' => 'price',
            'class' => '',
            'source' => '',
            'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
            'visible' => true,
            'required' => false,
            'user_defined' => true,
            'default' => '0',
            'visible_on_front' => false,
            'unique' => false,
            'is_configurable' => false,
            'used_for_promo_rules' => true
        ));
    }
}

$installer->endSetup();