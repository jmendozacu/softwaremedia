<?php
class OCM_Quotedispatch_Block_Create_List extends OCM_Quotedispatch_Block_Abstract
{

    public function getAllItems() {
        return Mage::getSingleton('checkout/session')->getQuote()->getAllItems();
    }

}