<?php

class OCM_Quotedispatch_Model_Adminuser
{

    static public function getOptionArray()
    {
        $return_array = array();
        $collection = Mage::getResourceModel('admin/user_collection');
        
        foreach ($collection as $user) {
            $return_array[$user->getUsername()] = $user->getUsername();
        }
        
        return $return_array;
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