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
class Aitoc_Aitcheckoutfields_Model_Mysql4_Attributecustomergroups extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {   
        $this->_init('aitoc_custom_attribute_cg', 'id');
    }    
}