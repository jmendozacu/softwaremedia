<?php

class AW_Advancedreports_Model_Aggregation extends Mage_Core_Model_Abstract
{
    const EXPIRED_TRUE = 1;
    const EXPIRED_FALSE = 0;
    const EXPIRES_AFTER = 5; //days count

    /**
     * Class constructor
     */
    protected function _construct()
    {
        $this->_init('advancedreports/aggregation');
    }
}