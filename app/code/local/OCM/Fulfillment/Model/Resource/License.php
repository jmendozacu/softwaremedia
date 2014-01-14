<?php
class OCM_Fulfillment_Model_Resource_License extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('ocm_fulfillment/license', 'id');
    }
}