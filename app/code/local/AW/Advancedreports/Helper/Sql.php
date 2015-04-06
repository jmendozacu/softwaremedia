<?php

/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/LICENSE-M1.txt
 *
 * @category   AW
 * @package    AW_Advancedreports
 * @copyright  Copyright (c) 2009-2011 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/LICENSE-M1.txt
 */

class AW_Advancedreports_Helper_Sql extends AW_Advancedreports_Helper_Abstract
{
    /**
     * Table names cache
     *
     * @var array
     */
    protected $_tables = array();

    /**
     * Retrieves name of table in DB
     *
     * @param string $tableName
     *
     * @return string
     */
    public function getTable($tableName)
    {
        if (!isset($this->_tables[$tableName])) {
            $this->_tables[$tableName] = Mage::getSingleton('core/resource')->getTableName($tableName);
        }
        return $this->_tables[$tableName];
    }

    /**
     * Retrieves filter string
     *
     * @return string
     */
    public function getProcessStates()
    {
        $states = explode(",", $this->_helper()->confProcessOrders());
        $isFirst = true;
        $filter = "";
        foreach ($states as $state) {
            if (!$isFirst) {
                $filter .= " OR ";
            }
            $filter .= "val.value = '" . $state . "'";
            $isFirst = false;
        }
        return "(" . $filter . ")";
    }
}