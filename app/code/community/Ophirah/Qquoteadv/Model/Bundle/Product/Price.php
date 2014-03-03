<?php
class Ophirah_Qquoteadv_Model_Bundle_Product_Price extends Mage_Bundle_Model_Product_Price
{
    /**
     * {@inheritDoc}
     */
    public function getFinalPrice($qty = null, $product)
    {        
        $customPrice = $product->getCustomPrice();
        return $customPrice !== null ? $customPrice : parent::getFinalPrice($qty, $product);
    }
}
