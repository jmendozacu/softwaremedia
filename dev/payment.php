<?php
require "../app/Mage.php";

$txRequest = new StdClass();
/*
//If voiding a transaction change newOrderRequest to reversalRequest
$txRequest->newOrderRequest = new StdClass();
$txRequest->newOrderRequest->orbitalConnectionUsername = Mage::getStoreConfig('payment/chasePaymentTech/username', Mage::app()->getStore());
$txRequest->newOrderRequest->orbitalConnectionPassword = Mage::getStoreConfig('payment/chasePaymentTech/password', Mage::app()->getStore());
$txRequest->newOrderRequest->version = '2.8';
$txRequest->newOrderRequest->industryType = 'EC';
$txRequest->newOrderRequest->bin = Mage::getStoreConfig('payment/chasePaymentTech/bin', Mage::app()->getStore());
$txRequest->newOrderRequest->merchantID = Mage::getStoreConfig('payment/chasePaymentTech/merchant_id', Mage::app()->getStore());
$txRequest->newOrderRequest->terminalID = Mage::getStoreConfig('payment/chasePaymentTech/terminal_id', Mage::app()->getStore());
$txRequest->newOrderRequest->customerRefNum = '45461SAAX';
$txRequest->newOrderRequest->avsZip = '66666';
$txRequest->newOrderRequest->avsAddress1 = '916 S, MAIN STREET';
$txRequest->newOrderRequest->avsCity = 'SALT LAKE CITY';

//$txRequest->newOrderRequest->customerPhone = "801-872-3456";
$txRequest->newOrderRequest->orderID = "1232436";

//$txRequest->newOrderRequest->orderID = '182322';
$txRequest->newOrderRequest->amount = 3000;

//If refunding or voiding, pass in transaction ID here
//$txRequest->newOrderRequest->txRefNum = '534C1F54C423ED6661FF9619129DA73763BA5459';


//Transtype (newOrderRequest only):
//A  = Auth Only
//AC = Auth & Capture
//R  = Refund

$txRequest->newOrderRequest->transType = 'A';

*/
/*
$txRequest = new StdClass();
$txRequest->profileAddRequest = new StdClass();
$txRequest->profileAddRequest->orbitalConnectionUsername = Mage::getStoreConfig('payment/chasePaymentTech/username', Mage::app()->getStore());
$txRequest->profileAddRequest->orbitalConnectionPassword = Mage::getStoreConfig('payment/chasePaymentTech/password', Mage::app()->getStore());
$txRequest->profileAddRequest->bin = Mage::getStoreConfig('payment/chasePaymentTech/bin', Mage::app()->getStore());
$txRequest->profileAddRequest->merchantID = Mage::getStoreConfig('payment/chasePaymentTech/merchant_id', Mage::app()->getStore());
$txRequest->profileAddRequest->customerName = "Jeff Losee";
$txRequest->profileAddRequest->customerRefNum = '';
$txRequest->profileAddRequest->customerAddress1 = "Address Line 1";
$txRequest->profileAddRequest->customerCity = "South Jordan";
$txRequest->profileAddRequest->customerState = "UT";
$txRequest->profileAddRequest->customerZIP = "84095";
$txRequest->profileAddRequest->customerEmail = "jeff@jaldev.com";
$txRequest->profileAddRequest->customerPhone = "801-872-3456";
$txRequest->profileAddRequest->customerCountryCode = "US";
$txRequest->profileAddRequest->ccAccountNum = '5454545454545454';
$txRequest->profileAddRequest->ccExp = "2014" . sprintf('%02d', "08");

$txRequest->profileAddRequest->orderDefaultAmount = 2000;
$txRequest->profileAddRequest->mbType = "D";
$txRequest->profileAddRequest->mbDeferredBillDate = "10012014";
$txRequest->profileAddRequest->mbOrderIDGenerationMethod = "DI";



$txRequest->profileAddRequest->customerProfileAction = 'C';
$txRequest->profileAddRequest->customerProfileOrderOverideInd = 'OI';

// Tell Orbital to autogenerate the profile number
$txRequest->profileAddRequest->customerProfileFromOrderInd = 'A';

// Customer's Payment Type to save in the profile
$txRequest->profileAddRequest->customerAccountType = 'CC';

*/

/*
$txRequest = new StdClass();
$txRequest->profileChangeRequest = new StdClass();
$txRequest->profileChangeRequest->orbitalConnectionUsername = Mage::getStoreConfig('payment/chasePaymentTech/username', Mage::app()->getStore());
$txRequest->profileChangeRequest->orbitalConnectionPassword = Mage::getStoreConfig('payment/chasePaymentTech/password', Mage::app()->getStore());
$txRequest->profileChangeRequest->bin = Mage::getStoreConfig('payment/chasePaymentTech/bin', Mage::app()->getStore());
$txRequest->profileChangeRequest->merchantID = Mage::getStoreConfig('payment/chasePaymentTech/merchant_id', Mage::app()->getStore());
$txRequest->profileChangeRequest->customerRefNum = '36086112';
// What are we doing: CRUD
$txRequest->profileChangeRequest->customerProfileAction = 'AU';

//$txRequest->profileChangeRequest->ccAccountNum = '6011000995500000';
//$txRequest->profileChangeRequest->customerAddress1 = "New Address Line 1";
//$txRequest->profileChangeRequest->customerPhone = "801-872-1234";

$txRequest->profileChangeRequest->orderDefaultAmount = 4500;
$txRequest->profileChangeRequest->amount = 4500;
$txRequest->profileChangeRequest->mbType = "D";
$txRequest->profileChangeRequest->mbDeferredBillDate = "10012014";
$txRequest->profileChangeRequest->mbOrderIDGenerationMethod = "DI";
*/

$txRequest = new StdClass();
$txRequest->profileFetchRequest = new StdClass();
$txRequest->profileFetchRequest->orbitalConnectionUsername = Mage::getStoreConfig('payment/chasePaymentTech/username', Mage::app()->getStore());
$txRequest->profileFetchRequest->orbitalConnectionPassword = Mage::getStoreConfig('payment/chasePaymentTech/password', Mage::app()->getStore());
$txRequest->profileFetchRequest->bin = Mage::getStoreConfig('payment/chasePaymentTech/bin', Mage::app()->getStore());
$txRequest->profileFetchRequest->merchantID = Mage::getStoreConfig('payment/chasePaymentTech/merchant_id', Mage::app()->getStore());
$txRequest->profileFetchRequest->customerRefNum = '36086944';

// What are we doing: CRUD
$txRequest->profileFetchRequest->customerProfileAction = 'R';


/*
$txRequest = new StdClass();
$txRequest->profileDeleteRequest = new StdClass();
$txRequest->profileDeleteRequest->orbitalConnectionUsername = Mage::getStoreConfig('payment/chasePaymentTech/username', Mage::app()->getStore());
$txRequest->profileDeleteRequest->orbitalConnectionPassword = Mage::getStoreConfig('payment/chasePaymentTech/password', Mage::app()->getStore());
$txRequest->profileDeleteRequest->bin = Mage::getStoreConfig('payment/chasePaymentTech/bin', Mage::app()->getStore());
$txRequest->profileDeleteRequest->merchantID = Mage::getStoreConfig('payment/chasePaymentTech/merchant_id', Mage::app()->getStore());
$txRequest->profileDeleteRequest->customerRefNum = '36085468';

// What are we doing: CRUD
$txRequest->profileDeleteRequest->customerProfileAction = 'D';
*/
/*
$txRequest = new StdClass();
$txRequest->reversalRequest = new StdClass();
$txRequest->reversalRequest->orbitalConnectionUsername = Mage::getStoreConfig('payment/chasePaymentTech/username', Mage::app()->getStore());
$txRequest->reversalRequest->orbitalConnectionPassword = Mage::getStoreConfig('payment/chasePaymentTech/password', Mage::app()->getStore());
$txRequest->reversalRequest->merchantID = Mage::getStoreConfig('payment/chasePaymentTech/merchant_id', Mage::app()->getStore());
$txRequest->reversalRequest->terminalID = Mage::getStoreConfig('payment/chasePaymentTech/terminal_id', Mage::app()->getStore());


$txRequest->reversalRequest->bin = Mage::getStoreConfig('payment/chasePaymentTech/bin', Mage::app()->getStore());
$txRequest->reversalRequest->merchantID = Mage::getStoreConfig('payment/chasePaymentTech/merchant_id', Mage::app()->getStore());
$txRequest->reversalRequest->orderID = "12342";

// What are we doing: CRUD
$txRequest->reversalRequest->txRefNum = '534C1D6050A598322CC4D0926F1D0BCF8F97540D';
*/

$wsdl = Mage::getStoreConfig('payment/chasePaymentTech/url', Mage::app()->getStore());
date_default_timezone_set('US/Eastern');
try {
	$client = new SoapClient($wsdl, array('trace' => 1));
	
	//if void change ->newOrder to ->reversal
	//$response = $client->profileChange($txRequest);
	//$response = $client->profileFetch($txRequest);
	$response = $client->profileFetch($txRequest);
	echo date("Y-m-d H:i:s");
	echo "<br />";
	echo "<pre>";
	var_dump($response);
	echo "</pre>";

} catch (SoapFault $fault) {
	die($fault);
}
