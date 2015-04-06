<?php

class AW_Advancedreports_Model_System_Config_Source_Skutype
{
    const SKUTYPE_GROUPED = 'grouped';
    const SKUTYPE_SIMPLE = 'simple';

    public function toOptionArray()
    {
        return array(
            array(
                'value' => self::SKUTYPE_SIMPLE,
                'label' => Mage::helper('advancedreports')->__('SKU of simple product'),
            ),
            array(
                'value' => self::SKUTYPE_GROUPED,
                'label' => Mage::helper('advancedreports')->__('SKU of grouped product'),
            ),
        );
    }
}
