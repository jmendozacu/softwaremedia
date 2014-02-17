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
class Aitoc_Aitcheckoutfields_Model_Mysql4_Attributecategoryrefs extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {   
        $this->_init('aitoc_custom_attribute_cat_refs', 'id');
    }    
}