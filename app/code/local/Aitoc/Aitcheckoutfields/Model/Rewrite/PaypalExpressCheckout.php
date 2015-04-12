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
/**
 * @copyright  Copyright (c) 2011 AITOC, Inc. 
 */
class Aitoc_Aitcheckoutfields_Model_Rewrite_PaypalExpressCheckout extends Mage_Paypal_Model_Express_Checkout
{
    public function place($token, $shippingMethodCode = null)
    {        
    	$shippingMethodFromReq = Mage::app()->getRequest()->getPost('shipping_method');
    	
    	if (!$shippingMethodCode && $shippingMethodFromReq)
    	{
    		$this->updateShippingMethod($shippingMethodFromReq);
    	}
    
        $return = parent::place($token, $shippingMethodCode);
        $orderId = $this->getOrder() instanceof Mage_Sales_Model_Order ? $this->getOrder()->getId() : null;
		
		$recurringProfiles = $this->getRecurringPaymentProfiles();
		$recurringProfileIds = array();
		foreach($recurringProfiles as $recProfile)
		{
		    $recurringProfileIds[] =$recProfile->getId();
	    }
		if(count($recurringProfileIds) > 0)
		    Mage::dispatchEvent('aitcheckoutfields_paypal_express_order_place_after', array('order_id' => $orderId,
                                                                                            'recurring_profile_ids' => $recurringProfileIds));
		else
            Mage::dispatchEvent('aitcheckoutfields_paypal_express_order_place_after', array('order_id' => $orderId));
		return $return;
    }
}