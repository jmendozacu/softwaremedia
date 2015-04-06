<?php

class AW_Advancedreports_Model_Mysql4_Collection_Dayofweek extends AW_Advancedreports_Model_Mysql4_Collection_Abstract
{
    /**
     * Set up day of week filter
     * $dayofweek = 1,2,3,4,5,6,7
     *
     * @return AW_Advancedreports_Model_Mysql4_Collection_Dayofweek
     */
    public function setDayOfWeekFilter($isAllStores = false)
    {
        if ($isAllStores) {
            $currencyRate = "main_table.store_to_base_rate";
        } else {
            $currencyRate = new Zend_Db_Expr("'1'");
        }

        $itemTable = $this->_helper()->getSql()->getTable('sales_flat_order_item');
        $filterField = $this->_helper()->confOrderDateFilter();
        $globalTz = $this->_helper()->getTimeZoneOffset(true);

        if ($this->_helper()->checkSalesVersion('1.4.0.0')) {
            $this->getSelect()
                ->join(
                    array('item' => $itemTable),
                    "main_table.entity_id = item.order_id AND item.parent_item_id IS NULL",
                    array(
                        'day_of_week' => "DAYOFWEEK(CONVERT_TZ(main_table.{$filterField}, '+00:00', '{$globalTz}'))",
                        'sum_qty'     => 'SUM(item.qty_ordered)',
                        'sum_total'   => "SUM(item.base_row_total * $currencyRate)",
                        'name'        => 'name', 'sku' => 'sku',
                    )
                )
                ->group('day_of_week');
        } else {
            $this->getSelect()
                ->join(
                    array('item' => $itemTable),
                    "e.entity_id = item.order_id AND item.parent_item_id IS NULL",
                    array(
                        'day_of_week' => "DAYOFWEEK(CONVERT_TZ(e.{$filterField}, '+00:00', '{$globalTz}'))",
                        'sum_qty'     => 'SUM(item.qty_ordered)',
                        'sum_total'   => "SUM(item.base_row_total * $currencyRate)",
                        'name'        => 'name',
                        'sku'         => 'sku',
                    )
                )
                ->group('day_of_week');
        }
        return $this;
    }
}