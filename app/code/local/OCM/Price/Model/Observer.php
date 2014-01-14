<?php


class OCM_Price_Model_Observer {
    
    public function adjustFinalPrice($observer) {
    
    
        if (Mage::getModel('core/cookie')->get(OCM_Peachtree_Model_Observer::COOKIE_NAME) || Mage::registry(OCM_Peachtree_Model_Observer::COOKIE_NAME)) {

           $event = $observer->getEvent();
           $product = $event->getProduct();
           
           $cpc_price = $product->getCpcPrice();
           
           if(!is_null($cpc_price)) {
               $product->setFinalPrice($cpc_price);
           }
        }
        return;
    }
    
    
}