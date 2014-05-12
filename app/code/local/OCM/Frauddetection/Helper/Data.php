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
        'earthlink.net', 
        'aol.com', 
        'live.com',
        "mail.com","email.com","usa.com","myself.com","consultant.com","post.com","europe.com","asia.com","iname.com","writeme.com","dr.com","engineer.com","cheerful.com","accountant.com","techie.com","linuxmail.org","lawyer.com","uymail.com","contractor.net","accountant.com","activist.com","adexec.com","allergist.com","alumni.com","alumnidirector.com","angelic.com","appraiser.net","archaeologist.com","arcticmail.com","artlover.com","asia.com","auctioneer.net","bartender.net","bikerider.com","birdlover.com","brew-meister.com","cash4u.com","chef.net","chemist.com","clerk.com","clubmember.org","collector.org","columnist.com","comic.com","computer4u.com","consultant.com","contractor.net","coolsite.net","counsellor.com","cyberservices.com","deliveryman.com","diplomats.com","disposable.com","doctor.com","dr.com","engineer.com","execs.com","fastservice.com","financier.com","fireman.net","gardener.com","geologist.com","graduate.org","graphic-designer.com","groupmail.com","hairdresser.net","homemail.com","hot-shot.com","instruction.com","instructor.net","insurer.com","job4u.com","journalist.com","lawyer.com","legislator.com","lobbyist.com","minister.com","musician.org","myself.com","net-shopping.com","optician.com","orthodontist.net","pediatrician.com","photographer.net","physicist.net","planetmail.com","planetmail.net","politician.com","post.com","presidency.com","priest.com","programmer.net","publicist.com","qualityservice.com","radiologist.net","realtyagent.com","registerednurses.com","repairman.com","representative.com","rescueteam.com","salesperson.net","scientist.com","secretary.net","socialworker.net","sociologist.com","solution4u.com","songwriter.net","surgical.net","teachers.org","tech-center.com","techie.com","technologist.com","theplate.com","therapist.net","toothfairy.com","tvstar.com","umpire.com","webname.com","worker.com","workmail.com","writeme.com","2trom.com","activist.com","aircraftmail.com","artlover.com","atheist.com","bikerider.com","birdlover.com","blader.com","boardermail.com","brew-master.com","brew-meister.com","bsdmail.com","catlover.com","chef.net","clubmember.org","collector.org","cutey.com","dbzmail.com","doglover.com","doramail.com","galaxyhit.com","gardener.com","greenmail.net","hackermail.com","hilarious.com","keromail.com","kittymail.com","linuxmail.org","lovecat.com","marchmail.com","musician.org","nonpartisan.com","petlover.com","photographer.net","snakebite.com","songwriter.net","techie.com","theplate.com","toke.com","uymail.com","computer4u.com","consultant.com","contractor.net","coolsite.net","cyberdude.com","cybergal.com","cyberservices.com","cyber-wizard.com","engineer.com","fastservice.com","graphic-designer.com","groupmail.com","homemail.com","hot-shot.com","housemail.com","humanoid.net","iname.com","inorbit.com","mail-me.com","myself.com","net-shopping.com","null.net","physicist.net","planetmail.com","planetmail.net","post.com","programmer.net","qualityservice.com","rocketship.com","scientist.com","solution4u.com","tech-center.com","techie.com","technologist.com","webname.com","workmail.com","writeme.com","acdcfan.com","angelic.com","artlover.com","atheist.com","chemist.com","diplomats.com","discofan.com","elvisfan.com","execs.com","hiphopfan.com","housemail.com","kissfans.com","madonnafan.com","metalfan.com","minister.com","musician.org","ninfan.com","oath.com","ravemail.com","reborn.com","reggaefan.com","snakebite.com","songwriter.net","bellair.net","californiamail.com","dallasmail.com","nycmail.com","pacific-ocean.com","pacificwest.com","sanfranmail.com","usa.com","africamail.com","arcticmail.com","asia.com","asia-mail.com","australiamail.com","berlin.com","brazilmail.com","chinamail.com","dublin.com","dutchmail.com","englandmail.com","europe.com","europemail.com","germanymail.com","irelandmail.com","israelmail.com","italymail.com","koreamail.com","mexicomail.com","moscowmail.com","munich.com","polandmail.com","safrica.com","samerica.com","scotlandmail.com","spainmail.com","swedenmail.com","swissmail.com","torontomail.com","angelic.com","atheist.com","disciples.com","innocent.com","minister.com","muslim.com","oath.com","priest.com","protestant.com","reborn.com","reincarnate.com","religious.com","saintly.com"
    );
    protected $_alwaysBlacklist = array(
    	        "mail.com","email.com","usa.com","myself.com","consultant.com","post.com","europe.com","asia.com","iname.com","writeme.com","dr.com","engineer.com","cheerful.com","accountant.com","techie.com","linuxmail.org","lawyer.com","uymail.com","contractor.net","accountant.com","activist.com","adexec.com","allergist.com","alumni.com","alumnidirector.com","angelic.com","appraiser.net","archaeologist.com","arcticmail.com","artlover.com","asia.com","auctioneer.net","bartender.net","bikerider.com","birdlover.com","brew-meister.com","cash4u.com","chef.net","chemist.com","clerk.com","clubmember.org","collector.org","columnist.com","comic.com","computer4u.com","consultant.com","contractor.net","coolsite.net","counsellor.com","cyberservices.com","deliveryman.com","diplomats.com","disposable.com","doctor.com","dr.com","engineer.com","execs.com","fastservice.com","financier.com","fireman.net","gardener.com","geologist.com","graduate.org","graphic-designer.com","groupmail.com","hairdresser.net","homemail.com","hot-shot.com","instruction.com","instructor.net","insurer.com","job4u.com","journalist.com","lawyer.com","legislator.com","lobbyist.com","minister.com","musician.org","myself.com","net-shopping.com","optician.com","orthodontist.net","pediatrician.com","photographer.net","physicist.net","planetmail.com","planetmail.net","politician.com","post.com","presidency.com","priest.com","programmer.net","publicist.com","qualityservice.com","radiologist.net","realtyagent.com","registerednurses.com","repairman.com","representative.com","rescueteam.com","salesperson.net","scientist.com","secretary.net","socialworker.net","sociologist.com","solution4u.com","songwriter.net","surgical.net","teachers.org","tech-center.com","techie.com","technologist.com","theplate.com","therapist.net","toothfairy.com","tvstar.com","umpire.com","webname.com","worker.com","workmail.com","writeme.com","2trom.com","activist.com","aircraftmail.com","artlover.com","atheist.com","bikerider.com","birdlover.com","blader.com","boardermail.com","brew-master.com","brew-meister.com","bsdmail.com","catlover.com","chef.net","clubmember.org","collector.org","cutey.com","dbzmail.com","doglover.com","doramail.com","galaxyhit.com","gardener.com","greenmail.net","hackermail.com","hilarious.com","keromail.com","kittymail.com","linuxmail.org","lovecat.com","marchmail.com","musician.org","nonpartisan.com","petlover.com","photographer.net","snakebite.com","songwriter.net","techie.com","theplate.com","toke.com","uymail.com","computer4u.com","consultant.com","contractor.net","coolsite.net","cyberdude.com","cybergal.com","cyberservices.com","cyber-wizard.com","engineer.com","fastservice.com","graphic-designer.com","groupmail.com","homemail.com","hot-shot.com","housemail.com","humanoid.net","iname.com","inorbit.com","mail-me.com","myself.com","net-shopping.com","null.net","physicist.net","planetmail.com","planetmail.net","post.com","programmer.net","qualityservice.com","rocketship.com","scientist.com","solution4u.com","tech-center.com","techie.com","technologist.com","webname.com","workmail.com","writeme.com","acdcfan.com","angelic.com","artlover.com","atheist.com","chemist.com","diplomats.com","discofan.com","elvisfan.com","execs.com","hiphopfan.com","housemail.com","kissfans.com","madonnafan.com","metalfan.com","minister.com","musician.org","ninfan.com","oath.com","ravemail.com","reborn.com","reggaefan.com","snakebite.com","songwriter.net","bellair.net","californiamail.com","dallasmail.com","nycmail.com","pacific-ocean.com","pacificwest.com","sanfranmail.com","usa.com","africamail.com","arcticmail.com","asia.com","asia-mail.com","australiamail.com","berlin.com","brazilmail.com","chinamail.com","dublin.com","dutchmail.com","englandmail.com","europe.com","europemail.com","germanymail.com","irelandmail.com","israelmail.com","italymail.com","koreamail.com","mexicomail.com","moscowmail.com","munich.com","polandmail.com","safrica.com","samerica.com","scotlandmail.com","spainmail.com","swedenmail.com","swissmail.com","torontomail.com","angelic.com","atheist.com","disciples.com","innocent.com","minister.com","muslim.com","oath.com","priest.com","protestant.com","reborn.com","reincarnate.com","religious.com","saintly.com"
			);
    protected $_allowShippingMethod = array(
        'EUROPE_FIRST_INTERNATIONAL_PRIORITY',
        'FEDEX_1_DAY_FREIGHT',
        'FEDEX_2_DAY_FREIGHT',
        'FEDEX_EXPRESS_SAVER',
        'FIRST_OVERNIGHT',
        'STANDARD_OVERNIGHT',
        'productmatrix_Priority_Overnight',
        'productmatrix_Overnight_Saturday',
        'productmatrix_Overnight',
        'productmatrix_Standard_Overnight',
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
        $customerOrders = $collection->addFieldToFilter('customer_email',$customerEmail)->addFieldToFilter('status', 'complete');
        
        $pos = strpos($customerEmail,'@');
        $maildomain = substr($customerEmail,$pos+1);
        if(in_array($maildomain,$this->_alwaysBlacklist)){
        	return "Fraud Detection: Always Blacklist E-mail Domain";
        	
        if ($customerOrders->getSize()>0)
        	return false;
        else
        	return "Fraud Detection: First time order, please review";
        		
		Mage::getSingleton('core/session', array('name' => 'adminhtml')); 
		$session = Mage::getSingleton('admin/session'); 
		if ( $session->isLoggedIn() ){ 
			$admin = $session->getUser();
			if ($admin->getId()){
					return false;
		    }
		}
	
		  
        //Only perform check if this is the first order for that customer
        Mage::log('Customer Orders: ' . $customerOrders->getSize(),null,'fraud.log');

        // compare shippingaddress and billingaddress
        $shippingAddress = $order->getShippingAddress();
        $billingAddress = $order->getBillingAddress();
        if($result==false){
            if($shippingAddress->getRegion()!=$billingAddress->getRegion()
                || $shippingAddress->getPostcode()!=$billingAddress->getPostcode()
                || $shippingAddress->getCity()!=$billingAddress->getCity()
                || $shippingAddress->getCountryId()!=$billingAddress->getCountryId()
                || implode(',',$shippingAddress->getStreet())!=implode(',',$billingAddress->getStreet())){
                
                $result = "Fraud Detection: Shipping address does not match billing address.";
            } else {
                
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
    
        return $result;
    }
}