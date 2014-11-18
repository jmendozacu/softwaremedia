<?php

class OCM_Checkout_Model_Checkout_Observer extends Mage_Checkout_Model_Observer {


 public function loadCustomerQuote()
    { 
    
        $lastQid = Mage::getSingleton('checkout/session')->getQuoteId(); //quote id during session before login;
        if ($lastQid) { //before login session exists means cart has items                            
            $customerQuote = Mage::getModel('sales/quote')
                ->loadByCustomer(Mage::getSingleton('customer/session')->getCustomerId()); //the cart from last login         
            //set it to the session before login and remove its items if any
            //$customerQuote->setQuoteId($lastQid);
            //$this->_removeAllItems($customerQuote);
            
        } else { //no session before login, so empty the cart (current cart is the old cart)
            //$quote = Mage::getModel('checkout/session')->getQuote();                                
            //$this->_removeAllItems($quote);
        }        
    }
    
    /**
     * iterate through quote and remove all items
     *           
     * @return nothing
     */
    protected function _removeAllItems($quote){
        //reset all custom attributes in the quote object here, eg:     
       // $quote->setDestinationCity('');
        
        foreach ($quote->getAllItems() as $item) {
            $item->isDeleted(true);
            if ($item->getHasChildren()) foreach ($item->getChildren() as $child) $child->isDeleted(true);
        }
        $quote->collectTotals()->save();        
    } //_removeAllItems 

    
}