<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$model=Mage::getModel('eav/entity_setup','core_setup');
$installer->startSetup();
$attributeId=$model->getAttribute('catalog_product','peachtree_updated');
$allAttributeSetIds=$model->getAllAttributeSetIds('catalog_product');
foreach ($allAttributeSetIds as $attributeSetId) {
try{
$attributeGroupId=$model->getAttributeGroup('catalog_product',$attributeSetId,'Warehouse info');
}
catch(Exception $e)
{
$attributeGroupId=$model->getDefaultAttributeGroupId('catalog/product',$attributeSetId);
}
$model->addAttributeToSet('catalog_product',$attributeSetId,$attributeGroupId,$attributeId);
}

$installer->endSetup();