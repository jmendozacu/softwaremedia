<?php

class OCM_Quotedispatch_Model_Status //extends OCM_Quotedispatch_Model_Abstract
{

    public $available_statuses = array(1);
    static public $purchased_status = 2;
    public $expired_status = 3;
    
    static public function getOptionArray()
    {
        return array(
            0 => Mage::helper('quotedispatch')->__('Open / Request'),
            1 => Mage::helper('quotedispatch')->__('Available for Purchase'),
            2 => Mage::helper('quotedispatch')->__('Purchased'),
            3 => Mage::helper('quotedispatch')->__('Declined / Expired'),
        );
    }
    
    public function toOptionArray() {
        
        $options = array();
        foreach ($this->getOptionArray() as $value => $label ) {
            $options[] = array (
                'value' => $value,
                'label' => $label
            );
        }
        return $options;
    }
    

}