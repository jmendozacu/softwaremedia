<?php

class OCM_Peachtree_Model_Mysql4_Referer_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('peachtree/referer');
    }
}