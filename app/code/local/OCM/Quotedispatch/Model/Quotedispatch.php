<?php

class OCM_Quotedispatch_Model_Quotedispatch extends OCM_Quotedispatch_Model_Abstract
{

    /*
    Status Values:
    0 => unavalable for purhase
    1 => available for purchase
    2 => purchased
    */

    public function _construct()
    {
        parent::_construct();
        $this->_init('quotedispatch/quotedispatch');
    }
    
    public function getAllItems() {
        
        if (!$this->hasData('all_items')) {
            
            $name_attr = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', 'name');
            
            //die(print_r(array_keys($name_attr->getData())));
            
            $collection = Mage::getModel('quotedispatch/quotedispatch_items')->getCollection()
                ->addFieldToFilter('quotedispatch_id',$this->getId())
                //->addFieldToFilter('email',$this->getEmail())
            ;
        
            $collection->getSelect()
                ->joinleft(
                    array('e' => 'catalog_product_entity'),
                    'main_table.product_id = e.entity_id'
                )
                ->joinleft(
                    array('pv' => 'catalog_product_entity_varchar'), 
                    'pv.entity_id=main_table.product_id', 
                    array('name' => 'value')
                )
                ->where('pv.attribute_id='.$name_attr->getAttributeId())
                ->columns(array(
                    'line_total' => new Zend_Db_Expr('main_table.price * main_table.qty')
                    )
                )
            ;
            
        
            //die($collection->getSelect());
        
            $this->setData('all_items', $collection);
        }
        return $this->getData('all_items');
        
    }
    
    public function loadByMultiple($filters) {
        $collection = $this->getCollection();
    
        foreach ($filters as $column => $value) {
            $collection->addFieldToFilter('main_table.'.$column, $value);
        }
        
        // TODO : make adding collection methods a param in abstract or better yet hook it to load()
        $collection->addQuoteSubtotal();
        
        $item = $collection->getFirstItem();

        if($item->getId()) {
            //$this->load($item->getId());
            $this->setData($item->getData());
        } else {
            $this->setData(array());
        }
        return $this;
    }

    public function getSubtotal()
    {
        if (!$this->hasData('subtotal')) {
            $subtotal = $this->_getResource()->getSubtotal($this);
            $this->setData('subtotal',$subtotal);
        }
        return $this->getData('subtotal');
    }


}