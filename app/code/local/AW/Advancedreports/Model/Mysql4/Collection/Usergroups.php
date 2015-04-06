<?php

class AW_Advancedreports_Model_Mysql4_Collection_Usergroups extends AW_Advancedreports_Model_Mysql4_Collection_Abstract
{
    /**
     * Add groups
     *
     * @return AW_Advancedreports_Model_Mysql4_Collection_Usergroups
     */
    public function addCustomerGroups($isAllStores = false)
    {
        if ($isAllStores) {
            $currencyRate = "main_table.store_to_base_rate";
        } else {
            $currencyRate = new Zend_Db_Expr("'1'");
        }

        $itemTable = $this->_helper()->getSql()->getTable('sales_flat_order_item');
        $customerEntityTable = $this->_helper()->getSql()->getTable('customer_entity');
        $customerGroupTable = $this->_helper()->getSql()->getTable('customer_group');

        if ($this->_helper()->checkSalesVersion('1.4.0.0')) {
            $this->getSelect()
                ->join(
                    array('item' => $itemTable),
                    "main_table.entity_id = item.order_id AND item.parent_item_id IS NULL",
                    array(
                        'sum_qty'   => 'SUM(item.qty_ordered)',
                        'sum_total' => "SUM(item.base_row_total * $currencyRate) ",
                        'name'      => 'name',
                        'sku'       => 'sku',
                    )
                )
                ->joinLeft(
                    array('cust' => $customerEntityTable),
                    "main_table.customer_id = cust.entity_id AND main_table.customer_id IS NOT NULL",
                    array()
                )
                ->joinLeft(
                    array('grp' => $customerGroupTable),
                    "grp.customer_group_id = IFNULL(cust.group_id, '0')",
                    array(
                        'group_name' => 'customer_group_code',
                        'group_id' => 'customer_group_id',
                    )
                )
                ->group('grp.customer_group_id');
        } else {
            $this->getSelect()
                ->join(
                    array('item' => $itemTable),
                    "e.entity_id = item.order_id AND item.parent_item_id IS NULL",
                    array(
                        'sum_qty'   => 'SUM(item.qty_ordered)',
                        'sum_total' => "SUM(item.base_row_total * $currencyRate) ",
                        'name'      => 'name',
                        'sku'       => 'sku',
                    )
                )
                ->joinLeft(
                    array('cust' => $customerEntityTable),
                    "e.customer_id = cust.entity_id AND e.customer_id IS NOT NULL",
                    array()
                )
                ->joinLeft(
                    array('grp' => $customerGroupTable),
                    "grp.customer_group_id = IFNULL(cust.group_id, '0')",
                    array(
                        'group_name' => 'customer_group_code',
                        'group_id' => 'customer_group_id',
                    )
                )
                ->group('grp.customer_group_id');
        }
        return $this;
    }
}