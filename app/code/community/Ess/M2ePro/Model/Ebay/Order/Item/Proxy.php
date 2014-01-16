<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Order_Item_Proxy extends Ess_M2ePro_Model_Order_Item_Proxy
{
    // ########################################

    public function getVariation()
    {
        return $this->item->getVariation();
    }

    public function getPrice()
    {
        return $this->item->getPrice();
    }

    public function getOriginalQty()
    {
        return $this->item->getQtyPurchased();
    }

    public function getTaxRate()
    {
        return $this->item->getEbayOrder()->getTaxRate();
    }

    public function getAdditionalData()
    {
        if (count($this->additionalData) == 0) {
            $this->additionalData[Ess_M2ePro_Helper_Data::CUSTOM_IDENTIFIER]['items'][] = array(
                'item_id' => $this->item->getItemId(),
                'transaction_id' => $this->item->getTransactionId()
            );
        }
        return $this->additionalData;
    }

    // ########################################
}