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
class Aitoc_Aitcheckoutfields_Block_Rewrite_FrontSalesOrderView  extends Mage_Sales_Block_Order_View
{
	public function _construct()
    {
    	parent::_construct();
        $packageName = Mage::getDesign()->getPackageName();
        if ('base' == $packageName)
        {
            $this->setTemplate('aitcommonfiles/design--frontend--base--default--template--sales--order--view.phtml');     
        }
    }
        
    public function getOrderCustomData()
    {
        $iStoreId = $this->getOrder()->getStoreId();

        $oFront = Mage::app()->getFrontController();
        
        $iOrderId = $oFront->getRequest()->getParam('order_id');
        
        $oAitcheckoutfields  = Mage::getModel('aitcheckoutfields/aitcheckoutfields');

        $aCustomAtrrList = $oAitcheckoutfields->getOrderCustomData($iOrderId, $iStoreId, false, true);

        return $aCustomAtrrList;
    }
}
?>