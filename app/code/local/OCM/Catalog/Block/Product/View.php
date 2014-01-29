<?php
class OCM_Catalog_Block_Product_View extends Mage_Catalog_Block_Product_View {

    public function getPriceHtml($product, $displayMinimalPrice = false, $idSuffix = '')
    {
        $this->setTemplate('catalog/product/price.phtml');
        $this->setProduct($product);
        return $this->toHtml();
    }

    public function getAttributeOptionImage($attributeCode,$optionId)
    {
        $_product = Mage::getModel('catalog/product');
        $_attributes = Mage::getResourceModel('eav/entity_attribute_collection')
            ->setEntityTypeFilter($_product->getResource()->getTypeId())
            ->addFieldToFilter('attribute_code', $attributeCode);
        $_attribute = $_attributes->getFirstItem()->setEntity($_product->getResource());
        $options = $_attribute->getSource()->getAllOptions(false);
        $return = array();
        $shopbyFilter = Mage::getModel('amshopby/filter')->getCollection()->addFieldToFilter('attribute_id',$_attribute->getId())->getFirstItem();
        foreach($options as $option) {
            if($option['value'] == $optionId){
                $image = Mage::getModel('amshopby/value')->getCollection()
                    ->addFieldToFilter('option_id',$option['value'])
                    ->addFieldToFilter('filter_id',$shopbyFilter->getId())
                    ->getFirstItem();
                if($image->getImgSmall()){
                    $return['image'] = $image->getImgSmall();
                } else {
                    $return['image'] = $attributeCode.$optionId.'.png';
                }
                $return['label'] = $option['label'];

                break;
            }

        }
        return $return;
    }
    public function getRelated($brand) {
        $related_categories = Mage::getModel('catalog/category')->getCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('url_path')
            ->addAttributeToFilter(array(
                array('attribute' => 'parent_id','eq' => '66'), 
                array('attribute' => 'parent_id', 'eq' => '110')))
            ->addAttributeToFilter('name', $brand)
            ->load();

        $related_categories = Mage::getModel('advancedstaticleftnav/lnobject')->getCollection()
            ->addFieldToSelect('related_category_id')
            ->addFieldToFilter('category_id', $related_categories->getFirstItem()->getId())
            ->addFieldToFilter('tree_id', '2');

        $related = $related_categories->toArray(); 
        $category_ids = array();
        foreach($related['items'] as $category_array) {
            $category_ids[] = $category_array['related_category_id'];
        }

        $category_ids = implode(',', $category_ids);

        $related_categories = Mage::getModel('catalog/category')->getCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('meta_title')
            ->addAttributeToSelect('url_path');
            
        $related_categories->getSelect()->where("find_in_set(`main_table`.`entity_id`, '$category_ids')");
        return $related_categories;
    }
}
