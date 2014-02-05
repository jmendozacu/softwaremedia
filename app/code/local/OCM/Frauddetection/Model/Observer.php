<?php

class OCM_Frauddetection_Model_Observer
{
    public function setOrderIsSuspectHold($observer)
    {
        $order = $observer->getOrder();
		$result = Mage::helper('ocm_frauddetection')->isViolations($order);
        if($result){
            $order->setState('new','orders_suspect_hold',$result,false)->save();
        } 
        
    }
}