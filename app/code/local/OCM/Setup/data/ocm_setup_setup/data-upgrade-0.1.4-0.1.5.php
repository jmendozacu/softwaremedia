<?php

$installer = $this;
$installer->startSetup();

// Add version attribute
$data = array(
    'label' => 'Version',
    'type' => 'varchar',
    'input' => 'text',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'is_required' => '0', 
    'is_user_defined' => '1',
    'is_searchable' => '0', 
    'is_filterable' => '1', 
    'is_comparable' => '0',
    'is_visible_on_front' => '1',
    'is_visible_in_advanced_search' => '1',
    'is_unique' => '0',
    'is_configurable' => '1'
);
$this->addAttribute('catalog_product', 'version', $data);

// Add platform attribute
$data = array(
    'label' => 'Platform',
    'type' => 'varchar',
    'input' => 'select',
    'backend' => 'eav/entity_attribute_backend_array',
    'frontend' => '',
    'source' => '',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'is_required' => '0', 
    'is_user_defined' => '1',
    'is_searchable' => '1', 
    'is_filterable' => '1', 
    'is_comparable' => '0',
    'option' => array('value' => array(
            'windows' => array('Windows'),
            'macintosh' => array('Macintosh')
        ,)
    ),
    'is_visible_on_front' => '1',
    'is_visible_in_advanced_search' => '1',
    'is_unique' => '0',
    'is_configurable' => '0'
);
$this->addAttribute('catalog_product', 'platform', $data);

// Add product_type attribute
$data = array(
    'label' => 'Product Type',
    'type' => 'varchar',
    'input' => 'select',
    'backend' => 'eav/entity_attribute_backend_array',
    'frontend' => '',
    'source' => '',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'is_required' => '0', 
    'is_user_defined' => '1',
    'is_searchable' => '1', 
    'is_filterable' => '1', 
    'is_comparable' => '0',
    'option' => array('value' => array(
            'full' => array('Full'),
            'upgrade' => array('Upgrade')
        ,)
    ),
    'is_visible_on_front' => '1',
    'is_visible_in_advanced_search' => '1',
    'is_unique' => '0',
    'is_configurable' => '0'
);
$this->addAttribute('catalog_product', 'product_type', $data);

// add them to attribute group
$attributeSetId = $this->getAttributeSetId('catalog_product', 'Default');
$attributeGroupId = $this->getAttributeGroup('catalog_product', $attributeSetId, 'General');

$attributeId = $this->getAttribute('catalog_product', 'version');
$this->addAttributeToSet('catalog_product', $attributeSetId, $attributeGroupId, $attributeId);
$attributeId = $this->getAttribute('catalog_product', 'platform');
$this->addAttributeToSet('catalog_product', $attributeSetId, $attributeGroupId, $attributeId);
$attributeId = $this->getAttribute('catalog_product', 'product_type');
$this->addAttributeToSet('catalog_product', $attributeSetId, $attributeGroupId, $attributeId);

$installer->endSetup();