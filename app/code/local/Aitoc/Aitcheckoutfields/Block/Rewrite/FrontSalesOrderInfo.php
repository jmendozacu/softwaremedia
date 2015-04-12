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
/* AITOC static rewrite inserts start */
/* $meta=%default,AdjustWare_Deliverydate% */
if(Mage::helper('core')->isModuleEnabled('AdjustWare_Deliverydate')){
    class Aitoc_Aitcheckoutfields_Block_Rewrite_FrontSalesOrderInfo_Aittmp extends AdjustWare_Deliverydate_Block_Rewrite_SalesOrderInfo {} 
 }else{
    /* default extends start */
    class Aitoc_Aitcheckoutfields_Block_Rewrite_FrontSalesOrderInfo_Aittmp extends Mage_Sales_Block_Order_Info {}
    /* default extends end */
}

/* AITOC static rewrite inserts end */
class Aitoc_Aitcheckoutfields_Block_Rewrite_FrontSalesOrderInfo extends Aitoc_Aitcheckoutfields_Block_Rewrite_FrontSalesOrderInfo_Aittmp
{
    public function _construct()
    {
    	parent::_construct();
        $packageName = Mage::getDesign()->getPackageName();
        if ('enterprise' == $packageName)
        {
            $this->setTemplate('aitcommonfiles/design--frontend--enterprise--default--template--sales--order--info.phtml');    
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