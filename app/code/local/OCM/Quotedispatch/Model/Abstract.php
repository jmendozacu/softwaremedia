<?php

class OCM_Quotedispatch_Model_Abstract extends Mage_Core_Model_Abstract
{
    
    public function loadByMultiple($filters) {
        $collection = $this->getCollection();
    
        foreach ($filters as $column => $value) {
            $collection->addFieldToFilter($column, $value);
        }
        $item = $collection->getFirstItem();
        if($item->getId()) {
            $this->load($item->getId());
        } else {
            $this->setData(array());
        }
        return $this;
    }
    
}