<?php

class OCM_Quotedispatch_Model_Mysql4_Quotedispatch_Notes_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('quotedispatch/quotedispatch_notes');
    }

}