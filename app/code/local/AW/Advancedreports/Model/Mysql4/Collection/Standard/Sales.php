<?php

class AW_Advancedreports_Model_Mysql4_Collection_Standard_Sales
    extends AW_Advancedreports_Model_Mysql4_Collection_Abstract
{
    /**
     * Reinitialize select
     *
     * @return AW_Advancedreports_Model_Mysql4_Collection_Product
     */
    public function reInitSelect()
    {
        if ($this->_helper()->checkSalesVersion('1.4.0.0')) {
            $orderTable = $this->_helper()->getSql()->getTable('sales_flat_order');
        } else {
            $orderTable = $this->_helper()->getSql()->getTable('sales_order');
        }
        $this->getSelect()->reset();
        $this->getSelect()->from(array($this->_getSalesCollectionTableAlias() => $orderTable), array());
        return $this;
    }

    /**
     * Add order columns
     *
     * @return AW_Advancedreports_Model_Mysql4_Collection_Standard_Sales
     */
    public function addSumColumns($isAllStores = false)
    {
        $orderTableAlias = $this->_getSalesCollectionTableAlias();

        if ($isAllStores) {
            $currencyRate = "{$orderTableAlias}.store_to_base_rate";
        } else {
            $currencyRate = new Zend_Db_Expr("'1'");

        }

        $this->getSelect()->columns(
            array(
                 'orders'   => "COUNT({$orderTableAlias}.entity_id)", # Just because it's unique
                 'subtotal' => "SUM({$orderTableAlias}.base_subtotal * $currencyRate)",
                 'tax'      => "SUM({$orderTableAlias}.base_tax_amount * $currencyRate)",
                 'discount' => "SUM({$orderTableAlias}.base_discount_amount * $currencyRate)",
                 'shipping' => "SUM({$orderTableAlias}.base_shipping_amount * $currencyRate)",
                 'total'    => "SUM({$orderTableAlias}.base_grand_total * $currencyRate)",
                 'invoiced' => "SUM({$orderTableAlias}.base_total_invoiced * $currencyRate)",
                 'refunded' => "SUM({$orderTableAlias}.base_total_refunded * $currencyRate)",
                 'int_1'    => "ROUND(1)",
            )
        );
        return $this;
    }

    public function addItems()
    {
        $itemTable = $this->_helper()->getSql()->getTable('sales_flat_order_item');
        $orderTableAlias = $this->_getSalesCollectionTableAlias();
        $this->getSelect()
            ->join(
                array('item' => $itemTable),
                "{$orderTableAlias}.entity_id = item.order_id AND item.parent_item_id IS NULL",
                array(
                    'items_count' => 'SUM(item.qty_ordered)',
                )
            );
        return $this;
    }

    /**
     * Group by Entity_Id
     *
     * @return AW_Advancedreports_Model_Mysql4_Collection_Standard_Sales
     */
    public function addGroupByEntityId()
    {
        $orderTableAlias = $this->_getSalesCollectionTableAlias();
        $this->getSelect()->group("{$orderTableAlias}.entity_id");
        return $this;
    }

    /**
     * Group by INT_1
     *
     * @return AW_Advancedreports_Model_Mysql4_Collection_Standard_Sales
     */
    public function addGroupByIntOne()
    {
        $this->getSelect()->group('int_1');
        return $this;
    }
}