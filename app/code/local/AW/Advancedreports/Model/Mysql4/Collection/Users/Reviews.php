<?php

class AW_Advancedreports_Model_Mysql4_Collection_Users_Reviews extends Mage_Review_Model_Mysql4_Review_Collection
{
    /**
     * Set up date filter to collection of grid
     *
     * @param Datetime $from
     * @param Datetime $to
     *
     * @return AW_Advancedreports_Model_Mysql4_Collection_Users_Reviews
     */
    public function setDateFilter($from, $to)
    {
        $this->getSelect()
            ->where("main_table.created_at >= ?", $from)
            ->where("main_table.created_at <= ?", $to)
        ;
        return $this;
    }
}