<?php
class OCM_Fulfillment_Model_License extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('ocm_fulfillment/license');
    }
}