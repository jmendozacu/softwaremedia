<?php
class OCM_Catalog_Block_Product_Bestsellers extends Mage_Catalog_Block_Product_Abstract{
    public function __construct()
    {
        parent::__construct();

        $storeId    = Mage::app()->getStore()->getId();
        
        $products = Mage::getResourceModel('reports/product_collection')
            ->addOrderedQty()
            ->addAttributeToSelect(array('name', 'price', 'small_image', 'short_description', 'description'))
            ->setStoreId($storeId)
            ->addStoreFilter($storeId)
            ->setOrder('ordered_qty', 'desc');

        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($products);
        $this->setProductCollection($products);
    }
}