<?php
require "../app/Mage.php";

$txRequest = new StdClass();

//If voiding a transaction change newOrderRequest to reversalRequest
$txRequest->newOrderRequest = new StdClass();
$txRequest->newOrderRequest->orbitalConnectionUsername = Mage::getStoreConfig('payment/chasePaymentTech/username', Mage::app()->getStore());
$txRequest->newOrderRequest->orbitalConnectionPassword = Mage::getStoreConfig('payment/chasePaymentTech/password', Mage::app()->getStore());
$txRequest->newOrderRequest->version = '2.8';
$txRequest->newOrderRequest->industryType = 'EC';
$txRequest->newOrderRequest->bin = Mage::getStoreConfig('payment/chasePaymentTech/bin', Mage::app()->getStore());
$txRequest->newOrderRequest->merchantID = Mage::getStoreConfig('payment/chasePaymentTech/merchant_id', Mage::app()->getStore());
$txRequest->newOrderRequest->terminalID = Mage::getStoreConfig('payment/chasePaymentTech/terminal_id', Mage::app()->getStore());
$txRequest->newOrderRequest->ccAccountNum = '4788250000028291';
$txRequest->newOrderRequest->ccExp = '201501';
$txRequest->newOrderRequest->ccCardVerifyNum = '';
$txRequest->newOrderRequest->avsZip = '66666';
$txRequest->newOrderRequest->avsAddress1 = '916 S, MAIN STREET';
$txRequest->newOrderRequest->avsCity = 'SALT LAKE CITY';
$txRequest->newOrderRequest->orderID = '100000269';
$txRequest->newOrderRequest->amount = 0;

//If refunding or voiding, pass in transaction ID here
//$txRequest->newOrderRequest->txRefNum = '530D141AE32C3C947029D4C49BF2738157C75491';

//Transtype (newOrderRequest only):
//A  = Auth Only
//AC = Auth & Capture
//R  = Refund
$txRequest->newOrderRequest->transType = 'A';
		
$wsdl = Mage::getStoreConfig('payment/chasePaymentTech/url', Mage::app()->getStore());

try {
	$client = new SoapClient($wsdl, array('trace' => 1));
	
	//if void change ->newOrder to ->reversal
	$response = $client->newOrder($txRequest);
	echo "<pre>";
	var_dump($response);
	echo "</pre>";

} catch (SoapFault $fault) {
	die($fault);
}
