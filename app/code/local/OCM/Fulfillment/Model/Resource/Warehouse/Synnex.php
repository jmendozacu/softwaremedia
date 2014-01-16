<?php
class OCM_Fulfillment_Model_Resource_Warehouse_Synnex extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('ocm_fulfillment/warehouse_synnex', 'id');
    }
}
