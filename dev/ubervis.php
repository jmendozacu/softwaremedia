<?php

require "../app/Mage.php";
Mage::app('admin')->setUseSessionInUrl(false);
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

//echo "test";

setShippingDescription("Shipping Option - Free Budget <span style='font-size: 70%'>(est. delivery Feb 13th)</span>");

   function setShippingDescription($description) {
    	$pos = strpos($description,'<');
    	if ($pos) {
    		//$this->setData('shipping_description',substr($description, 0, $pos -1));
    		$pos = strpos($description,'(');
    		$pos2 = strpos($description,')');
    		$estimate = substr($description, $pos + 15,-8);
    		echo date('Y-m-d',strtotime($estimate . " " . date('Y')));
    		die();
    		//$this->setDeliveryEstimate(date('Y-m-d',strtotime($estimate . " " . date('Y'))));
    		
    		
    	} else {
	    	//$this->setData('shipping_description',$description);
	    }
    }