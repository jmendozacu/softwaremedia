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
class Aitoc_Aitcheckoutfields_Block_Rewrite_FrontCustomerFormEdit extends Mage_Customer_Block_Account_Dashboard
{
    protected $_mainModel;
    
    protected function _construct()
    {
        parent::_construct();
        $this->_mainModel = Mage::getModel('aitcheckoutfields/aitcheckoutfields');
    }
    
    public function getCustomFieldsList($placeholder)
    {
        return $this->_mainModel->getCustomerAttributeList($placeholder);
    }
    
    public function getAttributeHtml($aField, $sSetName, $sPageType)
    {
        return $this->_mainModel->getAttributeHtml($aField, $sSetName, $sPageType);
    }
}