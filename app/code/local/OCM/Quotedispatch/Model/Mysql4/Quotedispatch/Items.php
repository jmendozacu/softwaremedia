<?php

class OCM_Quotedispatch_Model_Mysql4_Quotedispatch_Items extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('quotedispatch/ocm_quotedispatch_items', 'quotedispatch_item_id');
    }
    
}