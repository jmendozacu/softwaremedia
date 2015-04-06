<?php

class AW_Advancedreports_Model_Mysql4_Collection_Abstract extends AW_Advancedreports_Model_Mysql4_Order_Collection
{
    /**
     * Not use inside all whose wrappers
     * Notice: Use $this->_helper()->getSql()->getTable($table) instead
     *
     * @deprecated
     *
     * @param $table
     *
     * @return string
     */
    public function getTable($table)
    {
        return parent::getTable($table);
    }

    /**
     * Retrieves helper
     *
     * @return AW_Advancedreports_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('advancedreports');
    }

    /**
     * Set up date filter to collection of grid
     *
     * @param Datetime $from
     * @param Datetime $to
     *
     * @return AW_Advancedreports_Model_Mysql4_Collection_Abstract
     */
    public function setDateFilter($from, $to)
    {
        $filterField = $this->_helper()->confOrderDateFilter();

        if ($this->_helper()->checkSalesVersion('1.4.0.0')) {
            $this->getSelect()
                ->where("main_table.{$filterField} >= ?", $from)
                ->where("main_table.{$filterField} <= ?", $to)
            ;
        } else {
            $this->getSelect()
                ->where("e.{$filterField} >= ?", $from)
                ->where("e.{$filterField} <= ?", $to)
            ;
        }
        return $this;
    }

    /**
     * Set up order state filter
     *
     * @return AW_Advancedreports_Model_Mysql4_Collection_Abstract
     */
    public function setState()
    {
        if ($this->_helper()->checkSalesVersion('1.4.0.0')) {
            $this->addAttributeToFilter('status', explode(",", $this->_helper()->confProcessOrders()));
        } else {
            $entityValues = $this->_helper()->getSql()->getTable('sales_order_varchar');
            $entityAtribute = $this->_helper()->getSql()->getTable('eav_attribute');
            $this->getSelect()
                ->join(array('attr' => $entityAtribute), "attr.attribute_code = 'status'", array())
                ->join(
                    array('val' => $entityValues),
                    "attr.attribute_id = val.attribute_id AND " . $this->_helper()->getSql()->getProcessStates()
                    . " AND e.entity_id = val.entity_id",
                    array()
                );
        }
        return $this;
    }

    /**
     * Sales Collection Table Alias
     *
     * @return string
     */
    protected function _getSalesCollectionTableAlias()
    {
        if ($this->_helper()->checkSalesVersion('1.4.0.0')) {
            return 'main_table';
        } else {
            return 'e';
        }
    }
}