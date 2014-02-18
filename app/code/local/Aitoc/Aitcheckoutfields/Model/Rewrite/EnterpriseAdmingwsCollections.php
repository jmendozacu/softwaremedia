<?php
/**
 * Checkout Fields Manager
 *
 * @category:    Aitoc
 * @package:     Aitoc_Aitcheckoutfields
 * @version      10.5.3
 * @license:     x8JlL6PzUPBtvXBsIIWQy9KjFdhME32yIbvID6DGHQ
 * @copyright:   Copyright (c) 2014 AITOC, Inc. (http://www.aitoc.com)
 */
class Aitoc_Aitcheckoutfields_Model_Rewrite_EnterpriseAdmingwsCollections extends Enterprise_AdminGws_Model_Collections
{
    public function addStoreAttributeToFilter($collection)
    {
        $collection->addAttributeToFilter('main_table.store_id', array('in' => $this->_role->getStoreIds()));
    }
}

?>