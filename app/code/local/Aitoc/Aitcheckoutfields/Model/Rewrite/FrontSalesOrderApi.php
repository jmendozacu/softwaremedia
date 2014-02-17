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
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */


class Aitoc_Aitcheckoutfields_Model_Rewrite_FrontSalesOrderApi extends Mage_Sales_Model_Order_Api
{
    // overwrite parent
    public function info($orderIncrementId)
    {
        $result = parent::info($orderIncrementId);
        
        if ($result AND $result['order_id'])
        {
            $iStoreId = $result['store_id'];
    
            $oAitcheckoutfields  = Mage::getModel('aitcheckoutfields/aitcheckoutfields');
    
            $aCustomAtrrList = $oAitcheckoutfields->getOrderCustomData($result['order_id'], $iStoreId, true);
            
            $result['aitoc_order_custom_data'] = $aCustomAtrrList;
        }
        
        return $result;
    }
    
    // overwrite parent
    public function items($filters = null)
    {
        $result = parent::items($filters);
        
        if ($result AND is_array($result))
        {
            foreach ($result as $iKey => $aOrder)
            {
                $iStoreId = $aOrder['store_id'];
        
                $oAitcheckoutfields  = Mage::getModel('aitcheckoutfields/aitcheckoutfields');
        
                $aCustomAtrrList = $oAitcheckoutfields->getOrderCustomData($aOrder['order_id'], $iStoreId, true);
                
                $result[$iKey]['aitoc_order_custom_data'] = $aCustomAtrrList;
            }
        }
        
        return $result;
    }
}