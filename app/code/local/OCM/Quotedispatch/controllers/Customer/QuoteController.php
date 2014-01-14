<?php

require_once 'OCM/Quotedispatch/controllers/IndexController.php';

class OCM_Quotedispatch_Customer_QuoteController extends OCM_Quotedispatch_IndexController
{

    
    protected function _checkUid() {
        
        $user_email = Mage::getSingleton('customer/session')->getCustomer()->getEmail();
        
        if ($user_email) {
            return $user_email;
        }
        return false;
        
    }


}