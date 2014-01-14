<?php

class OCM_Quotedispatch_Model_Mysql4_Quotedispatch extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('quotedispatch/ocm_quotedispatch', 'quotedispatch_id');
    }
    
    public function getSubtotal($quote)
    {
        $select = $this->_getReadAdapter()->select()
            ->from(array('i' => 'ocm_quotedispatch_item'))
            ->group('i.quotedispatch_id')
            ->columns(
                array( 'subtotal' => new Zend_Db_Expr('SUM(i.price * i.qty)') )
            )
            ->where('i.quotedispatch_id = ?', $quote->getId())
            ->limit(1);

        $select = $this->_getReadAdapter()->fetchAll($select);
        
        return $select[0]['subtotal'];
    }
    
}