<?php


class OCM_Price_Model_Catalog_Product extends TBT_RewardsOnly_Model_Catalog_Product
{

    public function getFinalPrice($qty=null)
    {
        Mage::dispatchEvent('ocm_cpcprice_catalog_product_get_final_price',array('product' => $this));
    
        $price = $this->_getData('final_price');
        if ($price !== null) {
            return $price;
        }
        return $this->getPriceModel()->getFinalPrice($qty, $this);
    }
    
}
