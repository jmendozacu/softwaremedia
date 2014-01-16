<?php
class OCM_Quotedispatch_Block_View extends OCM_Quotedispatch_Block_Abstract
{

    public function getViewAllLink() {
        
         if($this->helper('customer')->isLoggedIn()) {
             return $this->getUrl('customer/quote/');
         } else {
             return $this->getUrl('quotedispatch').'?uid='.$this->getUid();
         }
        
    }

}