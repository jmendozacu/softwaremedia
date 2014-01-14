<?php
class OCM_Quotedispatch_Block_Abstract extends Mage_Core_Block_Template
{
	public function _prepareLayout() {
		return parent::_prepareLayout();
    }
    
     public function getQuoteCollection() { 
        if (!$this->hasData('quote_collection')) {
            $this->setData('quote_collection', Mage::registry('qdquotes'));
        }
        return $this->getData('quote_collection');
        
    }

     public function getEmail() { 
         $email = $this->getQuoteCollection()->getFirstItem()->getEmail();
         return $email;    
    }
    
    public function getUid() {
        return Mage::app()->getRequest()->getParam('uid');
    }

    public function getQuote() {
        
        if (!$this->hasData('quote')) {
            $this->setData('quote', Mage::registry('current_quote'));
        }
        return $this->getData('quote');
    }
    
    public function getQuoteUrl($quote_id) {
        $user_email = Mage::getSingleton('customer/session')->getCustomer()->getEmail();
        if($user_email) {
            return $this->getUrl('customer/quote/view',array('id'=>$quote_id));
        }
        
        return $this->getUrl('quotedispatch/index/view',array('id'=>$quote_id)) . "?uid=" . $this->getUid();
    }

    public function getOrderQuoteUrl($quote_id) {
        $user_email = Mage::getSingleton('customer/session')->getCustomer()->getEmail();
        if($user_email) {
            return $this->getUrl('customer/quote/order',array('id'=>$quote_id));
        }

        return $this->getUrl('quotedispatch/index/order') . "?uid=" . $this->getUid() . "&id=" . $quote_id;
    }


}