<?php

class OCM_Frauddetection_Model_Observer
{
    public function setOrderIsSuspectHold($observer)
    {
        $order = $observer->getOrder();
		$result = Mage::helper('ocm_frauddetection')->isViolations($order);
        if($result && 'orders_suspect_hold' !== $order->getStatus()){
            $order->setState('new','orders_suspect_hold',false,false)->save();
        } 
        
    }
}