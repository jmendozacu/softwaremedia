<?php

class AW_Advancedreports_Model_Mysql4_Order_Collection extends Mage_Sales_Model_Mysql4_Order_Collection
{
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
     * Before load action
     *
     * @return Varien_Data_Collection_Db
     */
    protected function _beforeLoad()
    {
        parent::_beforeLoad();

        if ($this->_helper()->checkCatalogPermissionsActive()) {
            $wherePart = $this->getSelect()->getPart(Zend_Db_Select::SQL_WHERE);
            $this->getSelect()->reset(Zend_Db_Select::WHERE);
            $weHaveStoreId = false;
            foreach ($wherePart as $where) {
                if (strpos($where, "store_id") !== false) {
                    if (!$weHaveStoreId) {
                        if ($this->_helper()->getNeedMainTableAlias()) {
                            $this->getSelect()->where(
                                str_replace("AND ", "", str_replace("(store_id", "(main_table.store_id", $where))
                            );
                        } else {
                            $this->getSelect()->where(
                                str_replace("AND ", "", str_replace("(store_id", "(e.store_id", $where))
                            );
                        }
                        $weHaveStoreId = true;
                    }
                } else {
                    $this->getSelect()->where(str_replace("AND ", "", $where));
                }
            }
        }
        return $this;
    }

    /**
     * Set up store ids to filter collection
     *
     * @param int|array $storeIds
     *
     * @return AW_Advancedreports_Model_Mysql4_Collection_Abstract
     */
    public function setStoreFilter($storeIds)
    {
        if (is_integer($storeIds)) {
            $storeIds = array($storeIds);
        }
        if ($this->_helper()->checkSalesVersion('1.4.0.0')) {
            $this->getSelect()
                ->where("main_table.store_id in ('" . implode("','", $storeIds) . "')");
        } else {
            $this->getSelect()
                ->where("e.store_id in ('" . implode("','", $storeIds) . "')");
        }
        return $this;
    }
}