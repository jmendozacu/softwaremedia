<?php
$installer = $this;
$installer->startSetup();
$installer->addAttribute('catalog_product', 'location', array(
    'group' => 'General',
    'type' => 'text',
    'backend' => '',
    'frontend' => '',
    'label' => 'Location',
    'input' => 'text',
    'class' => '',
    'source' => '',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'visible' => true,
    'required' => false,
    'user_defined' => true,
    'default' => '0',
    'wysiwyg_enabled' => false,
    'searchable' => false,
    'filterable' => false,
    'comparable' => false,
    'visible_on_front' => false,
    'unique' => false,
    'apply_to' => 'simple,configurable,virtual,bundle,downloadable',
    'is_configurable' => false
));
$dir = Mage::getBaseDir()."/app/code/local/OCM/Setup/data/ocm_setup_setup/location.csv";

if (($handle = fopen($dir, "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 10000, ";")) != FALSE) {
        $product = Mage::getModel('catalog/product')->getCollection()->addFieldToFilter('sku',$data[0])->getFirstItem();
        if($product->getId()){
            $product->setLocation($data[1])->save();
        }
    }
    fclose($handle);
}

$installer->endSetup();