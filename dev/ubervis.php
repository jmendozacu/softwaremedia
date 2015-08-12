<?php

require "../app/Mage.php";
Mage::app('admin')->setUseSessionInUrl(false);
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

$uber = new SoftwareMedia_Ubervisibility_Model_Observer();
$uber->updateProduct();

/*

$api = new SoftwareMedia_Ubervisibility_Helper_Api();
$csv_content = array();

$ubervis_updated_site_prods = $api->callApi(Zend_Http_Client::GET, 'product/updated-price/site', array(), 999);

$ubervis_updated_cpc_prods = $api->callApi(Zend_Http_Client::GET, 'product/updated-price/cpc', array(), 999);
		

$sku_list = array();

if (!empty($ubervis_updated_site_prods)) {
	foreach ($ubervis_updated_site_prods as $prod) {
		$prod_arr = (array) $prod;
		$sku_list[$prod_arr['sku']] = $prod_arr['sku'];
	}
}
		
var_dump($sku_list);

die();
/*

$noteList = Mage::getModel('customernotes/notes')->getCollection();
$noteList->setOrder('created_time','DESC');
$notes = array();

foreach($noteList as $note) {
	if(!array_key_exists($note->getCustomerId(), $notes))
		$notes[$note->getCustomerId()] = array();
		
	$notes[$note->getCustomerId()][] = $note;
}
 
foreach($notes as $customerNote) {
	if (count($customerNote) > 1) {
		$time = null;
		echo "New" . "<br />";
		foreach($customerNote as $note) {
				if ($time)
					$note->setUpdateTime($time);
				else
					$note->setUpdateTime(NULL);
				$note->save();
			
			$time = $note->getCreatedTime();
		}
	}
}

*/