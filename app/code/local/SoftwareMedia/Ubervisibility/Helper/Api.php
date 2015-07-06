<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Api
 *
 * @author david
 */
class SoftwareMedia_Ubervisibility_Helper_Api extends Mage_Core_Helper_Abstract {

	public function callApi($method, $uri, array $variables = array(), $timeout = 15) {
		$curl = new Varien_Http_Adapter_Curl();
		$curl->setConfig(array(
			'timeout' => $timeout //Timeout in no of seconds
		));

		$url = 'http://ubervisibility.com:8080/v1/' . $uri;

		if ($method == Zend_Http_Client::PUT) {
			$method = Zend_Http_Client::POST;
			$curl->addOption(CURLOPT_CUSTOMREQUEST, Zend_Http_Client::PUT);
		}

		$curl->write($method, $url, '1.1', array('Accept: application/json', 'Content-Type: application/json'), json_encode($variables));
		$data = $curl->read();
		$curl->close();

		if ($data === false) {
			return false;
		}
		$data = preg_split('/^\r\n/m', $data, 2);
		$data = trim($data[1]);

		//var_dump(json_decode($data));
		
		return json_decode($data);
	}

}
