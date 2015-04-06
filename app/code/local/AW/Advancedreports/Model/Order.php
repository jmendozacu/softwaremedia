<?php

class AW_Advancedreports_Model_Order extends Mage_Sales_Model_Order
{
    protected function _construct()
    {
        $this->_init('advancedreports/order');
    }
}