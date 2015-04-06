<?php

class AW_Advancedreports_Model_Mysql4_Collection_Product_Item extends Mage_Sales_Model_Mysql4_Order_Item_Collection
{
    /**
     * Group collection by attribute
     *
     * @param $attribute
     *
     * @return AW_Advancedreports_Model_Mysql4_Collection_Product_Item
     */
    public function groupByAttribute($attribute)
    {
        $this->getSelect()->group($attribute);
        return $this;
    }
}