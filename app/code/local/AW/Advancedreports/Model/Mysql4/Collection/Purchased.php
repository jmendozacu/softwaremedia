<?php

class AW_Advancedreports_Model_Mysql4_Collection_Purchased extends AW_Advancedreports_Model_Mysql4_Collection_Abstract
{
    /**
     * Add order query to collection select
     *
     * @return AW_Advancedreports_Model_Mysql4_Collection_Purchased
     */
    public function addOrderItemsCount($isAllStores = false)
    {
        if ($isAllStores) {
            $currencyRate = "main_table.store_to_base_rate";
        } else {
            $currencyRate = new Zend_Db_Expr("'1'");
        }
        $itemTable = $this->_helper()->getSql()->getTable('sales_flat_order_item');
        if ($this->_helper()->checkSalesVersion('1.4.0.0')) {
            $this->getSelect()
                ->join(
                    array('item' => $itemTable),
                    "(item.order_id = main_table.entity_id AND item.parent_item_id IS NULL)",
                    array('sum_qty' => 'SUM(item.qty_ordered)')
                )
                ->where("main_table.entity_id = item.order_id")
                ->group('main_table.entity_id');
        } else {
            $this->getSelect()
                ->join(
                    array('item' => $itemTable),
                    "(item.order_id = e.entity_id AND item.parent_item_id IS NULL)",
                    array('sum_qty' => 'SUM(item.qty_ordered)')
                )
                ->where("e.entity_id = item.order_id")
                ->group('e.entity_id');
        }
        $this->getSelect()
            ->columns(
                array(
                     'x_base_total'          => "(base_grand_total * $currencyRate)",
                     'x_base_total_invoiced' => "(base_total_invoiced * $currencyRate)",
                     'x_base_total_refunded' => "(base_total_refunded * $currencyRate)",
                )
            );
        return $this;
    }
}