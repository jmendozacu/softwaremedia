<?php
$installer = $this;
/* @var $installer Mage_Customer_Model_Entity_Setup */

$installer->startSetup();

$installer->addAttribute('customer', 'cod', array(
    'type'              => 'int',
    'backend'           => '',
    'frontend'          => '',
    'label'             => Mage::helper('emjainteractive_purchaseordermanagement')->__('COD Approved'),
    'input'             => 'select',
    'class'             => '',
    'source'            => 'eav/entity_attribute_source_boolean',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'           => false,
    'required'          => false,
    'user_defined'      => false,
    'default'           => '',
    'searchable'        => false,
    'filterable'        => false,
    'adminhtml_only'    => true,
    'visible_on_front'  => false,
    'unique'            => false,
    'sort_order'        => '230',
    

));

$attributeId = $this->getAttribute('customer', 'cod', 'attribute_id');
if ($attributeId) {
    $installer->run("
        INSERT IGNORE INTO {$this->getTable('customer/form_attribute')} VALUES ('adminhtml_customer', {$attributeId});
    ");
}

$installer->endSetup();


$installer->endSetup();