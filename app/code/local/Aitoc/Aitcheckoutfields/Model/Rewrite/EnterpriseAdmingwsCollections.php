<?php
/**
 * Checkout Fields Manager
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitcheckoutfields
 * @version      10.5.7
 * @license:     grDwoQqpctpZdS57isl8WpY91kLDyrRZ7i5S4ZKTe1
 * @copyright:   Copyright (c) 2015 AITOC, Inc. (http://www.aitoc.com)
 */
class Aitoc_Aitcheckoutfields_Model_Rewrite_EnterpriseAdmingwsCollections extends Enterprise_AdminGws_Model_Collections
{
    public function addStoreAttributeToFilter($collection)
    {
        $collection->addAttributeToFilter('main_table.store_id', array('in' => $this->_role->getStoreIds()));
    }
}

?>