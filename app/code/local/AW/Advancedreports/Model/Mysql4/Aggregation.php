<?php

class AW_Advancedreports_Model_Mysql4_Aggregation extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('advancedreports/aggregation', 'entity_id');
    }

    public function cleanTable()
    {
        $table = $this->getMainTable();
        $write = $this->_getWriteAdapter();
        $write->beginTransaction();
        $write->exec(new Zend_Db_Expr("DELETE FROM `{$table}`"));
        $write->commit();
        return $this;
    }
}