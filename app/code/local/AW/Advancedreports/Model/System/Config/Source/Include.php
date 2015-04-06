<?php

class AW_Advancedreports_Model_System_Config_Source_Include
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => '0',
                'label' => Mage::helper('advancedreports')->__('Exclude'),
            ),
            array(
                'value' => '1',
                'label' => Mage::helper('advancedreports')->__('Include'),
            ),
        );
    }
}