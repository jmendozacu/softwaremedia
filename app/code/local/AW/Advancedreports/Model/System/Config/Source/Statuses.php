<?php

class AW_Advancedreports_Model_System_Config_Source_Statuses
{
    public function toOptionArray()
    {
        $arr = array();
        foreach (Mage::getSingleton('sales/order_config')->getStatuses() as $value => $label) {
            $arr[] = array('value' => $value, 'label' => $label);
        }
        return $arr;
    }
}