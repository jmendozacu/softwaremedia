<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$model=Mage::getModel('eav/entity_setup','core_setup');
$installer->startSetup();
$attribute=$model->getAttribute('catalog_product','cost_override');
$attributeId = $attribute['attribute_id'];

$allAttributeSetIds=$model->getAllAttributeSetIds('catalog_product');

foreach ($allAttributeSetIds as $attributeSetId) {
try{
$attributeGroup=$model->getAttributeGroup('catalog_product',$attributeSetId,'Prices');
$attributeGroupId = $attributeGroup['attribute_group_id'];
}
catch(Exception $e)
{
$attributeGroup=$model->getDefaultAttributeGroupId('catalog/product',$attributeSetId);
$attributeGroupId = $attributeGroup['attribute_group_id'];
}
$model->addAttributeToSet('catalog_product',$attributeSetId,$attributeGroupId,$attributeId);
//echo "$model->addAttributeToSet('catalog_product',".$attributeSetId.",".$attributeGroupId.",".$attributeId.")";
}

$installer->endSetup();