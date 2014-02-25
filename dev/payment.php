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
$txRequest->newOrderRequest->ccAccountNum = '5454545454545454';
$txRequest->newOrderRequest->ccExp = '201505';
$txRequest->newOrderRequest->ccCardVerifyNum = '111';
$txRequest->newOrderRequest->avsZip = '11111';
$txRequest->newOrderRequest->avsAddress1 = '4921 S Murray Blvd APT S1';
$txRequest->newOrderRequest->avsCity = 'South Jordan';

//If refunding or voiding, pass in transaction ID here
//$txRequest->newOrderRequest->txRefNum = 'TRANSACTION ID';

//Transtype (newOrderRequest only):
//A  = Auth Only
//AC = Auth & Capture
//R  = Refund
$txRequest->newOrderRequest->transType = 'AC';
$txRequest->newOrderRequest->orderID = '1231123';
$txRequest->newOrderRequest->amount = round(31.82 * 100, 0);
		
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
