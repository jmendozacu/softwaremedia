<?php
class OCM_Mods_Helper_Data extends Mage_Core_Helper_Abstract
{
    function getCategoryThumbnail($brand_name) {
        //TODO: move to config
        $brands_id = 66;
        $more_brands_id = 110;
        $cat_collection = Mage::getModel('catalog/category')->getCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('thumbnail')
            ->addAttributeToFilter('parent_id', $brands_id)
            ->addAttributeToFilter('name',$brand_name);
        $cat_data = $cat_collection->getFirstItem()->getData();
        if(is_null($cat_data['thumbnail'])) {
            $cat_collection = Mage::getModel('catalog/category')->getCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('thumbnail')
            ->addAttributeToFilter('parent_id', $more_brands_id)
            ->addAttributeToFilter('name',$brand_name);
            $cat_data = $cat_collection->getFirstItem()->getData();
        }
        if(!is_null($cat_data['thumbnail'])) { 
            $brand_img = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB).'media/catalog/category/'.$cat_data['thumbnail'];
            return "<img alt='BRAND_IMG_ALT' src='$brand_img'/>";
        } else {
            return "";
        }
    }

}
