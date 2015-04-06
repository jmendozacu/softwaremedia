<?php

class AW_Advancedreports_Model_System_Config_Source_Datefilter
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'created_at',
                'label' => Mage::helper('advancedreports')->__('Created At'),
            ),
            array(
                'value' => 'updated_at',
                'label' => Mage::helper('advancedreports')->__('Updated At'),
            ),
        );
    }
}