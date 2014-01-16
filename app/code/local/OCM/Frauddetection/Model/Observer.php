<?php

class OCM_Frauddetection_Model_Observer
{
    public function setOrderIsSuspectHold($observer)
    {
        $order = $observer->getOrder();

        if(Mage::helper('ocm_frauddetection')->isViolations($order)){
            $order->setStatus('orders_suspect_hold',true)->save();
        } 
        
    }
}