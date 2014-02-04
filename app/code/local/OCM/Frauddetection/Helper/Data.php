<?php

class OCM_Frauddetection_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_allowMailDomain = array(
        'hotmail.com',
        'outlook.com', 
        'gmail.com',
        'yahoo.com',
        'ymail.com', 
        'rocketmail.com', 
        'earlink.net', 
        'aol.com', 
        'live.com'
    );
    protected $_allowShippingMethod = array(
        'EUROPE_FIRST_INTERNATIONAL_PRIORITY',
        'FEDEX_1_DAY_FREIGHT',
        'FEDEX_2_DAY_FREIGHT',
        'FEDEX_EXPRESS_SAVER',
        'FIRST_OVERNIGHT',
        'STANDARD_OVERNIGHT',
        'Express Mail',
        'First-Class Mail Letter',
        'Express Mail Flat-Rate Envelope Sunday/Holiday Guarantee',
        '1DM',
        '1DML',
        '1DA',
        '1DAL'
    );

    public function isViolations(Mage_Sales_Model_Order $order)
    {
        $result = false;
        $customerEmail = $order->getCustomerEmail();
        $collection = Mage::getModel('sales/order')->getCollection();
        $customerOrders = $collection->addFieldToFilter('customer_email',$customerEmail);
        
		Mage::getSingleton('core/session', array('name' => 'adminhtml')); 
		$session = Mage::getSingleton('admin/session'); 
		if ( $session->isLoggedIn() ){ 
			$admin = $session->getUser();
			if ($admin->getId()){
					return false;
		    }
		}
		    
        //Only perform check if this is the first order for that customer
        if($customerOrders->getSize()==1){
        
            // compare shippingaddress and billingaddress
            $shippingAddress = $order->getShippingAddress();
            $billingAddress = $order->getBillingAddress();
            if($result==false){
                if($shippingAddress->getRegion()!=$billingAddress->getRegion()
                    || $shippingAddress->getPostcode()!=$billingAddress->getPostcode()
                    || $shippingAddress->getCity()!=$billingAddress->getCity()
                    || $shippingAddress->getCountryId()!=$billingAddress->getCountryId()
                    || implode(',',$shippingAddress->getStreet())!=implode(',',$billingAddress->getStreet())){
                    $result = false;
                } else {
                    $result = "Fraud Detection: Shipping address does not match billing address.";
                }
            }

            // compare customerEmail's domain
            $pos = strpos($customerEmail,'@');
            $maildomain = substr($customerEmail,$pos+1);
            if($result==false){
                if(in_array($maildomain,$this->_allowMailDomain)){
                    $result = "Fraud Detection: E-mail address domain in potential blacklist";
                }
            }
            
            // check order is an international order
            if($result==false){
                if(Mage::getStoreConfig('general/country/default')!=$shippingAddress->getCountryId()){
                    $result = "Fraud Detection: International Order";
                }
            }
            
            // check order is over $2,000
            if($result==false){
                if($order->getSubtotal()>2000){
                    $result = "Fraud Detection: Order exceeds $2000";
                }
            }
            
            // check order requires overnight shipping
            if($result==false){
                if(in_array($order->getShippingMethod(),$this->_allowShippingMethod)){
                    $result = "Fraud Detection: Expedited shipping selected";
                }
            }
        }
        return $result;
    }
}