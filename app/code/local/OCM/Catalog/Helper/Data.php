<?php
class OCM_Catalog_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getCategoryUrl($name)
    {
        $category = Mage::getModel('catalog/category')->getCollection()
                    ->addFieldToFilter('name',$name)
                    ->addFieldToFilter('is_active',1)
                    ->getFirstItem();
        if($category->getId()){
            return $category;
        } else {
            return false;
        }
    }
}