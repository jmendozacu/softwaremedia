<?php

class AW_Advancedreports_Model_Custom_Date_Range
{
    public function toOptionArray()
    {
        return Mage::helper('advancedreports')->getOptions();
    }
}