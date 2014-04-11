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
$txRequest->newOrderRequest->customerRefNum = '35931422';
$txRequest->newOrderRequest->avsZip = '66666';
$txRequest->newOrderRequest->avsAddress1 = '916 S, MAIN STREET';
$txRequest->newOrderRequest->avsCity = 'SALT LAKE CITY';
$txRequest->newOrderRequest->orderID = '142322';
$txRequest->newOrderRequest->amount = 2500;
//$txRequest->newOrderRequest->ccAccountum = '371449635398431';
//$txRequest->newOrderRequest->ccExp = "2014" . sprintf('%02d', "08");
//If refunding or voiding, pass in transaction ID here
//$txRequest->newOrderRequest->txRefNum = '53472332B570BC5478B8CA40662D5F2AF1C25481';

//Transtype (newOrderRequest only):
//A  = Auth Only
//AC = Auth & Capture
//R  = Refund

$txRequest->newOrderRequest->transType = 'A';

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


$txRequest->profileAddRequest->mbRecurringStartDate = "08012014";
$txRequest->profileAddRequest->mbRecurringStartDate = "10012014";
$txRequest->profileAddRequest->mbRecurringFrequency = "1 * *";



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
$txRequest->profileChangeRequest->customerRefNum = '35931422';
// What are we doing: CRUD
$txRequest->profileChangeRequest->customerProfileAction = 'AU';

$txRequest->profileChangeRequest->ccAccountNum = '6011000995500000';


$txRequest = new StdClass();
$txRequest->profileFetchRequest = new StdClass();
$txRequest->profileFetchRequest->orbitalConnectionUsername = Mage::getStoreConfig('payment/chasePaymentTech/username', Mage::app()->getStore());
$txRequest->profileFetchRequest->orbitalConnectionPassword = Mage::getStoreConfig('payment/chasePaymentTech/password', Mage::app()->getStore());
$txRequest->profileFetchRequest->bin = Mage::getStoreConfig('payment/chasePaymentTech/bin', Mage::app()->getStore());
$txRequest->profileFetchRequest->merchantID = Mage::getStoreConfig('payment/chasePaymentTech/merchant_id', Mage::app()->getStore());
$txRequest->profileFetchRequest->customerRefNum = '35871352';

// What are we doing: CRUD
$txRequest->profileFetchRequest->customerProfileAction = 'R';
*/

/*
$txRequest = new StdClass();
$txRequest->profileDeleteRequest = new StdClass();
$txRequest->profileDeleteRequest->orbitalConnectionUsername = Mage::getStoreConfig('payment/chasePaymentTech/username', Mage::app()->getStore());
$txRequest->profileDeleteRequest->orbitalConnectionPassword = Mage::getStoreConfig('payment/chasePaymentTech/password', Mage::app()->getStore());
$txRequest->profileDeleteRequest->bin = Mage::getStoreConfig('payment/chasePaymentTech/bin', Mage::app()->getStore());
$txRequest->profileDeleteRequest->merchantID = Mage::getStoreConfig('payment/chasePaymentTech/merchant_id', Mage::app()->getStore());
$txRequest->profileDeleteRequest->customerRefNum = '35870018';

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
$txRequest->reversalRequest->orderID = "123411112";

// What are we doing: CRUD
$txRequest->reversalRequest->txRefNum = '5347213D6C98D3DA1941E5E9A4A81D9B757F541D';

*/
$wsdl = Mage::getStoreConfig('payment/chasePaymentTech/url', Mage::app()->getStore());

try {
	$client = new SoapClient($wsdl, array('trace' => 1));
	
	//if void change ->newOrder to ->reversal
	//$response = $client->profileAdd($txRequest);
	//$response = $client->profileChange($txRequest);
	$response = $client->newOrder($txRequest);
	echo "<pre>";
	var_dump($response);
	echo "</pre>";

} catch (SoapFault $fault) {
	die($fault);
}
